<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * SyncListCustomFieldsCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */

class SyncListsCustomFieldsCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        try {
            $this->stdout('Loading all lists...');

            /** @var array $lists */
            $lists = db()->createCommand('SELECT list_id FROM {{list}} WHERE `status` = "active"')->queryAll(true);

            foreach ($lists as $list) {
                (new SyncListCustomFieldsRunner())
                    ->setListId((int)$list['list_id'])
                    ->setLogger([$this, 'stdout'])
                    ->run();
            }

            $this->stdout('Done!');
        } catch (Exception $e) {
            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return 0;
    }
}
