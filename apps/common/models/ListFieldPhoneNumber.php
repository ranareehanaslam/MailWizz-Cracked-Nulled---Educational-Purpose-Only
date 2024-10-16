<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldPhoneNumber
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.17
 */

class ListFieldPhoneNumber extends ListField
{
    public const DEFAULT_COUNTRY = 'us';

    /**
     * @var string
     */
    public $default_country = self::DEFAULT_COUNTRY;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{list_field}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['default_country', 'required'],
            ['default_country', 'in', 'range' => array_keys($this->getCountriesList())],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'default_country' => t('list_fields', 'Default country'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'min_length' => t('default_country', 'Default selected country'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     *
     * @param string $className active record class name.
     *
     * @return ListFieldPhoneNumber the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var ListFieldPhoneNumber $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return array
     */
    public function getCountriesList(): array
    {
        static $countriesList;
        if ($countriesList === null) {
            $countriesList = CountryCollection::findAll(['order' => 'country_id ASC'])->mapWithKeys(function (Country $country) {
                return [strtolower((string)$country->code) => $country->name];
            })->all();
        }

        return (array)$countriesList;
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        $this->modelMetaData->getModelMetaData()->add('default_country', (string)$this->default_country);

        return parent::beforeSave();
    }

    /**
     * @return void
     */
    protected function afterFind()
    {
        $md = $this->modelMetaData->getModelMetaData();

        $this->default_country = $md->contains('default_country') ? (string)$md->itemAt('default_country') : (string)$this->default_country;

        parent::afterFind();
    }
}
