<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * ContentBuilder
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
abstract class ContentBuilder
{

    /**
     * @var ExtensionInit
     */
    public $_extension;
    /**
     * @var string
     */
    protected $_assetsAlias = '';

    /**
     * @var string
     */
    protected $_assetsRelativeUrl = '';

    /**
     * @var string
     */
    protected $_assetsUrl = '';

    /**
     * @var string
     */
    protected $detectedLanguage = 'en';

    /**
     * ContentBuilder constructor.
     * @param ExtensionInit $extension
     */
    public function __construct(ExtensionInit $extension)
    {
        $this->_extension = $extension;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return string
     */
    abstract public function getId(): string;

    /**
     * @param array $editorOptions
     *
     * @return void
     */
    abstract public function _createNewEditorInstance(array $editorOptions = []): void;

    /**
     *
     * @param array $params
     *
     * @return array
     * @throws CException
     */
    abstract public function getBuilderOptions(array $params = []): array;

    /**
     * @param array $allowedTags
     * @return void
     */
    abstract public function registerFrontendStyles(array $allowedTags = []): void;

    /**
     * @param array $allowedTags
     * @return void
     */
    abstract public function registerFrontendScripts(array $allowedTags = []): void;

    /**
     * @return void
     */
    public function run(): void
    {
        /**
         * Hooks that allow the use of the content builder from other extensions that will make use of it in different
         * application areas
         */

        if ($this->_extension->isAppName('backend')) {
            hooks()->doAction('ext_content_builder_backend_area_handler', $this);
        } elseif ($this->_extension->isAppName('customer')) {
            hooks()->doAction('ext_content_builder_customer_area_handler', $this);
        } elseif ($this->_extension->isAppName('frontend')) {
            hooks()->doAction('ext_content_builder_frontend_area_handler', $this);
        }
    }

    /**
     * @param array $params
     * @return void
     * @throws CException
     */
    public function _addContentBuilder(array $params = []): void
    {
        /** @var Controller|null $controller */
        $controller = $params['controller'] ?? null;

        if (empty($controller)) {
            return;
        }
        $options = $this->getBuilderOptions($params);

        app()->getController()->renderInternal($this->getBuilderViewFile(), [
            'options'          => $options,
            'uploadHandlerUrl' => $this->_extension->createUrl('content_builder/upload', ['source' => $controller->getId()]),
        ]);
    }

    /**
     * @return string
     */
    public function getBuilderViewFile(): string
    {
        return $this->_extension->getPathOfAlias(sprintf('common.views.%s.content-builder', $this->getId())) . '.php';
    }

    /**
     * @param Controller $controller
     * @return void
     */
    public function renderContentBuilder(Controller $controller): void
    {
        $this->_createNewEditorInstance();

        // add the code to handle the editor
        hooks()->addAction('ext_content_builder_add_builder_content', [$this, '_addContentBuilder']);

        $controller->layout = $this->_extension->getPathAlias('common.views.common.layout');
        $controller->render($this->_extension->getPathAlias('common.views.common.build'));
    }

    /**
     * @return void
     * @throws CException
     */
    public function renderFrontendView(Controller $controller)
    {
        $controller->renderFile($this->_extension->getPathOfAlias(sprintf('frontend.views.%s.view', $this->getId())) . '.php');
    }

    /**
     * @return string
     */
    public function publishAssets(): string
    {
        $src     = $this->getSrcAssetsPath();
        $dst     = (string)Yii::getPathOfAlias($this->getAssetsAlias());
        $isDebug = MW_DEBUG;
        // @phpstan-ignore-next-line
        if (is_dir($dst) && empty($isDebug)) {
            return $this->setAssetsUrl($this->getAssetsAbsoluteUrl());
        }

        CFileHelper::copyDirectory($src, $dst, ['newDirMode' => 0777]);
        return $this->setAssetsUrl($this->getAssetsAbsoluteUrl());
    }

    /**
     * Unpublish assets
     *
     * @return void
     * @throws CException
     */
    public function unpublishAssets(): void
    {
        $dst = (string)Yii::getPathOfAlias($this->getAssetsAlias());
        if (is_dir($dst)) {
            CFileHelper::removeDirectory($dst);
        }
        $this->removeTemplates();
    }

    /**
     * @param string $url
     * @return string
     */
    public function setAssetsUrl(string $url): string
    {
        return $this->_assetsUrl = $url;
    }

    /**
     * @return string
     */
    public function getAssetsAlias(): string
    {
        return $this->_assetsAlias;
    }

    /**
     * @return string
     */
    public function getAssetsRelativeUrl(): string
    {
        return apps()->getAppUrl('frontend', (string)$this->_assetsRelativeUrl, false, true);
    }

    /**
     * @return string
     */
    public function getAssetsAbsoluteUrl(): string
    {
        return apps()->getAppUrl('frontend', (string)$this->_assetsRelativeUrl, true, true);
    }

    /**
     * @return string
     */
    public function getAssetsUrl(): string
    {
        if ($this->_assetsUrl !== '') {
            return $this->_assetsUrl;
        }

        return $this->publishAssets();
    }

    /**
     * @return string
     */
    public function getFrontendLayoutAlias(): string
    {
        return $this->_extension->getPathAlias('frontend.views.' . $this->getId() . '.layout');
    }

    /**
     * @param string $append
     * @return string
     */
    public function getSrcAssetsPath(string $append = ''): string
    {
        return $this->_extension->getPathOfAlias($this->getSrcAssetsAlias($append));
    }

    /**
     * @param string $append
     * @return string
     */
    public function getSrcAssetsAlias(string $append = ''): string
    {
        if (empty($append)) {
            return sprintf('common.assets.%s', $this->getId());
        }
        return sprintf('common.assets.%s.%s', $this->getId(), $append);
    }

    /**
     * @return void
     * @throws CException
     */
    public function insertTemplates(): void
    {
        hooks()->doAction('ext_content_builder_insert_templates', new CAttributeCollection([
            'builder'       => $this,
            'templatesPath' => $this->getSrcAssetsPath('templates'),
        ]));
    }

    /**
     * @return void
     * @throws CException
     */
    public function removeTemplates(): void
    {
        hooks()->doAction('ext_content_builder_remove_templates', new CAttributeCollection([
            'builder' => $this,
        ]));
    }
}
