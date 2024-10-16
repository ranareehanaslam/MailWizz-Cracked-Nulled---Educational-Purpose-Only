<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DynamicListFieldValueTrait
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */
trait DynamicListFieldValueTrait
{
    /**
     * @return string
     */
    public function getListFieldValueClassName(): string
    {
        return (string) hooks()->applyFilters('dynamic_list_field_value_model_class_name', ListFieldValue::class, $this);
    }

    /**
     * @return ListFieldValue
     */
    public function getListFieldValueModel(): ListFieldValue
    {
        return call_user_func([$this->getListFieldValueClassName(), 'model']);
    }

    /**
     * @return ListFieldValue
     */
    public function createListFieldValueInstance(): ListFieldValue
    {
        $className = $this->getListFieldValueClassName();

        return new $className();
    }
}
