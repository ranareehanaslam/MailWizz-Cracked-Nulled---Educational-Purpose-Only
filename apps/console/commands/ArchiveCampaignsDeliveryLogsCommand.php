<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ArchiveCampaignsDeliveryLogsCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 *
 */

class ArchiveCampaignsDeliveryLogsCommand extends ConsoleCommand
{
    /**
     * @var int
     */
    public $days_back = 5;

    /**
     * @return int
     */
    public function actionIndex()
    {
        $result = 1;

        try {
            hooks()->doAction('console_command_archive_campaigns_delivery_logs_before_process', $this);

            $result = $this->process();

            hooks()->doAction('console_command_archive_campaigns_delivery_logs_after_process', $this);
        } catch (Exception $e) {
            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $result;
    }

    /**
     * @return int
     * @throws CDbException
     * @throws CException
     */
    protected function process()
    {
        $this->stdout('Detecting the transaction isolation level');

        $txVariablesArray = [
            'transaction_isolation',
            'tx_isolation',
        ];

        $txVariablesArrayForCommand = array_map(
            function (string $value): string {
                return sprintf('"%s"', $value);
            },
            $txVariablesArray
        );

        $command = sprintf('SHOW VARIABLES WHERE Variable_name IN (%s)', implode(',', $txVariablesArrayForCommand));
        $txData = db()->createCommand($command)->queryAll();

        /** @var string $isoLevel */
        $isoLevel = '';

        foreach ($txData as $row) {
            if (in_array($row['Variable_name'], $txVariablesArray)) {
                /** @var string $isoLevel */
                $isoLevel = (string)str_replace(['-', '_'], ' ', $row['Value']);
                break;
            }
        }

        if (empty($isoLevel)) {
            $this->stdout('Unable to detect the transaction isolation level');
            return 1;
        }

        $this->stdout(sprintf('Current transaction isolation level is: %s', $isoLevel));

        $this->stdout('Searching for campaigns');

        $sql  = sprintf('
			SELECT `campaign_id` 
			FROM {{campaign}} 
			WHERE 
			    `status` = :st AND 
			    `delivery_logs_archived` = :dla AND 
			    `finished_at` <= DATE_SUB(NOW(), INTERVAL %d DAY) 
			ORDER BY campaign_id ASC
		', abs($this->days_back));

        $rows = db()->createCommand($sql)->queryAll(true, [
            ':st' => Campaign::STATUS_SENT,
            ':dla' => Campaign::TEXT_NO,
        ]);

        if (empty($rows)) {
            $this->stdout('Found no campaigns');
            return 0;
        }

        $this->stdout(sprintf('Found %d campaigns', count($rows)));

        $this->stdout('Setting the new transaction isolation level');

        try {
            db()->createCommand('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED')->execute();
        } catch (Exception $e) {
            $this->stdout(sprintf('Error setting the new transaction isolation level: %s', $e->getMessage()));
            return 0;
        }

        $this->stdout('Starting to process the campaigns');

        foreach ($rows as $row) {
            $this->stdout(sprintf('Processing campaign %d', $row['campaign_id']));

            $this->stdout(sprintf('Checking the validity of the campaign %d', $row['campaign_id']));

            // make sure the campaign is still there and the same
            $sql  = 'SELECT campaign_id FROM {{campaign}} WHERE campaign_id = :cid AND delivery_logs_archived = :dla';
            $_row = db()->createCommand($sql)->queryRow(true, [':cid' => $row['campaign_id'], ':dla' => Campaign::TEXT_NO]);
            if (empty($_row)) {
                $this->stdout(sprintf('Campaign %d is not valid anymore', $row['campaign_id']));
                continue;
            }

            // since 2.3.3
            if (!(bool)hooks()->applyFilters('console_command_archive_campaigns_delivery_logs_is_campaign_archievable', true, (int)$row['campaign_id'])) {
                $this->stdout(sprintf('Campaign %d cannot be archived, skipping it...', $row['campaign_id']));
                continue;
            }

            $this->stdout(sprintf('Handling data for the campaign %d', $row['campaign_id']));

            $transaction = db()->beginTransaction();
            try {
                $this->stdout(sprintf('Copy data for campaign %d', $row['campaign_id']));
                $sql = '
                    INSERT INTO {{campaign_delivery_log_archive}} (campaign_id, subscriber_id, server_id, message, processed, retries, max_retries, email_message_id, delivery_confirmed, `status`, date_added)
                    SELECT campaign_id, subscriber_id, server_id, message, processed, retries, max_retries, email_message_id, delivery_confirmed, `status`, date_added
                    FROM {{campaign_delivery_log}}
                    WHERE campaign_id = :cid
                ';
                db()->createCommand($sql)->execute([':cid' => (int)$row['campaign_id']]);

                $this->stdout(sprintf('Update delivery logs archived flag for the campaign %d', $row['campaign_id']));
                $sql = 'UPDATE {{campaign}} SET delivery_logs_archived = :dla WHERE campaign_id = :cid';
                db()->createCommand($sql)->execute([':dla' => Campaign::TEXT_YES, ':cid' => (int)$row['campaign_id']]);

                $this->stdout(sprintf('Deleting delivery logs for campaign %d', $row['campaign_id']));
                $sql = 'DELETE FROM {{campaign_delivery_log}} WHERE campaign_id = :cid';
                db()->createCommand($sql)->execute([':cid' => (int)$row['campaign_id']]);

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollback();
                $this->stdout(sprintf('Error processing campaign %d: %s', $row['campaign_id'], $e->getMessage()));
                continue;
            }

            $this->stdout(sprintf('Done processing campaign %d', $row['campaign_id']));
        }

        $this->stdout(sprintf('Restoring the isolation level to: %s', $isoLevel));
        try {
            db()->createCommand(sprintf('SET SESSION TRANSACTION ISOLATION LEVEL %s', $isoLevel))->execute();
        } catch (Exception $e) {
            $this->stdout(sprintf('Error restoring the transaction isolation level: %s', $e->getMessage()));
            return 1;
        }

        $this->stdout('Done!');
        return 0;
    }
}
