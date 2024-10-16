<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeleteExpiredSessionsCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.9
 */

class DeleteExpiredSessionsCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        $this->stdout('Deleting expired sessions...');

        $start = microtime(true);

        db()->createCommand()->delete('{{session}}', '`expire` < :now', [
            ':now' => time(),
        ]);

        $timeTook  = round(microtime(true) - $start, 4);

        $this->stdout(sprintf('DONE, took %s seconds!', $timeTook));
        return 0;
    }
}
