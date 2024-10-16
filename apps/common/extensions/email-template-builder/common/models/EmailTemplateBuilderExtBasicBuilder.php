<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailVerificationExtBulkEmailCheckerCommon
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class EmailTemplateBuilderExtBasicBuilder extends EmailTemplateBuilder
{
    const BASIC_BUILDER_ID = 'basic';

    /**
     * @inheritDoc
     */
    public function __construct(ExtensionInit $extension)
    {
        parent::__construct($extension);

        $this->_assetsAlias       = 'root.frontend.assets.cache.ext-email-template-builder-basic';
        $this->_assetsRelativeUrl = '/frontend/assets/cache/ext-email-template-builder-basic';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Basic';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::BASIC_BUILDER_ID;
    }

    /**
     * @return void
     */
    public function registerAssets(): void
    {
        static $_assetsRegistered = false;
        if ($_assetsRegistered) {
            return;
        }
        $_assetsRegistered = true;

        /** @var string $assetsUrl */
        $assetsUrl = $this->getAssetsUrl();

        // find the language file, if any.
        $language     = str_replace('_', '-', app()->getLanguage());
        $languageFile = '';

        if (is_file(dirname(__FILE__) . '/../assets/' . $this->getId() . '/languages/' . $language . '.js')) {
            $languageFile = $language . '.js';
        }

        if ($languageFile === '' && strpos($language, '-') !== false) {
            $language = explode('-', $language);
            $language = $language[0];
            if (is_file(dirname(__FILE__) . '/../assets/' . $this->getId() . '/languages/' . $language . '.js')) {
                $languageFile = $language . '.js';
            }
        }

        // if language found, register it.
        if ($languageFile !== '') {
            $this->detectedLanguage = $language;
            clientScript()->registerScriptFile($assetsUrl . '/languages/' . $languageFile);
        }

        // register the rest of css/js
        clientScript()->registerCssFile($assetsUrl . '/static/css/main.c87ec30c.css');
        clientScript()->registerCssFile($assetsUrl . '/static/css/code-editor.css');
        clientScript()->registerScriptFile($assetsUrl . '/static/js/main.7a7f902f.js');
        clientScript()->registerScriptFile($assetsUrl . '/static/js/code-editor.js');
    }

    /**
     * Add the button to toggle the editor
     *
     * @param array $params
     *
     * @return void
     */
    public function _beforeWysiwygEditorRightSide(array $params = []): void
    {
        $title = $this->_extension->t('Toggle {name} template builder', [
            '{name}' => $this->getName(),
        ]);

        $toggle = CHtml::link($title, 'javascript:;', [
            'class' => 'btn btn-flat btn-primary',
            'title' => $title,
            'id'    => 'btn_' . $params['template']->getModelName() . '_content',
        ]);

        $info = CHtml::link(IconHelper::make('info'), '#page-info-toggle-template-builder', [
            'title'         => t('app', 'Info'),
            'class'         => 'btn btn-primary btn-flat no-spin',
            'data-toggle'   => 'modal',
        ]);

        echo $toggle . ' ' . $info;
    }

    /**
     * @inheritDoc
     */
    public function _afterWysiwygEditor(array $params = []): void
    {
        /** @var CustomerEmailTemplate|null $model */
        $model = null;

        if (!empty($params['template'])) {
            $model = $params['template'];
        }

        if (empty($model) || !is_object($model) || !($model instanceof ActiveRecord)) {
            return;
        }

        if (!$model->asa('modelMetaData') || !method_exists($model->modelMetaData, 'getModelMetaData')) {
            return;
        }

        /** @var string $modelName */
        $modelName = $model->getModelName();
        $builderId = $modelName . '_content';

        /** @var CkeditorExt $ckeditor */
        $ckeditor = $this->_extension->getManager()->getExtensionInstance('ckeditor');

        $options = [
            'rootId'        => 'builder_' . $builderId,
            'lang'          => $this->detectedLanguage,
            'mediaBaseUrl'  => $this->getAssetsUrl() . '/static/media/',
            'ckeditor'      => [
                'scriptUrl' => $ckeditor->getAssetsUrl() . '/ckeditor/ckeditor.js',
                'config'    => [
                    'toolbar'               => 'Emailbuilder',
                    'forcePasteAsPlainText' => true,
                ],
            ],
        ];

        // Since 2.3.2 we aren't checking if the FM is enabled, we just add the URLs
        // and the controller will show/block the request
        // See https://github.com/onetwist-software/mailwizz/issues/946
        $options['managerUrl'] = $ckeditor->getFilemanagerUrl();
        $options['ckeditor']['config']['filebrowserBrowseUrl'] = $ckeditor->getFilemanagerUrl();

        $json = [];
        $contentJson = $model->modelMetaData->getModelMetaData()->itemAt('content_json');

        if (!empty($contentJson)) {
            $contentJson = json_decode((string)base64_decode($contentJson), true);
            if (!empty($contentJson)) {
                $json = $contentJson;
                unset($contentJson);
            }
        }

        /** @var array $post */
        $post = (array)request()->getOriginalPost('', []);

        if (isset($post[$modelName]['content_json'])) {
            $contentJson = json_decode($post[$modelName]['content_json'], true);
            if (!empty($contentJson)) {
                $json = $contentJson;
                unset($contentJson);
            }
        }

        app()->getController()->renderInternal(dirname(__FILE__) . '/../views/' . $this->getId() . '/after-editor.php', [
            'json'      => $json,
            'options'   => $options,
            'modelName' => $modelName,
            'builderId' => $builderId,
            'extension' => $this->_extension,
        ]);
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _customerControllerTemplatesBeforeAction(CAction $action): void
    {
        parent::_customerControllerTemplatesBeforeAction($action);

        if (!in_array($action->getId(), ['create', 'update'])) {
            return;
        }

        // add the code to save the editor data
        hooks()->addAction('controller_action_save_data', [$this, '_controllerActionSaveData']);
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _customerControllerCampaignsBeforeAction(CAction $action): void
    {
        parent::_customerControllerCampaignsBeforeAction($action);

        if (!in_array($action->getId(), ['template'])) {
            return;
        }

        // add the code to save the editor data
        hooks()->addAction('controller_action_save_data', [$this, '_controllerActionSaveData']);
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _backendControllerEmailTemplatesGalleryBeforeAction(CAction $action): void
    {
        parent::_backendControllerEmailTemplatesGalleryBeforeAction($action);

        if (!in_array($action->getId(), ['create', 'update'])) {
            return;
        }

        // add the code to save the editor data
        hooks()->addAction('controller_action_save_data', [$this, '_controllerActionSaveData']);
    }

    /**
     * @param CAction $action
     *
     * @return void
     */
    public function _controllerExtCkeditorBeforeAction(CAction $action): void
    {
        if ($action->getId() != 'filemanager') {
            return;
        }

        // add image handling code for file manager
        hooks()->addAction('ext_ckeditor_elfinder_filemanager_view_html_head', [$this, '_extCkeditorElfinderFilemanagerViewHtmlHead']);
    }

    /**
     * @param CAttributeCollection $collection
     *
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function _controllerActionSaveData(CAttributeCollection $collection)
    {
        if (!$collection->itemAt('success')) {
            return;
        }

        /** @var ActiveRecord $template */
        $template = $collection->itemAt('template');

        /** @var array $post */
        $post = (array)request()->getOriginalPost('', []);

        if (isset($post[$template->getModelName()]['content_json'])) {
            $contentJson = $post[$template->getModelName()]['content_json'];
            if (!empty($contentJson)) {
                $contentJson = base64_encode((string)json_encode(json_decode($contentJson, true)));
                $template->modelMetaData->setModelMetaData('content_json', $contentJson)->saveModelMetaData();
            }
        }
    }

    /**
     * Render the javascript code for elfinder
     *
     * @return void
     */
    public function _extCkeditorElfinderFilemanagerViewHtmlHead()
    {
        $script = file_get_contents(dirname(__FILE__) . '/../assets/' . $this->getId() . '/static/js/code-elfinder.js');
        echo sprintf("<script>\n%s\n</script>", $script);
    }
}
