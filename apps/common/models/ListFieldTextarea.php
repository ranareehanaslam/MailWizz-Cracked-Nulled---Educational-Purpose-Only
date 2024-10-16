<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldText
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.8
 */

class ListFieldTextarea extends ListFieldText
{

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{list_field}}';
    }


    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListFieldTextarea the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var ListFieldTextarea $model */
        $model = parent::model($className);

        return $model;
    }
}
