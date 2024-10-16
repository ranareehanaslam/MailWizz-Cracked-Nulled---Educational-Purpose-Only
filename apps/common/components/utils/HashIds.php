<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * HashIds
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.1.20
 */
class HashIds extends CApplicationComponent
{
    /**
     * @var string
     */
    public $salt = '{HASHIDS_SALT}';

    /**
     * @var int
     */
    public $minHashLength = 3;

    /**
     * @var string
     */
    public $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';

    /**
     * @var \Hashids\Hashids
     */
    protected $hasher;

    /**
     * @return \Hashids\Hashids
     */
    public function getHasher(): Hashids\Hashids
    {
        if ($this->hasher === null) {
            $this->hasher = new \Hashids\Hashids($this->salt, $this->minHashLength, $this->alphabet);
        }
        return $this->hasher;
    }

    /**
     * @param int $id
     * @return string
     */
    public function encode(int $id): string
    {
        return $this->getHasher()->encode($id);
    }

    /**
     * @param string $hash
     * @return int
     */
    public function decode(string $hash): int
    {
        return $this->getHasher()->decode($hash)[0] ?? 0;
    }
}
