<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeliveryServerWarmupPlanHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.1.10
 */
class DeliveryServerWarmupPlanHelper
{
    /**
     * @param DeliveryServer $server
     * @param callable|null $logger
     *
     * @return void
     * @throws CDbException
     */
    public static function handleServerWarmupPlanScheduleLogs(DeliveryServer $server, ?callable $logger = null): void
    {
        if ($logger === null) {
            $logger = function ($input) {
            };
        }

        call_user_func($logger, sprintf('[Server: #%d] - Started processing', $server->server_id));

        // nothing to do in this case
        if (empty($server->warmup_plan_id)) {
            call_user_func($logger, sprintf('[Server: #%d] - This server has no warmup plan', $server->server_id));
            return;
        }

        /** @var DeliveryServerWarmupPlan|null $plan */
        $plan = $server->warmupPlan;
        if (empty($plan) || !$plan->getIsActive()) {
            call_user_func($logger, sprintf('[Server: #%d] - Unable to load an active warmup plan', $server->server_id));
            return;
        }

        if ($plan->getIsDeliveryServerCompleted((int)$server->server_id)) {
            call_user_func($logger, sprintf('[Server: #%d] - The warmup plan #%d is completed for this server', $server->server_id, $plan->plan_id));
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('plan_id', (int)$plan->plan_id);
        $criteria->order = 'schedule_id ASC';

        /** @var DeliveryServerWarmupPlanSchedule[] $schedules */
        $schedules = DeliveryServerWarmupPlanSchedule::model()->findAll($criteria);

        /** @var DeliveryServerWarmupPlanSchedule|null $currentSchedule */
        $currentSchedule = null;

        /** @var DeliveryServerWarmupPlanScheduleLog|null $lastLog */
        $lastLog = null;

        $countedLogsSuccessfully = true;

        call_user_func($logger, sprintf('[Server: #%d] - For warmup plan #%d, we found %d schedules', $server->server_id, $plan->plan_id, count($schedules)));

        foreach ($schedules as $index => $schedule) {
            call_user_func($logger, sprintf('[Server: #%d] - Schedule #%d(no. %d)', $server->server_id, $schedule->schedule_id, $index));

            $criteria = new CDbCriteria();
            $criteria->compare('plan_id', (int)$plan->plan_id);
            $criteria->compare('server_id', (int)$server->server_id);
            $criteria->compare('schedule_id', (int)$schedule->schedule_id);

            /** @var DeliveryServerWarmupPlanScheduleLog|null $log */
            $log = DeliveryServerWarmupPlanScheduleLog::model()->find($criteria);
            if (empty($log)) {
                $currentSchedule = $schedule;
                call_user_func($logger, sprintf('[Server: #%d] - Schedule #%d(no. %d) is the active schedule', $server->server_id, $schedule->schedule_id, $index));
                break;
            }

            call_user_func($logger, sprintf('[Server: #%d] - Schedule #%d(no. %d) is an older schedule', $server->server_id, $schedule->schedule_id, $index));

            if ($log->getStatusIs(DeliveryServerWarmupPlanScheduleLog::STATUS_PROCESSING)) {
                try {
                    $criteria = new CDbCriteria();
                    $criteria->compare('server_id', (int)$server->server_id);
                    $criteria->addCondition('`date_added` BETWEEN :startDate AND DATE_FORMAT(NOW(), "%Y-%m-%d %H:00:00")');
                    $criteria->params[':startDate'] = $log->started_at;
                    $count = (int)DeliveryServerUsageLog::model()->count($criteria);

                    $status = $count >= $log->allowed_quota
                        ? DeliveryServerWarmupPlanScheduleLog::STATUS_COMPLETED
                        : DeliveryServerWarmupPlanScheduleLog::STATUS_PROCESSING;

                    $log->saveAttributes([
                        'status'        => $status,
                        'used_quota'    => $count,
                        'last_updated'  => MW_DATETIME_NOW,
                    ]);

                    call_user_func($logger, sprintf(
                        '[Server: #%d] - Schedule #%d(no. %d) is allowed to send %d and it has sent %d so far',
                        $server->server_id,
                        $schedule->schedule_id,
                        $index,
                        $log->allowed_quota,
                        $log->used_quota
                    ));
                } catch (Exception $e) {
                    Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
                    $countedLogsSuccessfully = false;

                    call_user_func($logger, $e->getMessage());
                }
            }

            $lastLog = $log;
        }

        // this is true by default, set to false ONLY if we need to count, and we fail
        if (!$countedLogsSuccessfully) {
            return;
        }

        // in ths case, even if the hour has passed, we were not able to deliver the number of emails
        // the schedule should send, so we span into the next hour, and so on, until we reach the allowed quota
        if ($lastLog && $lastLog->getStatusIs(DeliveryServerWarmupPlanScheduleLog::STATUS_PROCESSING)) {
            call_user_func($logger, sprintf(
                '[Server: #%d] - Schedule #%d has not reached the quota, it needs more time, skipping for now...',
                $server->server_id,
                $lastLog->schedule_id
            ));

            // Here we should re-assign the quota to the delivery server if somehow in the meantime the server warmup plan had changed
            [$hourlyQuota, $dailyQuota, $monthlyQuota] = self::assignQuotaFromPlanToDeliveryServer($server, $plan, (int)$lastLog->allowed_quota);

            call_user_func($logger, sprintf(
                '[Server: #%d] - Done, according to log #%d, server quotas are: hourly: %s, daily: %s, monthly: %s',
                $server->server_id,
                $lastLog->allowed_quota,
                $hourlyQuota,
                $dailyQuota,
                $monthlyQuota
            ));

            return;
        }

        // in this case, the warmup plan finished, this happens only once
        if (empty($currentSchedule)) {
            // @since 2.2.14 we do not reset the quota to 0, so the last schedule values will stay.

            call_user_func($logger, sprintf(
                '[Server: #%d] - The warmup plan has finished',
                $server->server_id
            ));

            return;
        }

        call_user_func($logger, sprintf(
            '[Server: #%d] - Creating the log for schedule #%d',
            $server->server_id,
            $currentSchedule->schedule_id
        ));

        $log = new DeliveryServerWarmupPlanScheduleLog();
        $log->plan_id       = (int)$plan->plan_id;
        $log->server_id     = (int)$server->server_id;
        $log->schedule_id   = (int)$currentSchedule->schedule_id;
        $log->allowed_quota = (int)$currentSchedule->getPlanQuota();
        $log->started_at    = new CDbExpression('DATE_FORMAT(NOW(), "%Y-%m-%d %H:00:00")');
        if (!$log->save()) {
            Yii::log($log->shortErrors->getAllAsString(), CLogger::LEVEL_ERROR);
            return;
        }

        // we must assign generated quota to the delivery server
        [$hourlyQuota, $dailyQuota, $monthlyQuota] = self::assignQuotaFromPlanToDeliveryServer($server, $plan, (int)$currentSchedule->getPlanQuota());

        call_user_func($logger, sprintf(
            '[Server: #%d] - Done, according to schedule #%d, new server quotas are: hourly: %s, daily: %s, monthly: %s',
            $server->server_id,
            $currentSchedule->schedule_id,
            $hourlyQuota,
            $dailyQuota,
            $monthlyQuota
        ));
    }

    /**
     * @param int|null $customerId
     * @return array
     */
    public static function getActiveWarmupPlans(?int $customerId = null): array
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'customer_id, plan_id, name';
        $criteria->compare('status', DeliveryServerWarmupPlan::STATUS_ACTIVE);
        if (!empty($customerId)) {
            $criteria->compare('customer_id', (int)$customerId);
        }

        return DeliveryServerWarmupPlanCollection::findAll($criteria)->mapWithKeys(function (DeliveryServerWarmupPlan $model) {
            return [
                $model->plan_id => $model->getNameWithCustomer(),
            ];
        })->all();
    }

