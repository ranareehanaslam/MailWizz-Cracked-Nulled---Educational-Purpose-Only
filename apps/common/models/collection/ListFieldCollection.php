<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldCollection
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */

class ListFieldCollection extends BaseCollection
{
    /**
     * @param mixed $condition
     *
     * @return ListFieldCollection
     */
    public static function findAll($condition = ''): self
    {
        return new self(ListField::model()->findAll($condition));
    }

    /**
     * @param array $attributes
     *
     * @return ListFieldCollection
     */
    public static function findAllByAttributes(array $attributes): self
    {
        return new self(ListField::model()->findAllByAttributes($attributes));
    }
}
