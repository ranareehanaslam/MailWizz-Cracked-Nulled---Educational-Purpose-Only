<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldMultiValueAwareTrait
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.6
 */
trait ListFieldMultiValueAwareTrait
{
    /**
     * @return array
     */
    public function getImportValuesStrategiesList(): array
    {
        return [
            ListFieldMultiValueAwareInterface::IMPORT_VALUES_STRATEGY_REPLACE => t('list_fields', 'Replace at import'),
            ListFieldMultiValueAwareInterface::IMPORT_VALUES_STRATEGY_MERGE   => t('list_fields', 'Merge at import'),
        ];
    }

    /**
     * @return array
     */
    public function getMultiValuesSeparatorsList(): array
    {
        $list =  [
            ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_COMMA   => ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_COMMA,
            ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_PIPE      => ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_PIPE,
            ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_SEMICOLON => ListFieldMultiValueAwareInterface::MULTI_VALUES_SEPARATOR_SEMICOLON,
        ];

        return (array)hooks()->applyFilters('list_field_multi_values_separators_list', $list);
    }

    /**
     * @return bool
     */
    public function getImportValuesStrategyIsMerge(): bool
    {
        return $this->getImportValuesStrategy() === ListFieldMultiValueAwareInterface::IMPORT_VALUES_STRATEGY_MERGE;
    }

    /**
     * @return bool
     */
    public function getImportValuesStrategyIsReplace(): bool
    {
        return $this->getImportValuesStrategy() === ListFieldMultiValueAwareInterface::IMPORT_VALUES_STRATEGY_REPLACE;
    }
}
