<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * EmailTemplateBuilderExt
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class EmailTemplateBuilderExt extends ExtensionInit
{
    /**
     * @var string
     */
    public $name = 'Email Template Builder';

    /**
     * @var string
     */
    public $description = 'Drag and Drop Email Template Builder For MailWizz EMA';

    /**
     * @var string
     */
    public $version = '2.0.0';

    /**
     * @var string
     */
    public $minAppVersion = '2.3.0';

    /**
     * @var string
     */
    public $author = 'MailWizz Development Team';

    /**
     * @var string
     */
    public $website = 'https://www.mailwizz.com/';

    /**
     * @var string
     */
    public $email = 'support@mailwizz.com';

    /**
     * @var array
     */
    public $allowedApps = ['backend', 'customer'];

    /**
     * @var bool
     */
    protected $_canBeDeleted = false;

    /**
     * @var bool
     */
    protected $_canBeDisabled = true;

    /**
     * @inheritDoc
     */
    public function run()
    {
        /** @var CkeditorExt|null $ckeditor */
        $ckeditor = $this->getManager()->getExtensionInstance('ckeditor');

        /**
         * This extension depends on ckeditor so we need to make sure it is enabled.
         */
        if (empty($ckeditor)) {
            return;
        }

        $this->importClasses('common.models.*');

        // register the common model in container for singleton access
        container()->add(EmailTemplateBuilderExtCommon::class, EmailTemplateBuilderExtCommon::class);
        container()->add(EmailTemplateBuilderExtStripoCommon::class, EmailTemplateBuilderExtStripoCommon::class);

        /** @var EmailTemplateBuilderExtCommon $settings */
        $settings = container()->get(EmailTemplateBuilderExtCommon::class);
        $settings->getCurrentBuilderInstance()->run();

        if ($this->isAppName('backend')) {
            $this->addUrlRules([
                ['settings/index', 'pattern' => 'email-template-builder/settings'],
                ['settings/<action>', 'pattern' => 'email-template-builder/settings/*'],
            ]);

            $this->addControllerMap([
                'settings' => [
                    'class' => 'backend.controllers.EmailTemplateBuilderExtBackendSettingsController',
                ],
            ]);
        }

        if ($this->isAppName('backend') || $this->isAppName('customer')) {
            $this->addUrlRules([
                ['grapesjs_builder/upload', 'pattern' => 'email-template-builder/grapesjs/upload'],
                ['grapesjs_builder/files', 'pattern' => 'email-template-builder/grapesjs/files'],
                ['stripo/token', 'pattern' => 'email-template-builder/stripo/token'],
            ]);

            $this->addControllerMap([
                'grapesjs_builder' => [
                    'class' => 'common.controllers.EmailTemplateBuilderExtCommonGrapesjsController',
                ],
                'stripo' => [
                    'class' => 'common.controllers.EmailTemplateBuilderExtCommonStripoController',
                ],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function beforeEnable()
    {
        $this->importClasses('common.models.*');

        // register the common model in container for singleton access
        container()->add(EmailTemplateBuilderExtCommon::class, EmailTemplateBuilderExtCommon::class);
        container()->add(EmailTemplateBuilderExtStripoCommon::class, EmailTemplateBuilderExtStripoCommon::class);

        /** @var EmailTemplateBuilderExtCommon $settings */
        $settings = container()->get(EmailTemplateBuilderExtCommon::class);
        $settings->getCurrentBuilderInstance()->publishAssets();
        return true;
    }

    /**
     * @inheritDoc
     */
    public function beforeDisable()
    {
        /** @var EmailTemplateBuilderExtCommon $settings */
        $settings = container()->get(EmailTemplateBuilderExtCommon::class);
        $settings->getCurrentBuilderInstance()->unpublishAssets();
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPageUrl()
    {
        return $this->createUrl('settings/index');
    }
}
