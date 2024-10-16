<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ContentBuilderExt
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class ContentBuilderExt extends ExtensionInit
{
    /**
     * @var string
     */
    public $name = 'Content Builder';

    /**
     * @var string
     */
    public $description = 'Drag and Drop Content Builder For MailWizz EMA';

    /**
     * @var string
     */
    public $version = '1.0.0';

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
    public $allowedApps = ['backend', 'customer', 'frontend'];

    /**
     * @var int
     */
    public $priority = 1010;

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
        $this->importClasses('common.models.*');
        // register the common model in container for singleton access
        container()->add(ContentBuilderExtCommon::class, ContentBuilderExtCommon::class);

        /** @var ContentBuilderExtCommon $settings */
        $settings = container()->get(ContentBuilderExtCommon::class);

        $settings->getCurrentBuilderInstance()->run();

        if ($this->isAppName('customer') || $this->isAppName('backend')) {
            $loggedIn = ($this->isAppName('customer') && !customer()->getIsGuest()) ||
                        ($this->isAppName('backend') && !user()->getIsGuest());

            if ($loggedIn) {
                $this->addUrlRules([
                    ['content_builder/upload', 'pattern' => 'content-builder/upload/<source:(\w+)>'],
                    ['content_builder/customer_lists_forms', 'pattern' => 'content-builder/customer-lists-forms'],
                    ['content_builder/get_list_subscribe_form', 'pattern' => 'content-builder/get-list-subscribe-form/<list_uid:([a-z0-9]+)>'],
                    ['content_builder/get_list_subscribe_form', 'pattern' => 'content-builder/get-list-subscribe-form'],
                ]);

                $this->addControllerMap([
                    'content_builder' => [
                        'class' => 'common.controllers.ContentBuilderExtCommonController',
                    ],
                ]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function beforeEnable()
    {
        $this->importClasses('common.models.*');

        // register the common model in container for singleton access
        container()->add(ContentBuilderExtCommon::class, ContentBuilderExtCommon::class);

        /** @var ContentBuilderExtCommon $settings */
        $settings       = container()->get(ContentBuilderExtCommon::class);
        $currentBuilder = $settings->getCurrentBuilderInstance();

        $assetsPublished = $currentBuilder->publishAssets();

        return !empty($assetsPublished);
    }

    /**
     * @inheritDoc
     * @throws CException
     */
    public function afterEnable()
    {
        /** @var ContentBuilderExtCommon $settings */
        $settings       = container()->get(ContentBuilderExtCommon::class);
        $currentBuilder = $settings->getCurrentBuilderInstance();
        $currentBuilder->insertTemplates();
    }

    /**
     * @inheritDoc
     * @throws CException
     */
    public function beforeDisable()
    {
        $this->importClasses('common.models.*');

        // register the common model in container for singleton access
        container()->add(ContentBuilderExtCommon::class, ContentBuilderExtCommon::class);

        /** @var ContentBuilderExtCommon $settings */
        $settings = container()->get(ContentBuilderExtCommon::class);
        $settings->getCurrentBuilderInstance()->unpublishAssets();
        return true;
    }
}
