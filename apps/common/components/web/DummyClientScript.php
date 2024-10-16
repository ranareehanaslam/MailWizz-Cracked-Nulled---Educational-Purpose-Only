<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DummyClientScript
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.7
 */

class DummyClientScript extends ClientScript
{
    /**
     * @inheritDoc
     */
    public function registerCoreScript($name)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerCssFile($url, $media='')
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerCss($id, $css, $media='')
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerScriptFile($url, $position=null, array $htmlOptions=[])
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerScript($id, $script, $position=null, array $htmlOptions=[])
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerMetaTag($content, $name=null, $httpEquiv=null, $options=[], $id=null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerLinkTag($relation=null, $type=null, $href=null, $media=null, $options=[])
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPackage($name, $definition)
    {
        return $this;
    }
}
