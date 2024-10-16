<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageTemplatesGridWidgets
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

class LandingPageTemplatesGridWidget extends CWidget
{
    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $templates = LandingPageHelper::getTemplates();

        if (empty($templates)) {
            return;
        }

        clientScript()->registerScriptFile(apps()->getBaseUrl('assets/js/landing-page-templates-grid.js'));

        $this->render('landing-page-templates-grid', compact('templates'));
    }
}
