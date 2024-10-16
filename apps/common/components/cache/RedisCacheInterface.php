<?php declare(strict_types=1);
/**
 * RedisCacheInterface
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */

interface RedisCacheInterface extends ICache
{
    /**
     * @return Predis\Client
     */
    public function getClient(): Predis\Client;
}
