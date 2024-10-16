<?php declare(strict_types=1);

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */
abstract class CampaignSmartTrackingValidatorAbstract
{
    const DELIVERY_LOG_ATTRIBUTES_CACHE_KEY_PATTERN = 'campaign:%s:subscriber:%s:delivery_log_attributes';
    const RATE_LIMITER_KEY_PATTERN = 'ip:%s';

    /**
     * @var Campaign
     */
    protected $_campaign;

    /**
     * @var string
     */
    protected $_reason = '';

    /**
     * @var string
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_allowedIntervalAfterDelivery = '-1 minute';

    /**
     * @var int
     */
    protected $_allowedActionsPerTimeFrame = 10;

    /**
     * @var int
     */
    protected $_allowedActionsPerTimeFrameInterval = 30; //seconds

    /**
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign)
    {
        $this->_campaign = $campaign;
    }

    /**
     * @return void
     */
    abstract protected function setup(): void;

    /**
     * @return bool
     */
    abstract public function getIsEnabled(): bool;

    /**
     * @param ListSubscriber $subscriber
     * @param string $ipAddress
     * @return bool
     */
    public function validate(string $ipAddress, ListSubscriber $subscriber): bool
    {
        if (!$this->getIsEnabled()) {
            return true;
        }

        return
            $this->isValidRequestAgainstIgnoreList($ipAddress) &&
            $this->isValidRequestBySubscriberActionAfterDelivery($subscriber) &&
            $this->isValidRequestByIpNumberOfActionsInATimeFrame($ipAddress)
        ;
    }

    /**
     * @param string $ipAddress
     * @return bool
     */
    public function isValidRequestAgainstIgnoreList(string $ipAddress): bool
    {
        /** @var CampaignTrackingIgnoreList|null $ignored */
        $ignored = CampaignTrackingIgnoreList::model()->findByAttributes([
            'ip_address' => $ipAddress,
        ]);

        if (empty($ignored)) {
            return true;
        }

        if (!($isValid = $ignored->getStatusIs(CampaignTrackingIgnoreList::STATUS_INACTIVE))) {
            $this->setReason(t('app', 'Found in the campaign ignore list'));
        }

        return $isValid;
    }

    /**
     * @param ListSubscriber $subscriber
     * @return bool
     */
    public function isValidRequestBySubscriberActionAfterDelivery(ListSubscriber $subscriber): bool
    {
        $cacheKey      = sprintf(self::DELIVERY_LOG_ATTRIBUTES_CACHE_KEY_PATTERN, $this->getCampaign()->campaign_uid, $subscriber->subscriber_uid);
        $logAttributes = cache()->get($cacheKey);

        if (!is_array($logAttributes)) {
            // Get the campaign delivery log for this subscriber
            /** @var CampaignDeliveryLog|null $log */
            $log = $this->getCampaign()->getCampaignDeliveryLogModel()->findByAttributes([
                'campaign_id'   => $this->getCampaign()->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ]);

            $logAttributes = [];
            if (!empty($log)) {
                $logAttributes = $log->getAttributes();
            }
            cache()->set($cacheKey, $logAttributes);
        }

        if (empty($logAttributes)) {
            return true;
        }

        if (empty($logAttributes['date_added'])) {
            return true;
        }

        $isValid = (int)(strtotime($logAttributes['date_added'])) < (int)strtotime($this->getAllowedIntervalAfterDelivery());

        if (!$isValid) {
            $this->setReason(t('app', 'The subscriber executed too many actions in a short period of time, after email delivery'));
        }

        return $isValid;
    }

    /**
     * @param string $ipAddress
     * @return bool
     */
    public function isValidRequestByIpNumberOfActionsInATimeFrame(string $ipAddress): bool
    {
        // Check the number of clicks and opens in the X minutes/seconds interval for this IP
        $rateLimiterKey = sprintf(self::RATE_LIMITER_KEY_PATTERN, $ipAddress);
        $isValid        = !rateLimiter()->isOverLimit($rateLimiterKey, $this->getAllowedActionsPerTimeFrame(), $this->getAllowedActionsPerTimeFrameInterval() * 1000);

        if (!$isValid) {
            $this->setReason(t('app', 'The subscriber executed too many actions in a short period of time'));
        }

        return $isValid;
    }

    /**
     * @return Campaign
     */
    public function getCampaign(): Campaign
    {
        return $this->_campaign;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->_reason;
    }

    /**
     * @param string $reason
     * @return $this
     */
    public function setReason(string $reason): CampaignSmartTrackingValidatorAbstract
    {
        $this->_reason = $reason;
        return $this;
    }

    /**
     * @param string $action
     * @return $this
     */
    public function setAction(string $action): CampaignSmartTrackingValidatorAbstract
    {
        $this->_action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->_action;
    }

    /**
     * @return string
     */
    public function getAllowedIntervalAfterDelivery(): string
    {
        return $this->_allowedIntervalAfterDelivery;
    }

    /**
     * @param string $intervalExpression
     * @return $this
     */
    public function setAllowedIntervalAfterDelivery(string $intervalExpression): CampaignSmartTrackingValidatorAbstract
    {
        $this->_allowedIntervalAfterDelivery = $intervalExpression;
        return $this;
    }

    /**
     * @return int
     */
    public function getAllowedActionsPerTimeFrame(): int
    {
        return $this->_allowedActionsPerTimeFrame;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setAllowedActionsPerTimeFrame(int $count): CampaignSmartTrackingValidatorAbstract
    {
        $this->_allowedActionsPerTimeFrame = $count;
        return $this;
    }

    /**
     * @return int
     */
    public function getAllowedActionsPerTimeFrameInterval(): int
    {
        return $this->_allowedActionsPerTimeFrameInterval;
    }

    /**
     * @param int $interval
     * @return $this
     */
    public function setAllowedActionsPerTimeFrameInterval(int $interval): CampaignSmartTrackingValidatorAbstract
    {
        $this->_allowedActionsPerTimeFrameInterval = $interval;
        return $this;
    }
}
