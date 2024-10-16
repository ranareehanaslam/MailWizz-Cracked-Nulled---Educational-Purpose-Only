<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * SerializeHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.6
 */

class SerializeHelper
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function serialize($value): string
    {
        return serialize($value);
    }

    /**
     * @param string $data
     * @param array $options
     *
     * @return mixed
     */
    public static function unserialize(string $data, array $options = [])
    {
        $options = CMap::mergeArray([
            'allowed_classes' => false,
        ], $options);

        $unserialized = @unserialize($data, $options);
        $booleanFalse = 'b:0;';
        if ($data !== $booleanFalse && $unserialized === false) {
            Yii::log(sprintf('Unable to unserialize data: %s', $data), CLogger::LEVEL_ERROR);

            $data = (string)preg_replace_callback('/s:(\d+):"(.*?)";/sx', function (array $match): string {
                return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
            }, $data);

            $unserialized = unserialize($data, $options);
        }

        return $unserialized;
    }
}
