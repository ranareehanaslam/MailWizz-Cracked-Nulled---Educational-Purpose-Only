<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ApiSystemInit
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ApiSystemInit extends CApplicationComponent
{
    /**
     * @var bool
     */
    protected $_hasRanOnBeginRequest = false;

    /**
     * @var bool
     */
    protected $_hasRanOnEndRequest = false;

    /**
     * @return void
     * @throws CException
     */
    public function init()
    {
        parent::init();

        // hook into events and add our methods.
        app()->attachEventHandler('onBeginRequest', [$this, 'runOnBeginRequest']);
        app()->attachEventHandler('onEndRequest', [$this, 'runOnEndRequest']);
    }

    /**
     * @param CEvent $event
     *
     * @return void
     */
    public function runOnBeginRequest(CEvent $event)
    {
        if ($this->_hasRanOnBeginRequest) {
            return;
        }

        // 2.2.0 - handle campaign tracking custom url segments
        $this->handleCampaignTrackingCustomUrlSegments();

        // and mark the event as completed.
        $this->_hasRanOnBeginRequest = true;
    }

    /**
     * @param CEvent $event
     *
     * @return void
     */
    public function runOnEndRequest(CEvent $event)
    {
        if ($this->_hasRanOnEndRequest) {
            return;
        }

        // and mark the event as completed.
        $this->_hasRanOnEndRequest = true;
    }

    /**
     * @since 2.2.0
     *
     * @return void
     */
    private function handleCampaignTrackingCustomUrlSegments(): void
    {
        $trackClickUrlSegment = (string) app_param('campaign.track.click.url.segment', MW_CAMPAIGN_TRACK_CLICK_URL_SEGMENT);
        if ($trackClickUrlSegment !== MW_CAMPAIGN_TRACK_CLICK_URL_SEGMENT) {
            urlManager()->addRules([
                [
                    'campaigns_tracking/track_url',
                    'pattern'   => 'campaigns/<campaign_uid:([a-z0-9]+)>/' . $trackClickUrlSegment . '/<subscriber_uid:([a-z0-9]+)>/<hash:([a-z0-9]+)>',
                    'verb'      => 'GET',
                ],
            ], false);
        }

        $trackOpenUrlSegment = (string) app_param('campaign.track.open.url.segment', MW_CAMPAIGN_TRACK_OPEN_URL_SEGMENT);
        if ($trackOpenUrlSegment !== MW_CAMPAIGN_TRACK_OPEN_URL_SEGMENT) {
            urlManager()->addRules([
                [
                    'campaigns_tracking/track_opening',
                    'pattern'   => 'campaigns/<campaign_uid:([a-z0-9]+)>/' . $trackOpenUrlSegment . '/<subscriber_uid:([a-z0-9]+)>',
                    'verb'      => 'GET',
                ],
            ], false);
        }
    }
}