    /**
     * @param DeliveryServer $server
     * @return string
     */
    public static function getServerPlanInfoDisplay(DeliveryServer $server): string
    {
        if (!($plan = $server->warmupPlan)) {
            return '';
        }

        $result            = [$plan->name];

        $schedules         = $plan->schedules;
        $totalSchedules    = count($schedules);
        $currentScheduleNo = 0;
        foreach ($schedules as $index => $schedule) {
            $log = $schedule->getScheduleLogByServerId((int)$server->server_id);
            if (empty($log)) {
                continue;
            }
            $currentScheduleNo = $index + 1;
            if ($log->getStatusIs(DeliveryServerWarmupPlanScheduleLog::STATUS_PROCESSING)) {
                break;
            }
        }

        if (empty($log)) {
            $result[] = t('app', 'Plan not started');
            return implode('<br />', $result);
        }

        $result[] = sprintf('%s (%d/%d)', t('warmup_plans', 'Schedule'), $currentScheduleNo, $totalSchedules);
        $result[] = sprintf('%s (%d/%d)', t('warmup_plans', 'Emails'), $log->used_quota, $log->allowed_quota);
        if ($log->allowed_quota > 0) {
            $result[] = sprintf('(%d%%)', ($log->used_quota / $log->allowed_quota) * 100);
        }

        return implode('<br />', $result);
    }

