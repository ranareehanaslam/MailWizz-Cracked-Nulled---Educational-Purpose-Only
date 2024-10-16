<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * SurveyCollection
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.15
 */

class LandingPageRevisionCollection extends BaseCollection
{
    /**
     * @param mixed $condition
     *
     * @return LandingPageRevisionCollection
     */
    public static function findAll($condition = ''): self
    {
        return new self(LandingPageRevision::model()->findAll($condition));
    }

    /**
     * @param array $attributes
     *
     * @return LandingPageRevisionCollection
     */
    public static function findAllByAttributes(array $attributes): self
    {
        return new self(LandingPageRevision::model()->findAllByAttributes($attributes));
    }
}
