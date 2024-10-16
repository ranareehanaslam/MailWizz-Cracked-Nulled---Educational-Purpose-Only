<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * SurveyCategoryCollection
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.2
 */

class SurveyCategoryCollection extends BaseCollection
{
    /**
     * @param mixed $condition
     *
     * @return SurveyCategoryCollection
     */
    public static function findAll($condition = ''): self
    {
        return new self(SurveyCategory::model()->findAll($condition));
    }

    /**
     * @param array $attributes
     *
     * @return SurveyCategoryCollection
     */
    public static function findAllByAttributes(array $attributes): self
    {
        return new self(SurveyCategory::model()->findAllByAttributes($attributes));
    }
}
