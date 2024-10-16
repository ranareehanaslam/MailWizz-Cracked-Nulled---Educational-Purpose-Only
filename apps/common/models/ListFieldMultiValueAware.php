<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldMultiValueAware
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.6
 */

/**
 * Class ListFieldMultiValueAware
 */
abstract class ListFieldMultiValueAware extends ListField implements ListFieldMultiValueAwareInterface
{
    use ListFieldMultiValueAwareTrait;

    /**
     * @var string
     */
    public $import_values_strategy = ListFieldMultiValueAwareInterface::IMPORT_VALUES_STRATEGY_REPLACE;

    /**
     * @var string
     */
    public $multi_values_separator = ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_DEFAULT;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['import_values_strategy', 'in', 'range' => array_keys($this->getImportValuesStrategiesList())],
            ['multi_values_separator', 'in', 'range' => array_keys($this->getMultiValuesSeparatorsList())],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'import_values_strategy'  => t('list_fields', 'Importing strategy'),
            'multi_values_separator' => t('list_fields', 'Import values separator'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'import_values_strategy'  => t('list_fields', 'The default strategy is REPLACE, which means the new subscribers values will replace the old ones. Use MERGE to keep the old ones together with the imported ones.'),
            'multi_values_separator' => t('list_fields', 'This is the glue for the multiple values'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return string
     */
    public function getImportValuesStrategy(): string
    {
        // @phpstan-ignore-next-line
        return !empty($this->import_values_strategy) ? (string)$this->import_values_strategy : ListFieldMultiValueAwareInterface::IMPORT_VALUES_STRATEGY_REPLACE;
    }

    /**
     * @return string
     */
    public function getMultiValuesSeparator(): string
    {
        // @phpstan-ignore-next-line
        return !empty($this->multi_values_separator) ? (string)$this->multi_values_separator : ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_DEFAULT;
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        $this->modelMetaData->getModelMetaData()->add('import_values_strategy', (string)$this->getImportValuesStrategy());
        $this->modelMetaData->getModelMetaData()->add('multi_values_separator', (string)$this->getMultiValuesSeparator());

        return parent::beforeSave();
    }

    /**
     * @return void
     */
    protected function afterFind()
    {
        $md = $this->modelMetaData->getModelMetaData();

        $this->import_values_strategy = $md->contains('import_values_strategy') ? (string)$md->itemAt('import_values_strategy') : $this->import_values_strategy;
        $this->multi_values_separator = $md->contains('multi_values_separator') ? (string)$md->itemAt('multi_values_separator') : $this->multi_values_separator;

        parent::afterFind();
    }
}
