<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeleteCampaignsByDateRangeCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.2
 */

class DeleteCampaignsByDateRangeCommand extends ConsoleCommand
{
    /**
     * @var string
     */
    public $start = '';

    /**
     * @var string
     */
    public $end = '';

    /**
     * @var int
     */
    public $interactive = 1;

    /**
     * @return int
     */
    public function actionIndex()
    {
        if (empty($this->start) && empty($this->end)) {
            $this->stdout('Please set either the --start option or --end option, or both, i.e: --start="2023-01-01"');
            return 1;
        }

        $startDate      = '';
        $startTimestamp = 0;
        $endTimestamp   = time() + 24 * 60 * 60; // We add 1 day to now to make sure we get all today lists
        $endDate        = date('Y-m-d H:i:s', $endTimestamp);

        if (!empty($this->start)) {
            $startTimestamp = (int)strtotime($this->start);
            $startDate      = date('Y-m-d H:i:s', $startTimestamp);

            if (!$startTimestamp) {
                $this->stdout('Please enter a valid start date!');
                return 1;
            }

            if ($startTimestamp > $endTimestamp) {
                $this->stdout('The start date should be a date in the past!');
                return 1;
            }
        }

        if (!empty($this->end)) {
            $endTimestamp = (int)strtotime($this->end);
            $endDate      = (string)date('Y-m-d H:i:s', $endTimestamp);

            if (empty($endTimestamp)) {
                $this->stdout('Please enter a valid end date!');
                return 1;
            }

            if ($startDate && $startTimestamp > $endTimestamp) {
                $this->stdout('The start date should be lower then the end date!');
                return 1;
            }
        }

        $confirm = sprintf('Are you sure you want to delete the campaigns that are created between "%s" and "%s"?', $startDate, $endDate);
        if (empty($this->start)) {
            $confirm = sprintf('Are you sure you want to delete the campaigns older than "%s"?', $endDate);
        }

        if ($this->interactive && !$this->confirm($confirm)) {
            $this->stdout('Okay, aborting!');
            return 0;
        }

        $statuses = [Campaign::STATUS_SENT];

        // since 2.3.8
        $removeDrafts = $this->interactive && $this->confirm('Remove drafts as well?');
        if ($removeDrafts) {
            $statuses[] = Campaign::STATUS_DRAFT;
        }
        //

        $criteria = new CDbCriteria();
        $criteria->addInCondition('status', $statuses);

        if (!empty($startDate)) {
            $criteria->addCondition('date_added > :startDate');
            $criteria->params[':startDate'] = $startDate;
        }

        if (!empty($this->end)) {
            $criteria->addCondition('date_added < :endDate');
            $criteria->params[':endDate'] = $endDate;
        }

        $count = Campaign::model()->count($criteria);

        if (empty($count)) {
            $this->stdout('Nothing to delete, aborting!');
            return 0;
        }

        if ($this->interactive && !$this->confirm(sprintf('This action found %d campaigns. Proceed?', $count))) {
            $this->stdout('Okay, aborting!');
            return 0;
        }

        $start = microtime(true);

        $deletedCount = $skippedCount = 0;
        CampaignCollection::findAll($criteria)->each(function (Campaign $campaign) use (&$deletedCount, &$skippedCount) {
            if ($campaign->getRemovable()) {
                $campaign->delete();
                $deletedCount++;
            } else {
                $skippedCount++;
            }
        });

        $timeTook  = round(microtime(true) - $start, 4);

        $this->stdout(sprintf('DONE, took %s seconds to delete %s campaigns, skipped %s campaigns (possible reasons: AR event dependency)!', $timeTook, $deletedCount, $skippedCount));
        return 0;
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        $cmd = $this->getCommandRunner()->getScriptName() . ' ' . $this->getName();

        $help  = sprintf('command: %s --start=DATE/EXPRESSION --end=DATE/EXPRESSION --interactive = 1/0', $cmd) . PHP_EOL;
        $help .= '--start=DATE/EXPRESSION where DATE/EXPRESSION can be any expression parsable by php\'s strtotime function. or a date in the Y-m-d format ie: --start="-6 months".' . PHP_EOL;
        $help .= '--end=DATE/EXPRESSION where DATE/EXPRESSION can be any expression parsable by php\'s strtotime function. or a date in the Y-m-d format ie: --end="-6 months".' . PHP_EOL;

        $help .= '--interactive=1/0 where 0 means no user interaction will occur.' . PHP_EOL;

        return $help;
    }
}
