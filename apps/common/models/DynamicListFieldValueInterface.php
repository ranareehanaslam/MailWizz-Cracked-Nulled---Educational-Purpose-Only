<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DynamicListFieldValueInterface
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */
interface DynamicListFieldValueInterface
{
    /**
     * @return string
     */
    public function getListFieldValueClassName(): string;

    /**
     * @return ListFieldValue
     */
    public function getListFieldValueModel(): ListFieldValue;

    /**
     * @return ListFieldValue
     */
    public function createListFieldValueInstance(): ListFieldValue;
}
