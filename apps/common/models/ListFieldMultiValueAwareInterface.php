<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListFieldMultiValueAwareInterface
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.6
 */

/**
 * @property int $field_id
 */
interface ListFieldMultiValueAwareInterface
{
    const IMPORT_VALUES_STRATEGY_REPLACE = 'replace';
    const IMPORT_VALUES_STRATEGY_MERGE = 'merge';
    const MULTI_VALUES_SEPARATOR_COMMA = ',';
    const MULTI_VALUES_SEPARATOR_PIPE = '|';
    const MULTI_VALUES_SEPARATOR_SEMICOLON = ';';
    const MULTI_VALUES_SEPARATOR_DEFAULT = self::MULTI_VALUES_SEPARATOR_COMMA;

    /**
     * @return string
     */
    public function getImportValuesStrategy(): string;

    /**
     * @return array
     */
    public function getImportValuesStrategiesList(): array;

    /**
     * @return bool
     */
    public function getImportValuesStrategyIsMerge(): bool;

    /**
     * @return bool
     */
    public function getImportValuesStrategyIsReplace(): bool;

    /**
     * @return string
     */
    public function getMultiValuesSeparator(): string;

    /**
     * @return array
     */
    public function getMultiValuesSeparatorsList(): array;
}
