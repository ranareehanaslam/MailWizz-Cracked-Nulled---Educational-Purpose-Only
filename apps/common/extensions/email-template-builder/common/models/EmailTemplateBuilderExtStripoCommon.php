<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExtStripoCommon
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
class EmailTemplateBuilderExtStripoCommon extends ExtensionModel
{
    /**
     * @var string
     */
    public $plugin_id = '';

    /**
     * @var string
     */
    public $secret_key = '';

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['plugin_id, secret_key', 'safe'],
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
            'plugin_id'  => $this->t('Plugin ID'),
            'secret_key' => $this->t('Secret Key'),
        ];
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'plugin_id'  => '',
            'secret_key' => '',
        ];
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @inheritDoc
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'plugin_id'  => $this->t('Your Stripo Plugin ID'),
            'secret_key' => $this->t('Your Stripo Secret Key'),
        ];
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->secret_key;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Stripo';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->t('Build email templates using Stripo.');
    }

    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        return $this->getOptionPrefix();
    }

    /**
     * @return string
     */
    public function getOptionPrefix(): string
    {
        return EmailTemplateBuilderExtStripoBuilder::STRIPO_BUILDER_ID;
    }
}
