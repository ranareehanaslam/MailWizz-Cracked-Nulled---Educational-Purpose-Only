<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageUrlsWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

class LandingPageUrlsWidget extends CWidget
{
    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        clientScript()->registerScriptFile(apps()->getBaseUrl('assets/js/landing-page-urls.js'));
    }

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $this->render('landing-page-urls');
    }
}
