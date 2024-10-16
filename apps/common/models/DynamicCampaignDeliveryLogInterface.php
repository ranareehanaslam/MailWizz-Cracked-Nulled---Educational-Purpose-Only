<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DynamicCampaignDeliveryLogInterface
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */

interface DynamicCampaignDeliveryLogInterface
{
    /**
     * @return string
     */
    public function getCampaignDeliveryLogClassName(): string;

    /**
     * @return CampaignDeliveryLog
     */
    public function getCampaignDeliveryLogModel(): CampaignDeliveryLog;

    /**
     * @return CampaignDeliveryLog
     */
    public function createCampaignDeliveryLogInstance(): CampaignDeliveryLog;
}
