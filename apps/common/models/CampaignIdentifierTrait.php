<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CampaignIdentifierTrait
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */

trait CampaignIdentifierTrait
{
    /**
     * @var int|null
     */
    private $_campaignId;

    /**
     * @param int|null $id
     *
     * @return $this
     */
    public function setCampaignId(?int $id): self
    {
        if ($this->hasAttribute('campaign_id')) {
            $this->campaign_id = $id;
        } else {
            $this->_campaignId = $id;
        }

        hooks()->doAction('campaign_identifier_after_set_campaign_id', $this);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCampaignId(): ?int
    {
        if ($this->hasAttribute('campaign_id')) {
            return !empty($this->campaign_id) ? (int)$this->campaign_id : null;
        }
        return !empty($this->_campaignId) ? (int)$this->_campaignId : null;
    }
}
