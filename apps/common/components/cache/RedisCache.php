<?php declare(strict_types=1);

/**
 * RedisCache
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class RedisCache extends CCache implements RedisCacheInterface
{
    /**
     * @var string
     */
    public $hostname = '127.0.0.1';

    /**
     * @var int
     */
    public $port = 6379;

    /**
     * @var int
     */
    public $database = 1;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $scheme = 'tcp';

    /**
     * @var string
     */
    public $path = '';

    /**
     * @var Doctrine\Common\Cache\PredisCache
     */
    protected $_cache;

    /**
     * @var Predis\Client
     */
    protected $_client;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        // disable the serializer
        $this->serializer = false;
    }

    /**
     * @return Doctrine\Common\Cache\PredisCache
     */
    public function getCache()
    {
        if ($this->_cache !== null) {
            return $this->_cache;
        }

        return $this->_cache = new Doctrine\Common\Cache\PredisCache($this->getClient());
    }

    /**
     * @return Predis\Client
     */
    public function getClient(): Predis\Client
    {
        if ($this->_client !== null) {
            return $this->_client;
        }

        $params = [
            'scheme' => $this->scheme,
            'database' => $this->database,
            'read_write_timeout' => 0,
        ];

        if (!$this->path) {
            $params = CMap::mergeArray($params, [
                'host' => $this->hostname,
                'port' => $this->port,
            ]);
        } else {
            $params = CMap::mergeArray($params, [
                'path' => $this->path,
            ]);
        }

        if (!empty($this->username)) {
            $params['username'] = $this->username;
        }

        if (!empty($this->password)) {
            $params['password'] = $this->password;
        }

        $this->_client = new Predis\Client($params);

        return $this->_client;
    }

    /**
     * @param bool $active
     *
     * @return void
     */
    public function setConnectionActive($active = true)
    {
        if ($active) {
            $this->getCache();
        } elseif ($this->_client !== null) {
            $this->_client->disconnect();
        }
    }

    /**
     * @inheritDoc
     */
    protected function getValue($key)
    {
        // @phpstan-ignore-next-line
        return $this->getCache()->fetch($key);
    }

    /**
     * @inheritDoc
     */
    protected function setValue($key, $value, $expire = 0)
    {
        return $this->getCache()->save($key, $value, $expire);
    }

    /**
     * @inheritDoc
     */
    protected function addValue($key, $value, $expire = 0)
    {
        if ($this->getCache()->contains($key)) {
            return false;
        }
        return $this->getCache()->save($key, $value, $expire);
    }

    /**
     * @inheritDoc
     */
    protected function deleteValue($key)
    {
        return $this->getCache()->delete($key);
    }

    /**
     * @inheritDoc
     */
    protected function flushValues()
    {
        return $this->getCache()->flushAll();
    }
}
