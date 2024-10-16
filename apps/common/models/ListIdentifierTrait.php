<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListIdentifierTrait
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */

trait ListIdentifierTrait
{
    /**
     * @param int|null $id
     *
     * @return $this
     */
    public function setListId(?int $id): self
    {
        $this->list_id = $id;

        hooks()->doAction('list_identifier_after_set_list_id', $this);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getListId(): ?int
    {
        return !empty($this->list_id) ? (int)$this->list_id : null;
    }
}
