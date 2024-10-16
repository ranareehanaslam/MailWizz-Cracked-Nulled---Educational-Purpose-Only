<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeleteEmailBlacklistByCriteriaCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.3
 */

class DeleteEmailBlacklistByCriteriaCommand extends ConsoleCommand
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
     * @var string
     */
    public $reason = '';

    /**
     * @var int
     */
    public $interactive = 1;

    /**
     * @return int
     * @throws CDbException
     */
    public function actionIndex()
    {
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

        $confirm = sprintf('Are you sure you want to delete the blacklisted emails that are created between "%s" and "%s"?', $startDate, $endDate);
        if (empty($this->start)) {
            $confirm = sprintf('Are you sure you want to delete the blacklisted emails older than "%s"?', $endDate);
        }

        if (!empty($this->reason)) {
            $confirm .= sprintf(' Having the reason "%s"?', $this->reason);
        }

        if ($this->interactive && !$this->confirm($confirm)) {
            $this->stdout('Okay, aborting!');
            return 0;
        }

        $criteria = new CDbCriteria();

        if (!empty($this->reason)) {
            $criteria->compare('reason', $this->reason);
        }
        if (!empty($startDate)) {
            $criteria->addCondition('date_added > :startDate');
            $criteria->params[':startDate'] = $startDate;
        }

        if (!empty($this->end)) {
            $criteria->addCondition('date_added < :endDate');
            $criteria->params[':endDate'] = $endDate;
        }

        $count = EmailBlacklist::model()->count($criteria);

        if (empty($count)) {
            $this->stdout('Nothing to delete, aborting!');
            return 0;
        }

        if ($this->interactive && !$this->confirm(sprintf('This action will delete %d blacklisted emails. Proceed?', $count))) {
            $this->stdout('Okay, aborting!');
            return 0;
        }

        $start = microtime(true);

        while (true) {
            $criteria->limit = 1000;
            $emails = EmailBlacklist::model()->findAll($criteria);

            if (empty($emails)) {
                break;
            }

            foreach ($emails as $email) {
                $this->stdout(sprintf('Deleting the email: %s', $email->email));
                $email->delete();
            }
        }

        $timeTook  = round(microtime(true) - $start, 4);

        $this->stdout(sprintf("DONE, took %s seconds!\n", $timeTook));
        return 0;
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        $cmd = $this->getCommandRunner()->getScriptName() . ' ' . $this->getName();

        $help  = sprintf('command: %s --reason=STRING --start=DATE/EXPRESSION --end=DATE/EXPRESSION --interactive = 1/0', $cmd) . PHP_EOL;
        $help .= '--reason=STRING where STRING can be any reason for a blacklist. The search will be an exact match, ie: --reason="My reason".' . PHP_EOL;
        $help .= '--start=DATE/EXPRESSION where DATE/EXPRESSION can be any expression parsable by php\'s strtotime function. or a date in the Y-m-d format ie: --start="-6 months".' . PHP_EOL;
        $help .= '--end=DATE/EXPRESSION where DATE/EXPRESSION can be any expression parsable by php\'s strtotime function. or a date in the Y-m-d format ie: --end="-6 months".' . PHP_EOL;

        $help .= '--interactive=1/0 where 0 means no user interaction will occur.' . PHP_EOL;

        return $help;
    }
}
