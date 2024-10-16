<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ModelHashIdsTrait
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.1.20
 */

trait ModelHashIdsTrait
{
    /**
     * @return string
     */
    public function getHashId(): string
    {
        return (string)hashIds()->encode((int)$this->getPrimaryKey());
    }

    /**
     * @param string $hash
     * @return int
     */
    public static function decodeHashId(string $hash): int
    {
        return (int)hashIds()->decode($hash);
    }
}
