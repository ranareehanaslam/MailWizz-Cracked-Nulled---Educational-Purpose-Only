<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Campaign_delivery_statsController
 *
 * Handles the actions that fetch the data for the campaigns stats
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.2
 */

class Campaign_delivery_statsController extends Controller
{

    /**
     * Display campaign delivery stats
     *
     * @param string $campaign_uid
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionCampaign($campaign_uid)
    {
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(['campaigns/index']);
            return;
        }

        $dates = explode(' - ', (string)request()->getPost('range', ''));
        if (count($dates) != 2) {
            $this->renderJson([
                'chartData'    => [],
                'chartOptions' => [],
            ]);
        }

        $dateStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dates[0]);
        $dateEnd   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dates[1]);
        if (!$dateStart || !$dateEnd) {
            $this->renderJson([
                'chartData'    => [],
                'chartOptions' => [],
            ]);
            return;
        }

        $campaign = $this->loadCampaignModel($campaign_uid);
        $data = DeliveryReportHelper::getDeliveryStatsDataForChart($dateStart, $dateEnd, (int)$campaign->campaign_id);

        $this->renderJson([
            'chartData'    => $data['chartData'],
            'chartOptions' => $data['chartOptions'],
        ]);
    }

    /**
     * Export reports related to a certain campaign
     *
     * @param string $campaign_uid
     *
     * @return void
     * @throws CHttpException
     */
    public function actionCampaign_export(string $campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        DeliveryReportHelper::exportStats((int)$campaign->campaign_id);
    }

    /**
     * Display application delivery stats
     *
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionApplication()
    {
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(['dashboard/index']);
            return;
        }

        $dates = explode(' - ', (string)request()->getPost('range', ''));
        if (count($dates) != 2) {
            $this->renderJson([
                'chartData'    => [],
                'chartOptions' => [],
            ]);
        }

        $dateStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dates[0]);
        $dateEnd   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dates[1]);
        if (!$dateStart || !$dateEnd) {
            $this->renderJson([
                'chartData'    => [],
                'chartOptions' => [],
            ]);
            return;
        }

        $customerId = null;
        if (apps()->isAppName('customer') && app()->hasComponent('customer') && customer()->getId() > 0) {
            $customerId = customer()->getId();
        }

        $data = DeliveryReportHelper::getDeliveryStatsDataForChart($dateStart, $dateEnd, null, $customerId);

        $this->renderJson([
            'chartData'    => $data['chartData'],
            'chartOptions' => $data['chartOptions'],
        ]);
    }

    /**
     * Export delivery statistics for the application.
     *
     * @return void
     */
    public function actionApplication_export()
    {
        $customerId = null;
        if (apps()->isAppName('customer') && app()->hasComponent('customer') && customer()->getId() > 0) {
            $customerId = (int)customer()->getId();
        }
        DeliveryReportHelper::exportStats(null, $customerId);
    }

    /**
     * Load campaign model by campaign_uid
     *
     * @param string $campaign_uid The unique identifier of the campaign
     * @return Campaign The loaded campaign model
     * @throws CHttpException If the requested campaign does not exist
     */
    public function loadCampaignModel(string $campaign_uid): Campaign
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        $criteria->addNotInCondition('status', [Campaign::STATUS_PENDING_DELETE]);

        /** @var Campaign|null $model */
        $model = Campaign::model()->find($criteria);

        if (empty($model)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        return $model;
    }
}
