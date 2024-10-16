<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageVariantsListWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class LandingPageVariantsListWidget extends CWidget
{
    /**
     * @var LandingPage
     */
    public $page;

    /**
     * @var LandingPageRevisionVariant[]
     */
    public $variants;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $showPublishedVariant = true;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        if (empty($this->title)) {
            $this->title = t('landing_pages', 'Variants');
        }

        $page     = $this->page;
        $variants = $this->variants;
        $title    = $this->title;

        $publishedVariant = null;
        if ($this->showPublishedVariant) {
            $publishedVariant = $this->page->pickPublishedVariant();
        }
        clientScript()->registerScriptFile(AssetsUrl::js('landing-page-variants-list.js'));

        $this->render('landing-page-variants-list', compact('title', 'page', 'variants', 'publishedVariant'));
    }
}
