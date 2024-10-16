<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CampaignDeliveryLogHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.1
 */

class CampaignDeliveryLogHelper
{
    /**
     * @param string $messageId
     *
     * @return CampaignDeliveryLog|null
     */
    public static function findByEmailMessageId(string $messageId): ?CampaignDeliveryLog
    {
        /** @var CampaignDeliveryLog|null $log */
        $log = CampaignDeliveryLog::model()->findByEmailMessageId($messageId);
        if (!empty($log)) {
            return $log;
        }

        /** @var CampaignDeliveryLogArchive|null $log */
        $log = CampaignDeliveryLogArchive::model()->findByEmailMessageId($messageId);
        if (!empty($log)) {
            return $log;
        }

        return null;
    }

    /**
     * @param CDbCriteria $criteria
     *
     * @return CampaignDeliveryLog|null
     */
    public static function findByCriteria(CDbCriteria $criteria): ?CampaignDeliveryLog
    {
        /** @var CampaignDeliveryLogArchive|null $log */
        $log = CampaignDeliveryLog::model()->find($criteria);
        if (!empty($log)) {
            return $log;
        }

        /** @var CampaignDeliveryLogArchive|null $log */
        $log = CampaignDeliveryLogArchive::model()->find($criteria);
        if (!empty($log)) {
            return $log;
        }

        return null;
    }
}
