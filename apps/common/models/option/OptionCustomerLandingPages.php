<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * OptionCustomerLandingPages
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.15
 */

class OptionCustomerLandingPages extends OptionBase
{
    /**
     * @var int
     */
    public $max_landing_pages = -1;

    /**
     * @var string
     */
    protected $_categoryName = 'system.customer_landing_pages';

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['max_landing_pages', 'required'],
            ['max_landing_pages', 'numerical', 'integerOnly' => true, 'min' => -1, 'max' => 10000],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeLabels()
    {
        $labels = [
            'max_landing_pages' => $this->t('Max. landing pages'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributePlaceholders()
    {
        $placeholders = [];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'max_landing_pages' => $this->t('Maximum number of landing pages the customers can create, set to -1 for unlimited'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