    /**
     * @param DeliveryServer $server
     * @param DeliveryServerWarmupPlan $plan
     * @param int $quota
     * @return int[]
     * @throws CDbException
     */
    protected static function assignQuotaFromPlanToDeliveryServer(DeliveryServer $server, DeliveryServerWarmupPlan $plan, int $quota): array
    {
        $secondQuota    = 0;
        $minuteQuota    = 0;
        $hourlyQuota    = 0;
        $dailyQuota     = 0;
        $monthlyQuota   = 0;

        if ($plan->getSendingQuotaTypeIsMonthly()) {
            $monthlyQuota = $quota;
            $dailyQuota   = $monthlyQuota > 0 ? (int)round($monthlyQuota / 30) : 0;
            $hourlyQuota  = $dailyQuota > 0 ? (int)round($dailyQuota / 24) : 0;
            $minuteQuota  = $hourlyQuota > 0 ? (int)round($hourlyQuota / 60) : 0;
            $secondQuota  = $minuteQuota > 0 ? (int)round($minuteQuota / 60) : 0;
        } elseif ($plan->getSendingQuotaTypeIsDaily()) {
            $dailyQuota   = $quota;
            $hourlyQuota  = $dailyQuota > 0 ? (int)round($dailyQuota / 24) : 0;
            $minuteQuota  = $hourlyQuota > 0 ? (int)round($hourlyQuota / 60) : 0;
            $secondQuota  = $minuteQuota > 0 ? (int)round($minuteQuota / 60) : 0;
        } elseif ($plan->getSendingQuotaTypeIsHourly()) {
            $hourlyQuota  = $quota;
            $minuteQuota  = $hourlyQuota > 0 ? (int)round($hourlyQuota / 60) : 0;
            $secondQuota  = $minuteQuota > 0 ? (int)round($minuteQuota / 60) : 0;
        }

        $server->saveAttributes([
            // disabled since 2.4.6. See issue #1107 for details
            // 'second_quota'      => $secondQuota,

            // disabled since 2.4.8. See issue #1107 for details
            // 'minute_quota'      => $minuteQuota,

            'hourly_quota'      => $hourlyQuota,
            'daily_quota'       => $dailyQuota,
            'monthly_quota'     => $monthlyQuota,
            'pause_after_send'  => 0,
        ]);

        return [$hourlyQuota, $dailyQuota, $monthlyQuota];
    }
}
