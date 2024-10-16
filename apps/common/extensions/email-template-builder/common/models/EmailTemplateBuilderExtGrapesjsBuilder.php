<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExtGrapesjsBuilder
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class EmailTemplateBuilderExtGrapesjsBuilder extends EmailTemplateBuilder
{
    const GRAPESJS_BUILDER_ID = 'grapesjs';

    /**
     * @inheritDoc
     */
    public function __construct(ExtensionInit $extension)
    {
        parent::__construct($extension);
        $this->_assetsAlias       = 'root.frontend.assets.cache.ext-email-template-builder-grapesjs';
        $this->_assetsRelativeUrl = '/frontend/assets/cache/ext-email-template-builder-grapesjs';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'GrapesJS';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::GRAPESJS_BUILDER_ID;
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
        clientScript()->registerCssFile($assetsUrl . '/dist/grapesjs/css/grapes.min.css');
        clientScript()->registerCssFile($assetsUrl . '/style/material.css');
        clientScript()->registerCssFile($assetsUrl . '/style/toastr.min.css');
        clientScript()->registerCssFile($assetsUrl . '/style/tooltip.css');
        clientScript()->registerCssFile($assetsUrl . '/dist/grapesjs-preset-newsletter/index.css');
        clientScript()->registerCssFile($assetsUrl . '/static/code-editor.css');

        clientScript()->registerScriptFile($assetsUrl . '/dist/grapesjs/grapes.min.js');
        clientScript()->registerScriptFile($assetsUrl . '/dist/grapesjs-plugin-ckeditor/index.js');
        clientScript()->registerScriptFile($assetsUrl . '/dist/grapesjs-preset-newsletter/index.js');
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
        /** @var CustomerEmailTemplate|null $model */
        $model = null;

        if (!empty($params['template'])) {
            $model = $params['template'];
        }

        if (empty($model) || !is_object($model) || !($model instanceof ActiveRecord)) {
            return;
        }

        /** @var string $modelName */
        $modelName = $model->getModelName();
        $builderId = $modelName . '_content';

        $uploadModel = new EmailTemplateBuilderExtGrapesjsBuilderUpload();

        $filesPath = $uploadModel->getStoragePath();

        $assets = [];
        if (file_exists($filesPath) && is_dir($filesPath)) {
            $assets = array_map(function (string $item) use ($uploadModel): string {
                return $uploadModel->getStorageUrl() . '/' . $item;
            }, FileSystemHelper::readDirectoryContents($filesPath));
        }

        $defaultTemplate = FileSystemHelper::getFileContents(dirname(__FILE__) . '/../views/' . $this->getId() . '/_default_template.php');

        $options = [
            'clearOnRender'  =>  true,
            'height'         => '100%',
            'storageManager' => [
                'autoload' => 1,
            ],
            'container'      => '#builder_' . $builderId,
            'assetManager'   => [
                'assets'     => $assets,
                'uploadName' => $uploadModel->getModelName() . '[files]',
                'upload'     => $this->_extension->createUrl('grapesjs_builder/upload'),
                'params'     => [
                    request()->csrfTokenName => request()->getCsrfToken(),
                ],
            ],
            'fromElement' => true,
            'components'  => null,
            'plugins'     => ['grapesjs-preset-newsletter', 'grapesjs-plugin-ckeditor'],
            'panels'      => [],
            'pluginsOpts' => [
                'grapesjs-preset-newsletter' =>  [
                    'modalLabelImport'      =>  'Paste all your code here below and click import',
                        'modalLabelExport'  => 'Copy the code and use it wherever you want',
                        'codeViewerTheme'   => 'material',
                        //defaultTemplate   => $defaultTemplate,
                        'importPlaceholder' =>  '<table class="table"><tr><td class="cell">Hello world!</td></tr></table>',
                        'cellStyle' =>  [
                            'font-size'      => '12px',
                            'font-weight'    =>  300,
                            'vertical-align' =>  'top',
                            'color'          =>  'rgb(111, 119, 125)',
                            'margin'         => 0,
                            'padding'        => 0,
                        ],
                ],
                'grapesjs-plugin-ckeditor' => [
                    'position' =>  'center',
                    'options'  => [
                        'startupFocus'        => true,
                        'extraAllowedContent' => '*(*);*{*}', // Allows any class and any inline style
                        'allowedContent'      =>  true, // Disable auto-formatting, class removing, etc.
                        'enterMode'           => 'js:CKEDITOR.ENTER_BR',
                        'extraPlugins'        => 'sharedspace,justify,colorbutton,panelbutton,font',
                        'toolbar'             =>  [
                            [
                                'name'  => 'styles',
                                'items' => ['Font', 'FontSize'],
                            ],
                            ['Bold', 'Italic', 'Underline', 'Strike'],
                            ['name' => 'paragraph', 'items' => ['NumberedList', 'BulletedList']],
                            ['name' => 'links', 'items' => ['Link', 'Unlink']],
                            ['name' => 'colors', 'items' =>  ['TextColor', 'BGColor']],
                        ],
                    ],
                ],
            ],
            'defaultTemplate'                => $defaultTemplate,
            'ckeditorDefaultTemplate'        => app_param('email.templates.stub', ''),
            'loadDefaultTemplateButton'      => [
                'confirmText' => $this->_extension->t('Are you sure you want to load the default template? All your previous work will be lost.'),
                'title'       => $this->_extension->t('Load default template'),
            ],
            'colorPicker' =>  [
                'appendTo' => 'parent',
                'offset'   =>  [
                    'top'  => 26,
                    'left' => -166,
                ],
            ],
        ];

        app()->getController()->renderInternal(dirname(__FILE__) . '/../views/' . $this->getId() . '/after-editor.php', [
            'options'             => $options,
            'modelName'           => $modelName,
            'builderId'           => $builderId,
            'model'               => $model,
        ]);
    }
}
