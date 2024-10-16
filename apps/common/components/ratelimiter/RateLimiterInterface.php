<?php declare(strict_types=1);

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */
interface RateLimiterInterface
{
    public function isOverLimit(string $key, int $limit, int $milliseconds): bool;
}
