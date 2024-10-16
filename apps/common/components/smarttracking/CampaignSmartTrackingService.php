<?php declare(strict_types=1);

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */
class CampaignSmartTrackingService
{
    /**
     * @var Campaign
     */
    protected $_campaign;

    /**
     * @var CampaignSmartTrackingValidatorAbstract
     */
    protected $_actionValidator;

    /**
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign)
    {
        $this->_campaign = $campaign;
    }

    /**
     * @param ListSubscriber $subscriber
     * @param string $ipAddress
     * @param string $action
     * @return bool
     * @throws Exception
     */
    public function isValidRequest(string $ipAddress, string $action, ListSubscriber $subscriber): bool
    {
        $validator = $this->getActionValidator($action);

        if (!($result = $validator->validate($ipAddress, $subscriber))) {
            // Record the IP in the ignore list
            $this->recordToIgnoreList($action, $ipAddress, $validator->getReason());
        }

        return $result;
    }

    /**
     * @param string $action
     * @param string $ipAddress
     * @param string $reason
     * @return bool
     */
    public function recordToIgnoreList(string $action, string $ipAddress, string $reason): bool
    {
        $success = false;

        try {
            $ignored             = new CampaignTrackingIgnoreList();
            $ignored->action     = $action;
            $ignored->ip_address = $ipAddress;
            $ignored->reason     = $reason;
            $success = $ignored->save();
        } catch (Exception $e) {
        }

        return $success;
    }

    /**
     * @param string $action
     * @return CampaignSmartTrackingValidatorAbstract
     * @throws Exception
     */
    public function getActionValidator(string $action): CampaignSmartTrackingValidatorAbstract
    {
        if ($action === CampaignTrackingIgnoreList::ACTION_CLICK) {
            $this->_actionValidator = new CampaignSmartTrackingClickValidator($this->getCampaign());
        } elseif ($action === CampaignTrackingIgnoreList::ACTION_OPEN) {
            $this->_actionValidator = new CampaignSmartTrackingOpenValidator($this->getCampaign());
        } else {
            throw new Exception(t('app', 'No validator defined yet for this action'));
        }

        return $this->_actionValidator;
    }

    /**
     * @return Campaign
     */
    public function getCampaign(): Campaign
    {
        return $this->_campaign;
    }

    /**
     * @param Campaign $campaign
     * @return CampaignSmartTrackingService
     */
    public static function createInstance(Campaign $campaign): CampaignSmartTrackingService
    {
        return new static($campaign);
    }

    /**
     * @param CampaignSmartTrackingValidatorAbstract $actionValidator
     * @return $this
     */
    public function setActionValidator(CampaignSmartTrackingValidatorAbstract $actionValidator): CampaignSmartTrackingService
    {
        $this->_actionValidator = $actionValidator;
        return $this;
    }
}
