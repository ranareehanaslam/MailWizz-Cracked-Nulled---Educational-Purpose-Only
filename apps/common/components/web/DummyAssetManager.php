<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DummyAssetManager
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.7
 */

class DummyAssetManager extends CAssetManager
{

    /**
     * @inheritDoc
     */
    public function publish($path, $hashByName=false, $level=-1, $forceCopy=null)
    {
        return '';
    }
}
