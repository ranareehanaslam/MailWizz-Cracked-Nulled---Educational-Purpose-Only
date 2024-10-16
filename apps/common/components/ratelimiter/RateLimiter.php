<?php declare(strict_types=1);

use Doctrine\Common\Cache\FilesystemCache;
use Stiphle\Storage\DoctrineCache;

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */
class RateLimiter extends CApplicationComponent implements RateLimiterInterface
{
    /**
     * @var RateLimiterTimeWindow
     */
    private $rateLimiterTimeWindow;

    public function __construct()
    {
        $this->rateLimiterTimeWindow = new RateLimiterTimeWindow();
        if (cache() instanceof RedisCacheInterface) {
            /** @var Predis\Client $redisClient */
            $redisClient = cache()->getClient();
            $storage = new \Stiphle\Storage\Redis($redisClient);
        } else {
            $fsCache = new FilesystemCache(Yii::getPathOfAlias('common.runtime.ratelimiter'));
            $storage = new DoctrineCache($fsCache);
        }
        $this->rateLimiterTimeWindow->setStorage($storage);
    }

    public function isOverLimit(string $key, int $limit, int $milliseconds): bool
    {
        return $this->rateLimiterTimeWindow->isOverLimit($key, $limit, $milliseconds);
    }
}
