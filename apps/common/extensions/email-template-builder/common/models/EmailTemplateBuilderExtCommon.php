<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExtCommon
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
class EmailTemplateBuilderExtCommon extends ExtensionModel
{
    /**
     * @var string
     */
    public $current_builder = EmailTemplateBuilderExtBasicBuilder::BASIC_BUILDER_ID;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $rules = [
            ['current_builder', 'required'],
            ['current_builder', 'in', 'range' => array_keys($this->getAvailableBuildersDropDown())],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        $labels = [
            'current_builder' => $this->t(' Current builder'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @inheritDoc
     */
    public function attributePlaceholders()
    {
        $placeholders = [];
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @inheritDoc
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'current_builder' => $this->t('Please select the builder that you want to use'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @inheritDoc
     */
    public function getCategoryName(): string
    {
        return '';
    }

    /**
     * @return array
     */
    public function getAvailableBuildersDropDown(): array
    {
        $availableBuilders = $this->getAvailableBuildersInstances();

        $options = [];
        /** @var EmailTemplateBuilder $builder */
        foreach ($availableBuilders as $builder) {
            $options[$builder->getId()] = $builder->getName();
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getAvailableBuildersInstances(): array
    {
        return [
            EmailTemplateBuilderExtBasicBuilder::BASIC_BUILDER_ID       => new EmailTemplateBuilderExtBasicBuilder($this->getExtension()),
            EmailTemplateBuilderExtGrapesjsBuilder::GRAPESJS_BUILDER_ID => new EmailTemplateBuilderExtGrapesjsBuilder($this->getExtension()),
            EmailTemplateBuilderExtStripoBuilder::STRIPO_BUILDER_ID     => new EmailTemplateBuilderExtStripoBuilder($this->getExtension()),
        ];
    }

    /**
     * @return EmailTemplateBuilder
     */
    public function getCurrentBuilderInstance(): EmailTemplateBuilder
    {
        $availableBuilderInstances = $this->getAvailableBuildersInstances();

        return $availableBuilderInstances[$this->current_builder] ?? $availableBuilderInstances[EmailTemplateBuilderExtBasicBuilder::BASIC_BUILDER_ID];
    }
}
