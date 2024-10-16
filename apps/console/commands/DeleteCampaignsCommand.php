<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeleteCampaignsCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.3
 */

class DeleteCampaignsCommand extends ConsoleCommand
{
    /**
     * @param string $time
     * @param string $type
     *
     * @return int
     */
    public function actionIndex($time, $type='')
    {
        if (empty($time)) {
            $this->stdout('Please set the time option using the --time option, i.e: --time="-6 months"');
            return 1;
        }

        $timestamp = (int)strtotime($time);
        $date      = date('Y-m-d H:i:s', $timestamp);
        $confirm   = sprintf('Are you sure you want to delete the campaigns that are older than "%s" date?', $date);

        if (!$this->confirm($confirm)) {
            $this->stdout('Okay, aborting!');
            return 0;
        }

        $statuses = [Campaign::STATUS_SENT];

        // since 2.3.8
        $removeDrafts = $this->confirm('Remove drafts as well?');
        if ($removeDrafts) {
            $statuses[] = Campaign::STATUS_DRAFT;
        }
        //

        $criteria = new CDbCriteria();
        $criteria->addInCondition('status', $statuses);
        if (!empty($type)) {
            $criteria->compare('type', $type);
        }
        $criteria->addCondition('date_added < :dt');
        $criteria->params[':dt'] = $date;

        $count = Campaign::model()->count($criteria);

        if (empty($count)) {
            $this->stdout('Nothing to delete, aborting!');
            return 0;
        }

        if (!$this->confirm(sprintf('This action will delete %d campaigns. Proceed?', $count))) {
            $this->stdout('Okay, aborting!');
            return 0;
        }

        $start = microtime(true);

        CampaignCollection::findAll($criteria)->each(function (Campaign $campaign) {
            $campaign->delete();
        });

        $timeTook  = round(microtime(true) - $start, 4);

        $this->stdout(sprintf('DONE, took %s seconds!', $timeTook));
        return 0;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        $cmd = $this->getCommandRunner()->getScriptName() . ' ' . $this->getName();

        $help  = sprintf('command: %s --time=EXPRESSION --type=TYPE', $cmd) . "\n";
        $help .= '--time=EXPRESSION where EXPRESSION can be any expression parsable by php\'s strtotime function. ie: --time="-6 months".' . "\n";
        $help .= '--type=TYPE where TYPE can be either regular or autoresponder.' . "\n";

        return $help;
    }
}
