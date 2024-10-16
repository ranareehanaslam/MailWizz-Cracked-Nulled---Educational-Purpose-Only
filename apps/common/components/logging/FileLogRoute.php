<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * FileLogRoute
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.3
 */

class FileLogRoute extends CFileLogRoute
{
    /**
     * Formats a log message given different fields.
     * @param string $message message content
     * @param integer $level message level
     * @param string $category message category
     * @param integer $time timestamp
     * @return string formatted message
     */
    protected function formatLogMessage($message, $level, $category, $time)
    {
        if (!is_cli()) {
            $ip = request()->getUserHostAddress();
            return @date('Y/m/d H:i:s', (int)$time) . " [$level] [$category] [$ip] $message\n";
        }
        return @date('Y/m/d H:i:s', (int)$time) . " [$level] [$category] $message\n";
    }

    /**
     * Saves log messages in files.
     * @param array $logs list of log messages
     */
    protected function processLogs($logs)
    {
        // they produce just too much noise
        $messagePatterns = [
            '#SHOW FULL COLUMNS FROM `(.*)?campaign_delivery_log_campaign_(\d)+`#',
            '#SHOW FULL COLUMNS FROM `(.*)?list_field_value_list_(\d)+`#',
        ];

        foreach ($logs as $index => $log) {
            $message = $log[0];
            foreach ($messagePatterns as $messagePattern) {
                if (preg_match($messagePattern, $message)) {
                    unset($logs[$index]);
                    break;
                }
            }
        }
        $logs = array_values($logs);

        parent::processLogs($logs);
    }
}
