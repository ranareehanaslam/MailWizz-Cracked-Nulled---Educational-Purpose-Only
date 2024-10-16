<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CampaignSmartTrackingInfoWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.1
 */

class CampaignSmartTrackingInfoWidget extends CWidget
{
    /**
     * @var Campaign
     */
    public $campaign;

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        if (empty($this->campaign)) {
            return;
        }

        $campaign = $this->campaign;

        $this->render('smart-tracking-info', compact(
            'campaign'
        ));
    }
}
