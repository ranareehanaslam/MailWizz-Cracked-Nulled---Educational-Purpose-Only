<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeliveryReportHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.2
 */

class DeliveryReportHelper
{
    /**
     * @param \Carbon\Carbon $dateStart
     * @param \Carbon\Carbon $dateEnd
     * @param int|null $campaignId
     * @param int|null $customerId
     * @return array
     *
     */
    public static function getDeliveryStatsDataForChart(Carbon\Carbon $dateStart, Carbon\Carbon $dateEnd, ?int $campaignId = null, ?int $customerId = null): array
    {
        $data = [
            'chartData'    => [],
            'chartOptions' => [],
        ];

        if ($dateStart->greaterThan($dateEnd)) {
            return $data;
        }

        $chartDataSets = [
            'success' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Success'),
                'backgroundColor' => 'rgba(11, 183, 131, 1)',
                'borderColor'     => 'rgba(11, 183, 131, 1)',
                'data'            => [],
            ],
            'error' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Error'),
                'backgroundColor' => 'rgba(246, 78, 96, 1)',
                'borderColor'     => 'rgba(246, 78, 96, 1)',
                'data'            => [],
            ],
            'giveup' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Giveups'),
                'backgroundColor' => 'rgba(255, 168, 0, 1)',
                'borderColor'     => 'rgba(255, 168, 0, 1)',
                'data'            => [],
            ],
            'blacklisted' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Blacklisted'),
                'backgroundColor' => '#323248',
                'borderColor'     => '#323248',
                'data'            => [],
            ],
            'dp-reject' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Domain policy reject'),
                'backgroundColor' => 'grey',
                'borderColor'     => 'grey',
                'data'            => [],
            ],
            'hard-bounce' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Hard bounce'),
                'backgroundColor' => 'purple',
                'borderColor'     => 'purple',
                'data'            => [],
            ],
            'soft-bounce' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Soft bounce'),
                'backgroundColor' => 'blue',
                'borderColor'     => 'blue',
                'data'            => [],
            ],
            'internal-bounce' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Internal bounce'),
                'backgroundColor' => 'pink',
                'borderColor'     => 'pink',
                'data'            => [],
            ],
            'complaint' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Complaint'),
                'backgroundColor' => 'brown',
                'borderColor'     => 'brown',
                'data'            => [],
            ],
        ];

        $unit = '';
        if ($dateEnd->diffInYears($dateStart) > 1) {
            $unit = 'year';
        } elseif ($dateEnd->diffInMonths($dateStart) > 0) {
            $unit = 'month';
        } elseif ($dateEnd->diffInDays($dateStart) > 0) {
            $unit = 'day';
        } elseif ($dateEnd->diffInHours($dateStart) > 0) {
            $unit = 'hour';
        }

        if (empty($unit)) {
            return $data;
        }

        $groupDateFormatMapping = [
            'hour'  => 'Y-m-d H:00:00',
            'day'   => 'Y-m-d',
            'month' => 'Y-m-01',
            'year'  => 'Y',
        ];

        $jsGroupDateFormatMapping = [
            'hour'  => 'Y-M-D H:00:00',
            'day'   => 'MMM D Y',
            'month' => 'MMM Y',
            'year'  => 'Y',
        ];
        $chartOptions = [
            'responsive'          => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'title' => [
                    'text'    => t('campaigns', 'Campaign delivery stats'),
                    'display' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'type'    => 'time',
                    'display' => true,
                    'offset'  => true,
                    'time'    => [
                        'tooltipFormat' => $jsGroupDateFormatMapping[$unit],
                        'unit'          => $unit,
                    ],
                ],
            ],
        ];

        $dateFormat = $groupDateFormatMapping[$unit];
        $groupBy = sprintf('%s(date_added)', strtoupper($unit));

        $columns = [
            'date_added',
            'SUM(success_hourly) as successSum',
            'SUM(error_hourly) as errorSum',
            'SUM(giveup_hourly) as giveupSum',
            'SUM(blacklisted_hourly) as blacklistedSum',
            'SUM(dp_reject_hourly) as dpRejectSum',
            'SUM(hard_bounce_hourly) as hardBounceSum',
            'SUM(soft_bounce_hourly) as softBounceSum',
            'SUM(internal_bounce_hourly) as internalBounceSum',
            'SUM(complaint_hourly) as complaintSum',
        ];
        $criteria = new CDbCriteria();

        if ($campaignId) {
            $columns = array_merge([
                'campaign_id',
                'customer_id',
            ], $columns);
            $criteria->compare('t.campaign_id', (int)$campaignId);
        }

        if ($customerId) {
            $columns = array_merge(['customer_id'], $columns);
            $criteria->compare('t.customer_id', (int)$customerId);
        }

        $criteria->select = $columns;
        $criteria->addCondition('(t.date_added >= :dateStart AND t.date_added <= :dateEnd)');
        $criteria->group = $groupBy;
        $criteria->params[':dateStart'] = $dateStart->format('Y-m-d H:i:s');
        $criteria->params[':dateEnd'] = $dateEnd->format('Y-m-d H:i:s');

        $counters = CampaignDeliveryCountHistory::model()->findAll($criteria);

        $labels = [];
        foreach ($counters as $counter) {
            /** @var Carbon\Carbon $fromFormat */
            $fromFormat = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $counter->date_added);
            $labels[] = $fromFormat->format($dateFormat);

            $chartDataSets['success']['data'][]         = (int)$counter->successSum;
            $chartDataSets['error']['data'][]           = (int)$counter->errorSum;
            $chartDataSets['giveup']['data'][]          = (int)$counter->giveupSum;
            $chartDataSets['blacklisted']['data'][]     = (int)$counter->blacklistedSum;
            $chartDataSets['dp-reject']['data'][]       = (int)$counter->dpRejectSum;
            $chartDataSets['hard-bounce']['data'][]     = (int)$counter->hardBounceSum;
            $chartDataSets['soft-bounce']['data'][]     = (int)$counter->softBounceSum;
            $chartDataSets['internal-bounce']['data'][] = (int)$counter->internalBounceSum;
            $chartDataSets['complaint']['data'][]       = (int)$counter->complaintSum;
        }

        $chartData = [
            'labels'   => $labels,
            'datasets' => array_values($chartDataSets),
        ];

        $data['chartData']    = $chartData;
        $data['chartOptions'] = $chartOptions;

        return (array)$data;
    }

    /**
     * Retrieves delivery statistics data for a chart in a growth format.
     *
     * @param Carbon\Carbon $dateStart The start date of the data range.
     * @param Carbon\Carbon $dateEnd The end date of the data range.
     * @param int|null $campaignId Optional. The ID of the campaign. Defaults to null.
     * @param int|null $customerId Optional. The ID of the customer. Defaults to null.
     *
     * @return array The delivery statistics data for the chart in a growth format.
     *
     * @throws CException
     */
    public function getDeliveryStatsDataForChartAsGrowth(Carbon\Carbon $dateStart, Carbon\Carbon $dateEnd, ?int $campaignId = null, ?int $customerId = null): array
    {
        $data = [
            'chartData'    => [],
            'chartOptions' => [],
        ];

        if ($dateStart->greaterThan($dateEnd)) {
            return $data;
        }

        $chartDataSets = [
            'success' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Success'),
                'backgroundColor' => 'rgba(11, 183, 131, 1)',
                'borderColor'     => 'rgba(11, 183, 131, 1)',
                'data'            => [],
            ],
            'error' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Error'),
                'backgroundColor' => '#323248',
                'borderColor'     => '#323248',
                'data'            => [],
            ],
            'giveup' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Giveups'),
                'backgroundColor' => 'rgba(246, 78, 96, 1)',
                'borderColor'     => 'rgba(246, 78, 96, 1)',
                'data'            => [],
            ],
            'blacklisted' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Blacklisted'),
                'backgroundColor' => 'rgba(255, 168, 0, 1)',
                'borderColor'     => 'rgba(255, 168, 0, 1)',
                'data'            => [],
            ],
            'dp-reject' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Domain policy reject'),
                'backgroundColor' => 'rgba(255, 168, 0, 1)',
                'borderColor'     => 'rgba(255, 168, 0, 1)',
                'data'            => [],
            ],
            'hard-bounce' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Hard bounce'),
                'backgroundColor' => 'rgba(255, 168, 0, 1)',
                'borderColor'     => 'rgba(255, 168, 0, 1)',
                'data'            => [],
            ],
            'soft-bounce' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Soft bounce'),
                'backgroundColor' => 'rgba(255, 168, 0, 1)',
                'borderColor'     => 'rgba(255, 168, 0, 1)',
                'data'            => [],
            ],
            'internal-bounce' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Internal bounce'),
                'backgroundColor' => 'rgba(255, 168, 0, 1)',
                'borderColor'     => 'rgba(255, 168, 0, 1)',
                'data'            => [],
            ],
            'complaint' => [
                'type'            => 'bar',
                'label'           => t('campaigns', 'Complaint'),
                'backgroundColor' => 'rgba(255, 168, 0, 1)',
                'borderColor'     => 'rgba(255, 168, 0, 1)',
                'data'            => [],
            ],
        ];

        $unit = '';
        if ($dateEnd->diffInYears($dateStart) > 1) {
            $unit = 'year';
        } elseif ($dateEnd->diffInMonths($dateStart) > 0) {
            $unit = 'month';
        } elseif ($dateEnd->diffInDays($dateStart) > 0) {
            $unit = 'day';
        } elseif ($dateEnd->diffInHours($dateStart) > 0) {
            $unit = 'hour';
        }

        if (empty($unit)) {
            return $data;
        }

        $groupDateFormatMapping = [
            'hour'  => 'Y-m-d H:00:00',
            'day'   => 'Y-m-d',
            'month' => 'Y-m-01',
            'year'  => 'Y',
        ];

        $jsGroupDateFormatMapping = [
            'hour'  => 'Y-M-D H:00:00',
            'day'   => 'MMM D Y',
            'month' => 'MMM Y',
            'year'  => 'Y',
        ];
        $chartOptions = [
            'responsive'          => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'title' => [
                    'text'    => t('campaigns', 'Campaign delivery stats'),
                    'display' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'type'    => 'time',
                    'display' => true,
                    'offset'  => true,
                    'time'    => [
                        'tooltipFormat' => $jsGroupDateFormatMapping[$unit],
                        'unit'          => $unit,
                    ],
                ],
            ],
        ];

        $dateFormat = $groupDateFormatMapping[$unit];

        $model = CampaignDeliveryCountHistory::model();
        $groupBy = sprintf('%s(date_added)', strtoupper($unit));

        $subQuery = db()->createCommand()
            ->select('MAX(date_added) as max')
            ->from($model->tableName())
            ->group($groupBy)
            ->andWhere('(date_added >= :dateStart AND date_added <= :dateEnd)');
        $subQuery->params[':dateStart'] = $dateStart->format('Y-m-d H:i:s');
        $subQuery->params[':dateEnd'] = $dateEnd->format('Y-m-d H:i:s');

        $criteria = new CDbCriteria();
        if ($campaignId) {
            $subQuery->andWhere('campaign_id = :cid');
            $subQuery->params[':cid'] = (int)$campaignId;
            $criteria->compare('t.campaign_id', (int)$campaignId);
        }

        if ($customerId) {
            $subQuery->andWhere('customer_id = :cusid');
            $subQuery->params[':cusid'] = (int)$customerId;
            $criteria->compare('t.customer_id', (int)$customerId);
        }

        $groupedDatesInCriteria = (array)$subQuery->queryColumn();

        $criteria->addCondition('(t.date_added >= :dateStart AND t.date_added <= :dateEnd)');
        $criteria->addInCondition('t.date_added', $groupedDatesInCriteria);
        $criteria->params[':dateStart'] = $dateStart->format('Y-m-d H:i:s');
        $criteria->params[':dateEnd'] = $dateEnd->format('Y-m-d H:i:s');

        $counters = CampaignDeliveryCountHistory::model()->findAll($criteria);

        $labels = [];
        foreach ($counters as $counter) {
            /** @var Carbon\Carbon $fromFormat */
            $fromFormat = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $counter->date_added);
            $labels[] = $fromFormat->format($dateFormat);

            $chartDataSets['success']['data'][]         = (int)$counter->success_total;
            $chartDataSets['error']['data'][]           = (int)$counter->error_total;
            $chartDataSets['giveup']['data'][]          = (int)$counter->giveup_total;
            $chartDataSets['blacklisted']['data'][]     = (int)$counter->blacklisted_total;
            $chartDataSets['dp-reject']['data'][]       = (int)$counter->dp_reject_total;
            $chartDataSets['hard-bounce']['data'][]     = (int)$counter->hard_bounce_total;
            $chartDataSets['soft-bounce']['data'][]     = (int)$counter->soft_bounce_total;
            $chartDataSets['internal-bounce']['data'][] = (int)$counter->internal_bounce_total;
            $chartDataSets['complaint']['data'][]       = (int)$counter->complaint_total;
        }

        $chartData = [
            'labels'   => $labels,
            'datasets' => array_values($chartDataSets),
        ];

        $data['chartData']    = $chartData;
        $data['chartOptions'] = $chartOptions;

        return (array)$data;
    }

    /**
     * Exports statistics to a CSV file based on the given campaign ID and customer ID.
     *
     * @param int|null $campaignId The ID of the campaign. Defaults to null.
     * @param int|null $customerId The ID of the customer. Defaults to null.
     */
    public static function exportStats(?int $campaignId = null, ?int $customerId = null): void
    {
        $timestamp = (int)strtotime('-1 year');

        $criteria = new CDbCriteria();
        if ($customerId) {
            $criteria->compare('t.customer_id', (int)$customerId);
        }

        if ($campaignId) {
            $criteria->compare('t.campaign_id', (int)$campaignId);
        }

        $criteria->addCondition('t.date_added >= :datetime');
        $criteria->params[':datetime'] = date('Y-m-d H:i:s', $timestamp);

        $counters = CampaignDeliveryCountHistory::model()->findAll($criteria);

        // Set the download headers
        HeaderHelper::setDownloadHeaders('campaign-delivery-stats.csv');

        try {
            $csvWriter  = League\Csv\Writer::createFromPath('php://output', 'w');
            $attributes = AttributeHelper::removeSpecialAttributes($counters[0]->attributes);

            /** @var callable $callback */
            $callback   = [$counters[0], 'getAttributeLabel'];
            $attributes = array_map($callback, array_keys($attributes));

            $csvWriter->insertOne($attributes);

            foreach ($counters as $counter) {
                $attributes = AttributeHelper::removeSpecialAttributes($counter->attributes);
                $csvWriter->insertOne(array_values($attributes));
            }
        } catch (Exception $e) {
        }

        app()->end();
    }
}
