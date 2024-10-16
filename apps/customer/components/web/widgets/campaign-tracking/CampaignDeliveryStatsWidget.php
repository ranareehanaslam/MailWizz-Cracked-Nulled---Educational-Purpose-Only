<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CampaignDeliveryStatsWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.2
 */

class CampaignDeliveryStatsWidget extends CWidget
{
    /**
     * @var Campaign|null
     */
    public $campaign;

    /**
     * @var Customer|null
     */
    public $customer;

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $campaign = $this->campaign;
        $customer = $this->customer;

        $dataUrl = createUrl('campaign_delivery_stats/application');
        $dataExportUrl = ['campaign_delivery_stats/application_export'];
        $criteria = new CDbCriteria();

        if (!empty($campaign)) {
            $criteria->compare('campaign_id', $campaign->campaign_id);
            $dataUrl = createUrl('campaign_delivery_stats/campaign', ['campaign_uid' => $campaign->campaign_uid]);
            $dataExportUrl = ['campaign_delivery_stats/campaign_export', 'campaign_uid' => $campaign->campaign_uid];
        }

        if (!empty($customer)) {
            $criteria->compare('customer_id', $customer->customer_id);
        }

        /** @var CampaignDeliveryCountHistory|null $found */
        $found = CampaignDeliveryCountHistory::model()->find($criteria);
        if (empty($found)) {
            return;
        }

        $dateRanges = $this->getDateRangesList();

        clientScript()->registerScriptFile(apps()->getBaseUrl('assets/js/chartjs/moment.js'));
        clientScript()->registerScriptFile(apps()->getBaseUrl('assets/js/chartjs/chart.js'));
        clientScript()->registerScriptFile(apps()->getBaseUrl('assets/js/chartjs/chartjs-adapter-moment.js'));

        clientScript()->registerScriptFile(apps()->getBaseUrl('assets/js/campaign-delivery-stats.js'));

        $this->render('campaign-delivery-stats', compact('dataUrl', 'dataExportUrl', 'dateRanges'));
    }

    /**
     * @return array
     */
    protected function getDateRangesList(): array
    {
        $ranges = [
            '12 hours' => '12 Hours',
            '1 day'    => '1 Day',
            '1 week'   => '1 Week',
            '1 month'  => '1 Month',
            '3 months' => '3 Months',
            '6 months' => '6 Months',
            '1 year'   => '1 Year',
        ];

        $campaign       = [];
        $dateFormat = 'Y-m-d H:i:s';
        $dateEnd    = Carbon\Carbon::now();
        foreach ($ranges as $interval => $name) {
            $dateStart = \Carbon\Carbon::createFromTimestamp((int)strtotime(sprintf('-%s', $interval)));
            $campaign[sprintf('%s - %s', $dateStart->format($dateFormat), $dateEnd->format($dateFormat))] = $name;
        }

        return $campaign;
    }
}
