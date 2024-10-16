<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * UpdateWorkerFor_2_2_3
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.3
 */

class UpdateWorkerFor_2_2_3 extends UpdateWorkerAbstract
{
    public function run()
    {
        // keep this in memory for later use
        $sql = 'SELECT 
    		campaign_id, 
    		send_between_start, 
    		TIMESTAMPDIFF(
    		    HOUR, 
    		    DATE_FORMAT(NOW(), CONCAT("%Y-%m-%d", " ", send_between_start)), 
    		    DATE_FORMAT(NOW(), CONCAT("%Y-%m-%d", " ", send_between_end))
    		) AS hours_count 
			FROM {{campaign}} 
			WHERE `send_between_start` IS NOT NULL AND `send_between_end` IS NOT NULL 
			HAVING hours_count > 0';

        $rows = db()->createCommand($sql)->queryAll();

        // run the sql from file
        $this->runQueriesFromSqlFile('2.2.3');

        // import new translations strings
        $this->runImportTranslationsFromJsonFile('2.2.3');

        // refresh the cache schema for the table
        db()->getSchema()->getTable('{{campaign}}', true);

        // apply the changes to loaded rows
        foreach ($rows as $row) {
            db()->createCommand()
                ->update(
                    '{{campaign}}',
                    [
                        'send_between_start' => $row['send_between_start'],
                        'send_between_interval' => $row['hours_count'],
                    ],
                    'campaign_id = :cid',
                    [
                        ':cid' => $row['campaign_id'],
                    ]
                );
        }
    }
}
