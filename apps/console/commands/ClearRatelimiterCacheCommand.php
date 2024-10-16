<?php declare(strict_types=1);

if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

use Doctrine\Common\Cache\FilesystemCache;

/**
 * ClearRatelimiterCacheCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 *
 */

class ClearRatelimiterCacheCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        $result = 0;

        try {
            hooks()->doAction('console_command_clear_ratelimiter_cache_before_process', $this);

            $result = $this->process();

            hooks()->doAction('console_command_clear_ratelimiter_cache_after_process', $this);
        } catch (Exception $e) {
            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function process()
    {
        $this->stdout('Calling FilesystemCache::flushAll()...');

        $fsCache = new FilesystemCache(Yii::getPathOfAlias('common.runtime.ratelimiter'));
        $fsCache->flushAll();

        $this->stdout('DONE.');

        return 0;
    }
}
