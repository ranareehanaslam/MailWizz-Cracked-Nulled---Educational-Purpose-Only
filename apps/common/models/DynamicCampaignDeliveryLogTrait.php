<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DynamicCampaignDeliveryLogTrait
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */
trait DynamicCampaignDeliveryLogTrait
{
    /**
     * @return string
     */
    public function getCampaignDeliveryLogClassName(): string
    {
        return (string) hooks()->applyFilters('dynamic_campaign_delivery_log_model_class_name', CampaignDeliveryLog::class, $this);
    }

    /**
     * @return CampaignDeliveryLog
     */
    public function getCampaignDeliveryLogModel(): CampaignDeliveryLog
    {
        return call_user_func([$this->getCampaignDeliveryLogClassName(), 'model']);
    }

    /**
     * @return CampaignDeliveryLog
     */
    public function createCampaignDeliveryLogInstance(): CampaignDeliveryLog
    {
        $className = $this->getCampaignDeliveryLogClassName();

        return new $className();
    }
}
