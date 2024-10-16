<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldUrl
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.8
 */

class ListFieldUrl extends ListField
{
    public const ALLOWED_SCHEME_ANY = '';
    public const ALLOWED_SCHEME_HTTPS_HTTP = 'https_http';
    public const ALLOWED_SCHEME_HTTPS = 'https';
    public const ALLOWED_SCHEME_HTTP = 'http';

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
    public $allowed_scheme = self::ALLOWED_SCHEME_ANY;

    /**
     * @var string
     */
    public $whitelist_domains = '';

    /**
     * @var string
     */
    public $blacklist_domains = '';

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
            ['allowed_scheme', 'in', 'range' => array_keys($this->getAllowedSchemesList())],
            ['whitelist_domains, blacklist_domains', 'length', 'min' => 0, 'max' => 255],
            ['whitelist_domains, blacklist_domains', '_validateDomains'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'min_length'        => t('list_fields', 'Minimum length'),
            'max_length'        => t('list_fields', 'Maximum length'),
            'allowed_scheme'    => t('list_fields', 'Allowed scheme'),
            'whitelist_domains' => t('list_fields', 'Whitelist domains'),
            'blacklist_domains' => t('list_fields', 'Blacklist domains'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'min_length'        => t('list_fields', 'Minimum length of the text'),
            'max_length'        => t('list_fields', 'Maximum length of the text'),
            'allowed_scheme'    => t('list_fields', 'Allowed scheme'),
            'whitelist_domains' => t('list_fields', 'Allow only domains in this list. Separate multiple domains with a comma. Use only the domain name, i.e: example1.com, example2.com'),
            'blacklist_domains' => t('list_fields', 'Allow all domains except domains in this list. Separate multiple domains with a comma. Use only the domain name, i.e: example1.com, example2.com'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListFieldUrl the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var ListFieldUrl $model */
        $model = parent::model($className);

        return $model;
    }


    /**
     * @return array
     */
    public function getAllowedSchemesList(): array
    {
        return [
            self::ALLOWED_SCHEME_ANY        => t('list_fields', 'Any scheme'),
            self::ALLOWED_SCHEME_HTTPS_HTTP => t('list_fields', 'HTTPS and HTTP'),
            self::ALLOWED_SCHEME_HTTPS      => t('list_fields', 'HTTPS only'),
            self::ALLOWED_SCHEME_HTTP       => t('list_fields', 'HTTP only'),
        ];
    }

    /**
     * @param string $attribute
     * @param array $params
     *
     * @return void
     */
    public function _validateDomains(string $attribute, array $params = []): void
    {
        $value = $this->$attribute;
        if (empty($value) || $this->hasErrors($attribute)) {
            return;
        }

        $validator = new CUrlValidator();
        $domains = array_map('trim', explode(',', $value));
        $failed = [];
        foreach ($domains as $domain) {
            if (strpos($domain, '.') === false || $validator->validateValue(sprintf('https://%s', $domain))) {
                continue;
            }
            $failed[] = $domain;
        }

        if (empty($failed)) {
            return;
        }

        $this->addError($attribute, t('list_fields', 'Following entries are not valid domains: {entries}', [
            'entries' => implode(', ', $failed),
        ]));
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        $this->modelMetaData->getModelMetaData()->add('min_length', (int)$this->min_length);
        $this->modelMetaData->getModelMetaData()->add('max_length', (int)$this->max_length);
        $this->modelMetaData->getModelMetaData()->add('allowed_scheme', (string)$this->allowed_scheme);
        $this->modelMetaData->getModelMetaData()->add('whitelist_domains', (string)$this->whitelist_domains);
        $this->modelMetaData->getModelMetaData()->add('blacklist_domains', (string)$this->blacklist_domains);

        return parent::beforeSave();
    }

    /**
     * @return void
     */
    protected function afterFind()
    {
        $md = $this->modelMetaData->getModelMetaData();

        $this->min_length           = $md->contains('min_length') ? (int)$md->itemAt('min_length') : $this->min_length;
        $this->max_length           = $md->contains('max_length') ? (int)$md->itemAt('max_length') : $this->max_length;
        $this->allowed_scheme       = $md->contains('allowed_scheme') ? (string)$md->itemAt('allowed_scheme') : $this->allowed_scheme;
        $this->whitelist_domains    = $md->contains('whitelist_domains') ? (string)$md->itemAt('whitelist_domains') : $this->whitelist_domains;
        $this->blacklist_domains    = $md->contains('blacklist_domains') ? (string)$md->itemAt('blacklist_domains') : $this->blacklist_domains;

        parent::afterFind();
    }
}
