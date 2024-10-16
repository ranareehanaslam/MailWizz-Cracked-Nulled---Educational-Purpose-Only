<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.2
 */

class QueueProcessorConsoleHourlyCampaignDeliveryCountHistoryUpdate implements Processor
{
    /**
     * @param Message $message
     * @param Context $context
     *
     * @return string
     */
    public function process(Message $message, Context $context)
    {
        // do not retry this message
        if ($message->isRedelivered()) {
            return self::ACK;
        }
        $dateStart  = $message->getProperty('dateStart');
        $dateEnd    = $message->getProperty('dateEnd');
        $customerId = $message->getProperty('customerId');

        if (empty($dateEnd) || empty($dateStart) || empty($customerId)) {
            return self::ACK;
        }

        $dateStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateStart);
        $dateEnd   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateEnd);

        if (!$dateStart || !$dateEnd) {
            return self::ACK;
        }

        if ($dateStart->greaterThan($dateEnd)) {
            return self::ACK;
        }

        $criteria = new CDbCriteria();
        $criteria->addNotInCondition('status', [Campaign::STATUS_DRAFT, Campaign::STATUS_PENDING_DELETE]);
        $criteria->compare('customer_id', (int)$customerId);

        $campaigns = Campaign::model()->findAll($criteria);

        foreach ($campaigns as $campaign) {
            $counterExists = (int)CampaignDeliveryCountHistory::model()->countByAttributes([
                'campaign_id' => $campaign->campaign_id,
                'date_added'  => $dateEnd->format('Y-m-d H:00:00'),
            ]);

            if ($counterExists > 0) {
                continue;
            }

            // We stop calculating if the campaign finished sending more than 2 hours ago
            if ($campaign->finishedSendingMoreThanXHoursAgo(2)) {
                continue;
            }

            $countHistory = new CampaignDeliveryCountHistory();
            $countHistory->campaign_id = $campaign->campaign_id;
            $countHistory->customer_id = (int)$customerId;
            if ($countHistory->calculate($dateStart, $dateEnd)) {
                $countHistory->date_added = $dateEnd->format('Y-m-d H:00:00');
                $countHistory->detachBehavior('CTimestampBehavior');
                $countHistory->save(false);
            }
        }

        return self::ACK;
    }
}
