<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExtStripoBuilder
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class EmailTemplateBuilderExtStripoBuilder extends EmailTemplateBuilder
{
    const STRIPO_BUILDER_ID = 'stripo';

    /**
     * @inheritDoc
     */
    public function __construct(ExtensionInit $extension)
    {
        parent::__construct($extension);
        $this->_assetsAlias       = 'root.frontend.assets.cache.ext-email-template-builder-stripo';
        $this->_assetsRelativeUrl = '/frontend/assets/cache/ext-email-template-builder-stripo';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Stripo';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::STRIPO_BUILDER_ID;
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


        // register the rest of css/js
        clientScript()->registerCssFile($assetsUrl . '/static/code-editor.css');

        clientScript()->registerScriptFile('https://plugins.stripo.email/static/latest/stripo.js');
        clientScript()->registerScriptFile($assetsUrl . '/static/code-editor.js');
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
        $options = [
            'class' => 'btn btn-flat btn-primary',
            'title' => $title,
            'id'    => 'btn_' . $params['template']->getModelName() . '_content',
        ];

        echo CHtml::link($title, 'javascript:;', $options);
    }

    /**
     * @inheritDoc
     */
    public function _afterWysiwygEditor(array $params = []): void
    {
        /** @var CustomerEmailTemplate|CampaignTemplate|null $model */
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

        $getTokenUrl = $this->_extension->createAbsoluteUrl('stripo/token');

        $html = (string)$model->modelMetaData->getModelMetaData()->itemAt('content_html');

        /** @var array $post */
        $post = (array)request()->getOriginalPost('', []);

        if (isset($post[$modelName]['content_html'])) {
            $html = (string)$post[$modelName]['content_html'];
        }

        // Load the default HTML template
        if (empty($html)) {
            $html = FileSystemHelper::getFileContents(dirname(__FILE__) . '/../views/' . $this->getId() . '/_default_html.php');
        }

        $css = (string)$model->modelMetaData->getModelMetaData()->itemAt('content_css');
        if (isset($post[$modelName]['content_css'])) {
            $css = (string)$post[$modelName]['content_css'];
        }

        // Load the default HTML template
        if (empty($css)) {
            $css = FileSystemHelper::getFileContents(dirname(__FILE__) . '/../views/' . $this->getId() . '/_default_css.php');
        }

        /** @var User|Customer|null $user */
        $user = null;
        if ($this->_extension->isAppName('backend')) {
            $user = user()->getModel();
        } elseif ($this->_extension->isAppName('customer')) {
            $user = customer()->getModel();
        }

        $mergeTags = [];
        if (method_exists($model, 'getAvailableTags')) {
            foreach ($model->getAvailableTags() as $tag) {
                $mergeTag    = [
                    'label' => $tag['tag'],
                    'value' => $tag['tag'],
                ];
                $mergeTags[] = $mergeTag;
            }
        }

        /** @var OptionCommon $optionCommon */
        $optionCommon = container()->get(OptionCommon::class);

        $mergeTags = [
            'category' => $optionCommon->getSiteName(),
            'entries'  => array_reverse($mergeTags),
        ];

        $socialLinksOptions = new OptionSocialLinks();
        $socialNetworks     = [];
        foreach ($socialLinksOptions->attributes as $key => $value) {
            $socialNetwork    = [
                'name' => $key,
                'href' => $value,
            ];
            $socialNetworks[] = $socialNetwork;
        }

        $supportedLocales  = ['en', 'es', 'fr', 'de', 'it', 'pt', 'sl', 'ru', 'uk', 'nl', 'cs', 'tr', 'pl', 'zh'];
        $applicationLocale = LanguageHelper::getAppLanguageCode();
        $locale            = in_array($applicationLocale, $supportedLocales) ? $applicationLocale : 'en';

        $options = [
            'settingsId'              => 'builder-' . $builderId . '-settings-container',
            'previewId'               => 'builder-' . $builderId . '-preview-container',
            'html'                    => $html,
            'css'                     => $css,
            'locale'                  => $locale,
            'apiRequestData'          => [
                'emailId' => $user ? $user->email : '',
            ],
            'userFullName'            => $user ? $user->getFullName() : '',
            'codeEditorButtonId'      => 'btn_' . $builderId . '_content',
            'mergeTags'               => [$mergeTags],
            'conditionsEnabled'       => true,
            'customConditionsEnabled' => true,
            'socialNetworks'          => $socialNetworks,
            'getAuthToken'            => new CJavaScriptExpression(
                sprintf('
                function(callback) {
                    $.ajax({
                        type: "GET",
                        url: "%s",
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        success: data => callback(data.token),
                        error: (error) => {
                            notify.addError((error.responseJSON && error.responseJSON.error) ? error.responseJSON.error : "Unknown Error").show(); 
                            callback(null);
                        }
                    });
                }', $getTokenUrl)
            ),
        ];

        app()->getController()->renderInternal(dirname(__FILE__) . '/../views/' . $this->getId() . '/after-editor.php', [
            'options'   => $options,
            'modelName' => $modelName,
            'builderId' => $builderId,
            'model'     => $model,
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

        if (isset($post[$template->getModelName()]['content_html'])) {
            $contentHtml = $post[$template->getModelName()]['content_html'];
            if (!empty($contentHtml)) {
                $template->modelMetaData->setModelMetaData('content_html', $contentHtml)->saveModelMetaData();
            }
        }
        if (isset($post[$template->getModelName()]['content_css'])) {
            $contentCss = $post[$template->getModelName()]['content_css'];
            if (!empty($contentCss)) {
                $template->modelMetaData->setModelMetaData('content_css', $contentCss)->saveModelMetaData();
            }
        }
    }
}
