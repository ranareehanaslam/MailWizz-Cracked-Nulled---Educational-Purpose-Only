<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Ai Assistant
 *
 * An AI content generator/chat implementation
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
class AiAssistantExt extends ExtensionInit
{
    private const CSS_FILE_NAME = 'index-ef2ec0d1.css';
    private const JS_FILE_NAME = 'index-990ebd12.js';
    private const JS_POLYFILLS_LEGACY_FILE_NAME = 'polyfills-legacy-4bfdec30.js';
    private const JS_LEGACY_FILE_NAME = 'index-legacy-2faa2462.js';

    /**
     * @var string
     */
    public $name = 'AI Assistant';

    /**
     * @var string
     */
    public $description = 'An AI content generator/chat implementation';

    /**
     * @var string
     */
    public $version = '2.0.1';

    /**
     * @var string
     */
    public $minAppVersion = '2.0.0';

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
    public $cliEnabled = false;

    /**
     * @var bool
     */
    protected $_canBeDeleted = false;

    /**
     * @var string
     */
    private $_assetsUrl = '';

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->importClasses('common.models.*');

        // register the common models in container for singleton access
        container()->add(AiAssistantExtCommon::class, AiAssistantExtCommon::class);
        container()->add(AiAssistantExtCustomer::class, AiAssistantExtCustomer::class);

        /** @var AiAssistantExtCommon $commonSettings */
        $commonSettings = container()->get(AiAssistantExtCommon::class);
        if ($commonSettings->must_count_tokens) {
            // @tokensCount - We do not use the composer one, since we want to modify it because it has some output as an example.
            require_once MW_APPS_PATH . '/common/extensions/ai-assistant/common/vendor/coderevolutionplugins/gpt-3-encoder-php/gpt3-encoder.php';
        }

