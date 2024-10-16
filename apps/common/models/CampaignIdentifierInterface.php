<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CampaignIdentifierInterface
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */

interface CampaignIdentifierInterface
{
    /**
     * @param int|null $id
     *
     * @return mixed
     */
    public function setCampaignId(?int $id);

    /**
     * @return int|null
     */
    public function getCampaignId(): ?int;
}
