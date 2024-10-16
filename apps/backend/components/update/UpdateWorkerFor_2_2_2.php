<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * UpdateWorkerFor_2_2_2
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.2
 */

class UpdateWorkerFor_2_2_2 extends UpdateWorkerAbstract
{
    public function run()
    {
        // run the sql from file
        $this->runQueriesFromSqlFile('2.2.2');

        // import new translations strings
        $this->runImportTranslationsFromJsonFile('2.2.2');

        /** @var  OptionCronProcessSubscribers $optionCronProcessSubscribers */
        $optionCronProcessSubscribers = container()->get(OptionCronProcessSubscribers::class);
        $optionCronProcessSubscribers->sync_custom_fields_values = OptionCronProcessSubscribers::TEXT_YES;
        $optionCronProcessSubscribers->save();
    }
}
