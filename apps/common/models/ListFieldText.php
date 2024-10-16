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

class ListFieldText extends ListField
{
    private const CONTENT_RULE_NONE = '';
    private const CONTENT_RULE_ALPHA_CI = 'alpha_ci';
    private const CONTENT_RULE_ALPHANUM_CI = 'alphanum_ci';
    private const CONTENT_RULE_ALPHANUMEXT_CI = 'alphanumext_ci';

    private const CONTENT_RULE_ALPHA_CI_REGEX = '#^([a-z]+)$#i';
    private const CONTENT_RULE_ALPHANUM_CI_REGEX = '#^([a-z0-9]+)$#i';
    private const CONTENT_RULE_ALPHANUMEXT_CI_REGEX = '#^([a-z0-9\s\?\!\-\_\\n]+)$#i';

    /**
     * @var int
     */
    public $min_length = 1;

    /**
     * @var int
     */
    public $max_length = 255;

    /**
     * @var string
     */
    public $content_rule = self::CONTENT_RULE_NONE;

    /**
     * @var string
     */
    public $content_regex = '';

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
            ['min_length, max_length', 'required'],
            ['min_length, max_length', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 255],
            ['min_length', 'compare', 'compareAttribute' => 'max_length', 'operator' => '<'],
            ['max_length', 'compare', 'compareAttribute' => 'min_length', 'operator' => '>'],
            ['content_rule', 'in', 'range' => array_keys($this->getContentRulesList())],
            ['content_regex', 'length', 'min' => 3, 'max' => 100],
            ['content_regex', '_validateContentRegex'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'min_length'    => t('list_fields', 'Minimum length'),
            'max_length'    => t('list_fields', 'Maximum length'),
            'content_rule'  => t('list_fields', 'Content rule'),
            'content_regex' => t('list_fields', 'Content regex'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'min_length'    => t('list_fields', 'Minimum length of the text'),
            'max_length'    => t('list_fields', 'Maximum length of the text'),
            'content_rule'  => t('list_fields', 'Decides which rule applies to the content. If you need more flexibility, try the regular expressions field'),
            'content_regex' => t('list_fields', 'Advanced usage. Filter the content of the field using regular expressions. For example, following case insensitive regex, /^([a-z0-9\s\.]+)$/i will only allow letters from A to Z, numbers from 0 to 9, spaces and dots, as many times as needed. Use with care and make sure you test the subscription form afterwards'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListFieldText the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var ListFieldText $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return array
     */
    public function getContentRulesList(): array
    {
        return [
            self::CONTENT_RULE_ALPHA_CI       => t('list_fields', 'Allow A to Z. Case insensitive'),
            self::CONTENT_RULE_ALPHANUM_CI    => t('list_fields', 'Allow A to Z and 0 to 9. Case insensitive'),
            self::CONTENT_RULE_ALPHANUMEXT_CI => t('list_fields', 'Allow A to Z, 0 to 9, dots, spaces, dashes, underscores, new lines, question and exclamation marks. Case insensitive'),
        ];
    }

    /**
     * @return string
     * @throws CException
     */
    public function getContentRuleRegex(): string
    {
        $mapping = [
            self::CONTENT_RULE_ALPHA_CI       => self::CONTENT_RULE_ALPHA_CI_REGEX,
            self::CONTENT_RULE_ALPHANUM_CI    => self::CONTENT_RULE_ALPHANUM_CI_REGEX,
            self::CONTENT_RULE_ALPHANUMEXT_CI => self::CONTENT_RULE_ALPHANUMEXT_CI_REGEX,
        ];

        return $this->content_rule && isset($mapping[$this->content_rule]) ? $mapping[$this->content_rule] : '';
    }

    /**
     * @param string $attribute
     * @param array $params
     *
     * @return void
     */
    public function _validateContentRegex(string $attribute, array $params = []): void
    {
        $value = $this->$attribute;
        if (empty($value) || $this->hasErrors($value)) {
            return;
        }

        if (@preg_match($value, '') !== false) {
            return;
        }

        $this->addError($attribute, t('list_fields', 'Invalid regular expression'));
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        $this->modelMetaData->getModelMetaData()->add('min_length', (int)$this->min_length);
        $this->modelMetaData->getModelMetaData()->add('max_length', (int)$this->max_length);
        $this->modelMetaData->getModelMetaData()->add('content_rule', (string)$this->content_rule);
        $this->modelMetaData->getModelMetaData()->add('content_regex', (string)$this->content_regex);

        return parent::beforeSave();
    }

    /**
     * @return void
     */
    protected function afterFind()
    {
        $md = $this->modelMetaData->getModelMetaData();

        $this->min_length    = $md->contains('min_length') ? (int)$md->itemAt('min_length') : $this->min_length;
        $this->max_length    = $md->contains('max_length') ? (int)$md->itemAt('max_length') : $this->max_length;
        $this->content_rule  = $md->contains('content_rule') ? (string)$md->itemAt('content_rule') : $this->content_rule;
        $this->content_regex = $md->contains('content_regex') ? (string)$md->itemAt('content_regex') : $this->content_regex;

        parent::afterFind();
    }
}
