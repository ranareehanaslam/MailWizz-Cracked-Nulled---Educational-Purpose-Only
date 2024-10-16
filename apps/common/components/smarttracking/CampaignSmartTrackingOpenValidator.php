<?php declare(strict_types=1);

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */
class CampaignSmartTrackingOpenValidator extends CampaignSmartTrackingValidatorAbstract
{
    /**
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign)
    {
        parent::__construct($campaign);
        $this->setup();
    }

    /**
     * @return void
     */
    protected function setup(): void
    {
        $this->setAction(CampaignTrackingIgnoreList::ACTION_OPEN);
        $this->setAllowedIntervalAfterDelivery('-30 seconds');

        $this->setAllowedActionsPerTimeFrame(5);
        $this->setAllowedActionsPerTimeFrameInterval(10);
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->getCampaign()->option->getSmartOpenTracking();
    }
}
