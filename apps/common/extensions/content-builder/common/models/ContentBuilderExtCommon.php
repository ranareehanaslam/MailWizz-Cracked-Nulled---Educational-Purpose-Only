<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * ContentBuilderExtCommon
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 *
 * @since 2.3.0
 */

class ContentBuilderExtCommon extends ExtensionModel
{
    /**
     * @var string
     */
    public $current_builder = 'innova-studio-builder';

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
            'current_builder' => $this->t('Current builder'),
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
            'innova-studio-builder'    => new ContentBuilderExtInnovaStudioBuilder($this->getExtension()),
        ];
    }

    /**
     * @return ContentBuilder
     */
    public function getCurrentBuilderInstance(): ContentBuilder
    {
        return $this->getAvailableBuildersInstances()[$this->current_builder];
    }
}
