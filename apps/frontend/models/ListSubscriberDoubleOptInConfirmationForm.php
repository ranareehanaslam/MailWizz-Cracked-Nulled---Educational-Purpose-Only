<?php
declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListSubscriberDoubleOptInConfirmationForm
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.2
 */
class ListSubscriberDoubleOptInConfirmationForm extends FormModel
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $compareEmail;

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'compare', 'compareAttribute' => 'compareEmail', 'message' => t('lists', 'The email address you entered does not match the one on records.')],
        ];

        return CMap::mergeArray(parent::rules(), $rules);
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeLabels()
    {
        $labels = [
            'email'         => t('lists', 'Email'),
            'compareEmail'  => t('lists', 'Compare Email'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }
}
