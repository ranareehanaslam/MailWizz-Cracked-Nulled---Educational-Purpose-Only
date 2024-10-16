<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldCheckboxlist
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.6
 */

/**
 * Class ListFieldCheckboxlist
 */
class ListFieldCheckboxlist extends ListFieldMultiValueAware
{

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListFieldCheckboxlist the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var ListFieldCheckboxlist $model */
        $model = parent::model($className);

        return $model;
    }
}
