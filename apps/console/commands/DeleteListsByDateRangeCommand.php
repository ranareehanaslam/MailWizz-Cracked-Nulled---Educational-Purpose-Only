<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeleteListsByDateRangeCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.2
 */

class DeleteListsByDateRangeCommand extends ConsoleCommand
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

            if (empty($startTimestamp)) {
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
            $endDate      = date('Y-m-d H:i:s', $endTimestamp);

            if (empty($endTimestamp)) {
                $this->stdout('Please enter a valid end date!');
                return 1;
            }

            if ($startDate && $startTimestamp > $endTimestamp) {
                $this->stdout('The start date should be lower then the end date!');
                return 1;
            }
        }

        $confirm = sprintf('Are you sure you want to delete the lists that are created between "%s" and "%s"?', $startDate, $endDate);
        if (empty($this->start)) {
            $confirm   = sprintf('Are you sure you want to delete the lists older than "%s"?', $endDate);
        }

        if ($this->interactive && !$this->confirm($confirm)) {
            $this->stdout('Okay, aborting!');
            return 0;
        }


        $criteria = new CDbCriteria();
        $criteria->compare('status', Lists::STATUS_ACTIVE);

        if (!empty($startDate)) {
            $criteria->addCondition('date_added > :startDate');
            $criteria->params[':startDate'] = $startDate;
        }

        if (!empty($this->end)) {
            $criteria->addCondition('date_added < :endDate');
            $criteria->params[':endDate'] = $endDate;
        }

        $count = Lists::model()->count($criteria);

        if (empty($count)) {
            $this->stdout('Nothing to delete, aborting!');
            return 0;
        }

        if ($this->interactive && !$this->confirm(sprintf('This action will delete %d lists. For each list will remove related subscribers, segments, custom fields and campaigns. Proceed?', $count))) {
            $this->stdout('Okay, aborting!');
            return 0;
        }

        $start = microtime(true);

        ListsCollection::findAll($criteria)->each(function (Lists $list) {
            $activeCampaignsCount = (int)Campaign::model()->countByAttributes([
                'list_id' => $list->list_id,
                'status'  => [
                    Campaign::STATUS_SENDING, Campaign::STATUS_PROCESSING, Campaign::STATUS_DRAFT, Campaign::STATUS_PENDING_APPROVE,
                ],
            ]);

            if ($activeCampaignsCount) {
                $this->stdout(sprintf('List %s has active campaigns, skipping!', $list->list_uid));
                return;
            }
            $list->delete();
        });

        $timeTook  = round(microtime(true) - $start, 4);

        $this->stdout(sprintf('DONE, took %s seconds!', $timeTook));
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
