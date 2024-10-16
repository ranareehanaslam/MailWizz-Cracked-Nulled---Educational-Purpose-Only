<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldBuilderTypeUrlDomainsValidator
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.8
 */

class ListFieldBuilderTypeUrlDomainsValidator extends CValidator
{
    /**
     * @var array
     */
    public $whitelistDomains = [];

    /**
     * @var array
     */
    public $blacklistDomains = [];

    /**
     * @param CModel $object
     * @param string $attribute
     *
     * @return void
     */
    protected function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if (empty($value) || $object->hasErrors($value)) {
            return;
        }

        if (!empty($this->whitelistDomains)) {
            $regex = sprintf('/(%s)/i', implode('|', array_filter(array_unique(array_map(function ($domain) {
                return preg_quote(trim($domain), '/');
            }, $this->whitelistDomains)))));

            if (!@preg_match($regex, $value)) {
                $this->addError($object, $attribute, t('list_fields', 'The URL contains a domain that is not whitelisted!'));
            }
        }

        if (!empty($this->blacklistDomains)) {
            $regex = sprintf('/(%s)/i', implode('|', array_filter(array_unique(array_map(function ($domain) {
                return preg_quote(trim($domain), '/');
            }, $this->blacklistDomains)))));

            if (@preg_match($regex, $value)) {
                $this->addError($object, $attribute, t('list_fields', 'The URL contains a domain that is blacklisted!'));
            }
        }
    }
}