        if ($this->isAppName('backend')) {
            // handle all backend related tasks
            $this->backendApp();
        } elseif ($this->isAppName('customer')) {
            // handle all customer related tasks
            $this->customerApp();
        }
    }

    /**
     * @inheritDoc
     */
    public function getPageUrl()
    {
        return $this->createUrl('settings/index');
    }

    /**
     * @inheritDoc
     */
    public function beforeEnable()
    {
        $install = $this->runQueriesFromSqlFile($this->getPathOfAlias('common.data.sql') . '/install.sql');

        if (!$this->getOption('install.insert', 0)) {
            $this->runQueriesFromSqlFile($this->getPathOfAlias('common.data.sql') . '/insert.sql');
            $this->setOption('install.insert', 1);
        }

        return $install;
    }

    public function update()
    {
        $updateMap = [
            '2.0.1' => $this->getPathOfAlias('common.data.sql') . '/update-2.0.1.sql',
        ];

        foreach ($updateMap as $version => $sqlFile) {
            $optionKey = sprintf('update.version_%s', (string)str_replace('.', '_', $version));
            if ($this->getOption($optionKey, 0)) {
                continue;
            }
            $this->runQueriesFromSqlFile($sqlFile);
            $this->setOption($optionKey, 1);
        }

        return parent::update();
    }

    /**
     * @inheritDoc
     */
    public function beforeDelete()
    {
        return $this->runQueriesFromSqlFile($this->getPathOfAlias('common.data.sql') . '/delete.sql');
    }

    /**
     * @param array $menuItems
     *
     * @return array
     */
    public function _customerLeftNavigationMenuItems(array $menuItems): array
    {
        if (!isset($menuItems['settings'])) {
            return $menuItems;
        }

        if (is_string($menuItems['settings']['active'])) {
            $menuItems['settings']['active'] = [$menuItems['settings']['active']];
        }
        $menuItems['settings']['active'][] = $this->getRoute('settings');

        $route = app()->getController()->getRoute();

        $menuItems['settings']['items'][] = [
            'url'    => $this->createUrl('settings/index'),
            'label'  => $this->t('AI Assistant'),
            'active' => strpos($route, $this->getRoute('settings')) === 0,
        ];
        return $menuItems;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function _registerBackendMenuItem(array $items): array
    {
        $route = app()->getController()->getRoute();

        $item = [
            'name'   => $this->t('AI Assistant'),
            'icon'   => 'glyphicon-comment',
            'active' => $this->getRoute(''),
            'route'  => null,
            'items'  => [
                ['url'    => [$this->getRoute('topics/index')],
                 'label'  => $this->t('Topics'),
                 'active' => strpos($route, $this->getRoute('topics')) === 0,
                ],
                ['url'    => [$this->getRoute('settings/index')],
                 'label'  => t('app', 'Settings'),
                 'active' => strpos($route, $this->getRoute('settings')) === 0,
                ],
            ],
        ];

        $items['ai-assistant'] = $item;

        return $items;
    }

    /**
     * @return string
     * @throws CException
     */
    public function getAssetsUrl(): string
    {
        if ($this->_assetsUrl !== '') {
            return $this->_assetsUrl;
        }
        return $this->_assetsUrl = assetManager()->publish(dirname(__FILE__) . '/common/assets', false, -1, MW_DEBUG);
    }

    /**
     * @param CList $styles
     *
     * @return CList
     * @throws CException
     */
    public function _registerStyles(CList $styles): CList
    {
        $styles->add(['src' => $this->getAssetsUrl() . '/static/css/' . self::CSS_FILE_NAME, 'tags' => ['ai-assistant']]);

        return $styles;
    }

    /**
     * @param Controller $controller
     *
     * @return void
     * @throws CException
     */
    public function _registerAssistant($controller)
    {
        /** @var OptionCommon $optionCommon */
        $optionCommon = container()->get(OptionCommon::class);

        /** @var AiAssistantExtCommon $commonSettings */
        $commonSettings = container()->get(AiAssistantExtCommon::class);

        $secretKey              = $commonSettings->getSecretAccessKey();
        $canManageOpenAiAccount = true;

        if (apps()->isAppName('customer')) {
            /** @var AiAssistantExtCustomer $customerSettings */
            $customerSettings = container()->get(AiAssistantExtCustomer::class);

            $secretKey              = $customerSettings->getSecretAccessKey();
            $canManageOpenAiAccount = $commonSettings->getCanCustomersAddAccount();
        }

        $controller->renderFile($this->getPathOfAlias('common.views.assistant') . '.php', [
            'moduleIndexSrc'     => $this->getAssetsUrl() . '/static/js/' . self::JS_FILE_NAME,
            'polyFillsLegacySrc' => $this->getAssetsUrl() . '/static/js/' . self::JS_POLYFILLS_LEGACY_FILE_NAME,
            'indexLegacySrc'     => $this->getAssetsUrl() . '/static/js/' . self::JS_LEGACY_FILE_NAME,

            'apiBaseUrl'             => apps()->getAppUrl('', '', true),
            'siteName'               => $optionCommon->getSiteName(),
            'hasSecretKey'           => !empty($secretKey),
            'canManageOpenAiAccount' => $canManageOpenAiAccount,
        ]);
    }

    /**
     * Handle backend app
     *
     * @return void
     */
    protected function backendApp()
    {
        if (!($user = user()->getModel())) {
            return;
        }

        // register the url rule(s) to resolve the extension page(s).
        $this->addUrlRules([
            ['topics/<action>', 'pattern' => 'ai-assistant/topics/<action>/*'],
            ['settings/index', 'pattern' => 'ai-assistant/settings'],
        ]);

        // add the controllers
        $this->addControllerMap([
            'topics'   => [
                'class' => 'backend.controllers.AiAssistantExtBackendTopicsController',
            ],
            'settings' => [
                'class' => 'backend.controllers.AiAssistantExtBackendSettingsController',
            ],
        ]);
        /** @var AiAssistantExtCommon $model */
        $model = container()->get(AiAssistantExtCommon::class);

        if (!$model->getIsEnabled()) {
            return;
        }

        // add the menu item
        hooks()->addFilter('backend_left_navigation_menu_items', [$this, '_registerBackendMenuItem']);

        $this->addAssistant();
    }

    /**
     * Handle all customer related tasks
     *
     * @return void
     */
    protected function customerApp()
    {
        /** @var AiAssistantExtCommon $model */
        $model = container()->get(AiAssistantExtCommon::class);

        if (!($customer = customer()->getModel())) {
            return;
        }

        if (!$model->checkCustomerAccess($customer)) {
            return;
        }

        if ($model->getCanCustomersAddAccount()) {
            /** register the url rule to resolve the pages */
            $this->addUrlRules([
                ['settings/index', 'pattern' => 'ai-assistant/settings'],
                ['settings/<action>', 'pattern' => 'ai-assistant/settings/*'],
            ]);

            /** add the controllers */
            $this->addControllerMap([
                'settings' => [
                    'class' => 'customer.controllers.AiAssistantExtCustomerSettingsController',
                ],
            ]);

            hooks()->addFilter('customer_left_navigation_menu_items', [$this, '_customerLeftNavigationMenuItems']);
        }

        $this->addAssistant();
    }

    /**
     * @return void
     */
    protected function addAssistant(): void
    {
        /** register the url rule to resolve the pages */
        $this->addUrlRules([
            ['conversations/index', 'pattern' => 'ai-assistant/conversations/index', 'verb' => 'GET'],
            ['conversations/topics', 'pattern' => 'ai-assistant/conversations/topics', 'verb' => 'GET'],
            ['conversations/settings', 'pattern' => 'ai-assistant/conversations/settings', 'verb' => 'GET, POST'],
            ['conversations/create', 'pattern' => 'ai-assistant/conversations/create', 'verb' => 'POST'],
            [
                'conversations/update',
                'pattern' => 'ai-assistant/conversations/<conversation_id:(\d+)>/update',
                'verb'    => 'POST, PATCH',
            ],
            [
                'conversations/delete',
                'pattern' => 'ai-assistant/conversations/<conversation_id:(\d+)>/delete',
                'verb'    => 'POST',
            ],
            [
                'conversations/create_message',
                'pattern' => 'ai-assistant/conversations/<conversation_id:(\d+)>/messages',
                'verb'    => 'POST',
            ],
            [
                'conversations/get_messages',
                'pattern' => 'ai-assistant/conversations/<conversation_id:(\d+)>/messages',
                'verb'    => 'GET',
            ],
            [
                'conversations/get_open_ai_response',
                'pattern' => 'ai-assistant/conversations/<conversation_id:(\d+)>/ask',
                'verb'    => 'GET',
            ],

        ]);

        /** add the controllers */
        $this->addControllerMap([
            'conversations' => [
                'class' => 'common.controllers.AiAssistantExtCommonConversationsController',
            ],
        ]);

        /**
         * Register the assistant
         */
        hooks()->addAction('layout_footer_html', [$this, '_registerAssistant']);

        /**
         * Register the asset files
         */
        hooks()->addFilter('register_styles', [$this, '_registerStyles']);

        /**
         * Whitelist the CSS
         */
        hooks()->addFilter('ext_content_builder_register_styles_allowed_styles_tags', function (array $allowedTags = []) {
            $allowedTags[] = 'ai-assistant';

            return $allowedTags;
        });
    }
}
