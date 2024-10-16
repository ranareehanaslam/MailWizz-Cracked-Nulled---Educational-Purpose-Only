<?php declare(strict_types=1);

use Stiphle\Throttle\TimeWindow;

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */
class RateLimiterTimeWindow extends TimeWindow
{
    public function isOverLimit(string $key, int $limit, int $milliseconds): bool
    {
        $wait = $this->getEstimate($key, $limit, $milliseconds);

        if ($wait > 0) {
            return true;
        }

        $key = $this->getStorageKey($key, $limit, $milliseconds);
        $this->storage->lock($key);
        $count = (int) $this->storage->get($key);
        $count++;
        $this->storage->set($key, $count);
        $this->storage->unlock($key);

        return false;
    }

    public function getEstimate($key, $limit, $milliseconds)
    {
        $key = $this->getStorageKey($key, $limit, $milliseconds);
        $count = $this->storage->get($key);
        if ($count < $limit) {
            return 0;
        }

        return (int)$milliseconds - ((int)ceil((microtime(true) * 1000)) % (int) $milliseconds);
    }
}
