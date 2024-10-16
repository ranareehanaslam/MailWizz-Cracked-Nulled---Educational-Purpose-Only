<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilder
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
abstract class EmailTemplateBuilder
{
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
     * @var ExtensionInit
     */
    protected $_extension;

    /**
     * EmailTemplateBuilder constructor.
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
     * @return void
     */
    abstract public function registerAssets(): void;

    /**
     * Add the button to toggle the editor
     *
     * @param array $params
     *
     * @return void
     */
    abstract public function _beforeWysiwygEditorRightSide(array $params = []): void;

    /**
     * The view after ckeditor
     *
     * @param array $params
     *
     * @return void
     * @throws CException
     */
    abstract public function _afterWysiwygEditor(array $params = []): void;

    /**
     * @return void
     */
    public function run(): void
    {
        /**
         * Customer area only
         */
        if ($this->_extension->isAppName('customer')) {

            /**
             * Register the instance as late as possible to avoid JS conflicts
             */
            hooks()->addAction('customer_controller_before_action', [$this, '_customerControllerBeforeAction']);

            /**
             * Handle the builder for customer area, in the templates controller
             */
            hooks()->addAction('customer_controller_templates_before_action', [$this, '_customerControllerTemplatesBeforeAction']);

            /**
             * Handle the builder for customer area, in the campaigns controller
             */
            hooks()->addAction('customer_controller_campaigns_before_action', [$this, '_customerControllerCampaignsBeforeAction']);

            /**
             * CKEditor controller
             */
            hooks()->addAction('customer_controller_ckeditor_ext_ckeditor_before_action', [$this, '_controllerExtCkeditorBeforeAction']);
        }

        /**
         * Backend area only
         */
        if ($this->_extension->isAppName('backend')) {

            /**
             * Register the instance as late as possible to avoid JS conflicts
             */
            hooks()->addAction('backend_controller_before_action', [$this, '_backendControllerBeforeAction']);

            /**
             * Handle the builder for backend area, in the email templates gallery controller
             */
            hooks()->addAction('backend_controller_email_templates_gallery_before_action', [$this, '_backendControllerEmailTemplatesGalleryBeforeAction']);

            /**
             * CKEditor controller
             */
            hooks()->addAction('backend_controller_ckeditor_ext_ckeditor_before_action', [$this, '_controllerExtCkeditorBeforeAction']);
        }
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _customerControllerBeforeAction(CAction $action)
    {
        $allowedRoutes = ['templates/create', 'templates/update', 'campaigns/template'];
        if (in_array(controller()->getRoute(), $allowedRoutes)) {
            /**
             * Register the assets just after ckeditor is done.
             */
            hooks()->addAction('wysiwyg_editor_instance', [$this, '_createNewEditorInstance'], 99);
        }
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _customerControllerTemplatesBeforeAction(CAction $action): void
    {
        if (!in_array($action->getId(), ['create', 'update'])) {
            return;
        }

        // add the button
        hooks()->addAction('before_wysiwyg_editor_right_side', [$this, '_beforeWysiwygEditorRightSide']);

        // add the code to handle the editor
        hooks()->addAction('after_wysiwyg_editor', [$this, '_afterWysiwygEditor']);
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _customerControllerCampaignsBeforeAction(CAction $action): void
    {
        if (!in_array($action->getId(), ['template'])) {
            return;
        }

        // add the button
        hooks()->addAction('before_wysiwyg_editor_right_side', [$this, '_beforeWysiwygEditorRightSide']);

        // add the code to handle the editor
        hooks()->addAction('after_wysiwyg_editor', [$this, '_afterWysiwygEditor']);
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _backendControllerBeforeAction(CAction $action)
    {
        $allowedRoutes = ['email_templates_gallery/create', 'email_templates_gallery/update'];
        if (in_array(controller()->getRoute(), $allowedRoutes)) {
            /**
             * Register the assets just after ckeditor is done.
             */
            hooks()->addAction('wysiwyg_editor_instance', [$this, '_createNewEditorInstance'], 99);
        }
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _backendControllerEmailTemplatesGalleryBeforeAction(CAction $action): void
    {
        if (!in_array($action->getId(), ['create', 'update'])) {
            return;
        }

        // add the button
        hooks()->addAction('before_wysiwyg_editor_right_side', [$this, '_beforeWysiwygEditorRightSide']);

        // add the code to handle the editor
        hooks()->addAction('after_wysiwyg_editor', [$this, '_afterWysiwygEditor']);
    }

    /**
     * @return string
     */
    public function publishAssets(): string
    {
        $src = dirname(__FILE__) . '/../assets/' . $this->getId() . '/';
        $dst = (string)Yii::getPathOfAlias($this->getAssetsAlias());

        // @phpstan-ignore-next-line
        $isDebug = defined('MW_DEBUG') && MW_DEBUG;
        // @phpstan-ignore-next-line
        if (is_dir($dst) && !$isDebug) {
            return $this->setAssetsUrl($this->getAssetsAbsoluteUrl());
        }

        CFileHelper::copyDirectory($src, $dst, ['newDirMode' => 0777]);
        return $this->setAssetsUrl($this->getAssetsAbsoluteUrl());
    }

    /**
     * Unpublish assets
     *
     * @return void
     */
    public function unpublishAssets(): void
    {
        $dst = (string)Yii::getPathOfAlias($this->getAssetsAlias());
        if (is_dir($dst)) {
            CFileHelper::removeDirectory($dst);
        }
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
     * @param array $editorOptions
     *
     * @return void
     */
    public function _createNewEditorInstance(array $editorOptions = []): void
    {
        $this->registerAssets();
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
     * @param CAction $action
     *
     * @return void
     */
    public function _controllerExtCkeditorBeforeAction(CAction $action): void
    {
    }
}
