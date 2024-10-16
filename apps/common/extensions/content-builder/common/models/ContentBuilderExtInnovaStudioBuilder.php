<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * ContentBuilderExtInnovaStudioBuilder
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class ContentBuilderExtInnovaStudioBuilder extends ContentBuilder
{
    /**
     * @inheritDoc
     */
    public function __construct(ExtensionInit $extension)
    {
        parent::__construct($extension);
        $this->_assetsAlias       = 'root.frontend.assets.cache.ext-content-builder-innova-studio';
        $this->_assetsRelativeUrl = '/frontend/assets/cache/ext-content-builder-innova-studio';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Innova Studio ContentBuilder.js template builder';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'innova-studio-builder';
    }

    /**
     * @return string
     */
    public function publishAssets(): string
    {
        $src = $this->getSrcAssetsPath();
        $dst = (string)Yii::getPathOfAlias($this->getAssetsAlias());

        $isDebug = MW_DEBUG;
        // @phpstan-ignore-next-line
        if (is_dir($dst) && empty($isDebug)) {
            return $this->setAssetsUrl($this->getAssetsAbsoluteUrl());
        }

        $pathsNotWritable    = [];
        $foldersToBeWritable = ['static', 'contentbuilder/assets'];

        foreach ($foldersToBeWritable as $folder) {
            $path = $this->getSrcAssetsPath($folder);
            if (!is_dir($path) || !is_writable($path)) {
                $pathsNotWritable[] = $path;
            }
        }

        if (!empty($pathsNotWritable)) {
            notify()->addError($this->_extension->t('Following folders have to be writable by the web server, that is chmod to 0777 recursive.'));
            notify()->addError($pathsNotWritable);
            return '';
        }

        $this->handleSnippetsUrls();
        $this->handleCustomModules();

        CFileHelper::copyDirectory($src, $dst, ['newDirMode' => 0777]);

        return $this->setAssetsUrl($this->getAssetsAbsoluteUrl());
    }

    /**
     * @param array $editorOptions
     * @return void
     */
    public function _createNewEditorInstance(array $editorOptions = []): void
    {
        static $_assetsRegistered = false;
        if ($_assetsRegistered) {
            return;
        }
        $_assetsRegistered = true;

        hooks()->addFilter('register_scripts', function (CList $scripts) {
            $allowedTags = (array)hooks()->applyFilters(
                'ext_content_builder_register_scripts_allowed_scripts_tags',
                ['bootstrap', 'notify', 'app', 'cookie', 'list-form-custom-asset'],
                $this
            );

            $items = [];
            foreach ($scripts->toArray() as $item) {
                if (
                    isset($item['src'], $item['tags']) &&
                    is_array($item['tags']) &&
                    count(array_intersect($item['tags'], $allowedTags))
                ) {
                    $items[] = $item;
                }
            }

            /** @var string $assetsUrl */
            $assetsUrl = $this->getAssetsUrl();

            $items = array_merge($items, [
                ['src' => $assetsUrl . '/contentbuilder/contentbuilder/lang/en.js'],
                ['src' => $assetsUrl . '/contentbuilder/contentbuilder/contentbuilder.min.js'],
                ['src' => $assetsUrl . '/static/snippet-config.js'],
            ]);

            $scripts->clear();
            $scripts->mergeWith($items);

            return $scripts;
        });

        hooks()->addFilter('register_styles', function (CList $styles) {
            $allowedTags = (array)hooks()->applyFilters(
                'ext_content_builder_register_styles_allowed_styles_tags',
                ['bootstrap', 'font-awesome', 'list-form-custom-asset'],
                $this
            );

            $items = [];
            foreach ($styles->toArray() as $item) {
                if (
                    isset($item['src'], $item['tags']) &&
                    is_array($item['tags']) &&
                    count(array_intersect($item['tags'], $allowedTags))
                ) {
                    $items[] = $item;
                }
            }

            /** @var string $assetsUrl */
            $assetsUrl = $this->getAssetsUrl();

            $items = array_merge($items, [
                ['src' => $assetsUrl . '/contentbuilder/assets/minimalist-blocks/content.css'],
                ['src' => $assetsUrl . '/contentbuilder/contentbuilder/contentbuilder.css'],
                ['src' => $assetsUrl . '/static/code-editor.css'],
                ['src' => $assetsUrl . '/static/custom-styles.css'],
            ]);

            $styles->clear();
            $styles->mergeWith($items);

            return $styles;
        });
    }

    /**
     * @param array $params
     * @return array
     */
    public function getBuilderOptions(array $params = []): array
    {
        /** @var Controller|null $controller */
        $controller = $params['controller'];
        if (empty($controller)) {
            return [];
        }

        $assetsUrl               = $this->getAssetsUrl();
        $contentBuilderUrl       = $assetsUrl . '/contentbuilder';
        $contentBuilderAssetsUrl = $contentBuilderUrl . '/assets/';
        $themesUrl               = $contentBuilderUrl . '/contentbuilder/themes/';

        $sidebarStatus = $_COOKIE['content_builder_snippets_list_status'] ?? false;
        $sidebarStatus = !empty($sidebarStatus) && $sidebarStatus === 'opened';
        $options = [
            'container'              => '.content-builder-container',
            'onChange'               => new CJavaScriptExpression('
                                            function() {
                                                let html = trimEmptyLines(this.builder.html());
                                                contentChanged = (initialContent !== html);
                                                if (contentChanged) {
                                                    $(".content-changes-notify").show();
                                                } else {
                                                    $(".content-changes-notify").hide();
                                                }
                                                $("#form-model-content-input-id").val(html);
                                            }'),
            'snippetOpen'            => $sidebarStatus,
            'clearPreferences'       =>  true,
            'row'                    => 'row',
            'cols'                   => [
                'col-md-1',
                'col-md-2',
                'col-md-3',
                'col-md-4',
                'col-md-5',
                'col-md-6',
                'col-md-7',
                'col-md-8',
                'col-md-9',
                'col-md-10',
                'col-md-11',
                'col-md-12',
            ],
            'useLightbox'            => true,
            'htmlSyntaxHighlighting' => true,
            'themes'                 => [
                ['#ffffff', '', ''],
                ['#282828', 'dark', $themesUrl . 'dark.css'],
                ['#0088dc', 'colored', $themesUrl . 'colored-blue.css'],
                ['#006add', 'colored', $themesUrl . 'colored-blue6.css'],
                ['#0a4d92', 'colored', $themesUrl . 'colored-darkblue.css'],
                ['#96af16', 'colored', $themesUrl . 'colored-green.css'],
                ['#f3522b', 'colored', $themesUrl . 'colored-orange.css'],

                ['#b92ea6', 'colored', $themesUrl . 'colored-magenta.css'],
                ['#e73171', 'colored', $themesUrl . 'colored-pink.css'],
                ['#782ec5', 'colored', $themesUrl . 'colored-purple.css'],
                ['#ed2828', 'colored', $themesUrl . 'colored-red.css'],
                ['#f9930f', 'colored', $themesUrl . 'colored-yellow.css'],
                ['#13b34b', 'colored', $themesUrl . 'colored-green4.css'],
                ['#333333', 'colored-dark', $themesUrl . 'colored-dark.css'],

                ['#dbe5f5', 'light', $themesUrl . 'light-blue.css'],
                ['#fbe6f2', 'light', $themesUrl . 'light-pink.css'],
                ['#dcdaf3', 'light', $themesUrl . 'light-purple.css'],
                ['#ffe9e0', 'light', $themesUrl . 'light-red.css'],
                ['#fffae5', 'light', $themesUrl . 'light-yellow.css'],
                ['#ddf3dc', 'light', $themesUrl . 'light-green.css'],
                ['#c7ebfd', 'light', $themesUrl . 'light-blue2.css'],

                ['#ffd5f2', 'light', $themesUrl . 'light-pink2.css'],
                ['#eadafb', 'light', $themesUrl . 'light-purple2.css'],
                ['#c5d4ff', 'light', $themesUrl . 'light-blue3.css'],
                ['#ffefb1', 'light', $themesUrl . 'light-yellow2.css'],
                ['#fefefe', 'light', $themesUrl . 'light-gray3.css'],
                ['#e5e5e5', 'light', $themesUrl . 'light-gray2.css'],
                ['#dadada', 'light', $themesUrl . 'light-gray.css'],

                ['#3f4ec9', 'colored', $themesUrl . 'colored-blue2.css'],
                ['#6779d9', 'colored', $themesUrl . 'colored-blue4.css'],
                ['#10b9d7', 'colored', $themesUrl . 'colored-blue3.css'],
                ['#006add', 'colored', $themesUrl . 'colored-blue5.css'],
                ['#e92f94', 'colored', $themesUrl . 'colored-pink3.css'],
                ['#a761d9', 'colored', $themesUrl . 'colored-purple2.css'],
                ['#f9930f', 'colored', $themesUrl . 'colored-yellow2.css'],

                ['#f3522b', 'colored', $themesUrl . 'colored-red3.css'],
                ['#36b741', 'colored', $themesUrl . 'colored-green2.css'],
                ['#00c17c', 'colored', $themesUrl . 'colored-green3.css'],
                ['#fb3279', 'colored', $themesUrl . 'colored-pink2.css'],
                ['#ff6d13', 'colored', $themesUrl . 'colored-orange2.css'],
                ['#f13535', 'colored', $themesUrl . 'colored-red2.css'],
                ['#646464', 'colored', $themesUrl . 'colored-gray.css'],

                ['#3f4ec9', 'dark', $themesUrl . 'dark-blue.css'],
                ['#0b4d92', 'dark', $themesUrl . 'dark-blue2.css'],
                ['#006add', 'dark', $themesUrl . 'dark-blue3.css'],
                ['#5f3ebf', 'dark', $themesUrl . 'dark-purple.css'],
                ['#e92f69', 'dark', $themesUrl . 'dark-pink.css'],
                ['#4c4c4c', 'dark', $themesUrl . 'dark-gray.css'],
                ['#ed2828', 'dark', $themesUrl . 'dark-red.css'],

                ['#006add', 'colored', $themesUrl . 'colored-blue8.css'],
                ['#ff7723', 'colored', $themesUrl . 'colored-orange3.css'],
                ['#ff5722', 'colored', $themesUrl . 'colored-red5.css'],
                ['#f13535', 'colored', $themesUrl . 'colored-red4.css'],
                ['#00bd79', 'colored', $themesUrl . 'colored-green5.css'],
                ['#557ae9', 'colored', $themesUrl . 'colored-blue7.css'],
                ['#fb3279', 'colored', $themesUrl . 'colored-pink4.css'],
            ],
            'imageSelect'            => $this->getAssetsPageUrl($controller->getId()),
            'fileSelect'             => $this->getAssetsPageUrl($controller->getId()),
            'videoSelect'            => $this->getAssetsPageUrl($controller->getId()),
            'audioSelect'            => $this->getAssetsPageUrl($controller->getId()),

            'snippetUrl'    => $assetsUrl . '/static/snippet-config.js',
            'snippetPath'   => $contentBuilderAssetsUrl . 'minimalist-blocks/',
            'modulePath'    => $contentBuilderAssetsUrl . 'modules/',
            'assetPath'     => $contentBuilderAssetsUrl,
            'fontAssetPath' => $contentBuilderAssetsUrl . 'fonts/',
            'pluginPath'    => $contentBuilderUrl . '/contentbuilder/',
            'plugins'       => [
                ['name' => 'preview', 'showInMainToolbar' => true, 'showInElementToolbar' => true],
                ['name' => 'wordcount', 'showInMainToolbar' => true, 'showInElementToolbar' => true],
                ['name' => 'symbols', 'showInMainToolbar' => true, 'showInElementToolbar' => true],
            ],

        ];

        if (!empty($params)) {
            $options = array_merge($params, $options);
        }

        return $options;
    }

    /**
     * @param array $allowedTags
     * @return void
     */
    public function registerFrontendStyles(array $allowedTags = []): void
    {
        hooks()->addFilter('register_styles', function (CList $styles) use ($allowedTags) {
            $allowedTags = array_filter(array_merge($allowedTags, ['bootstrap']));

            $items = [];
            foreach ($styles->toArray() as $item) {
                if (
                    isset($item['src'], $item['tags']) &&
                    is_array($item['tags']) &&
                    count(array_intersect($item['tags'], $allowedTags))
                ) {
                    $items[] = $item;
                }
            }

            /** @var string $assetsUrl */
            $assetsUrl = $this->getAssetsUrl();

            $items = array_merge($items, [
                ['src' => $assetsUrl . '/contentbuilder/assets/minimalist-blocks/content.css'],
                ['src' => $assetsUrl . '/static/custom-styles.css'],
            ]);

            $styles->clear();
            $styles->mergeWith($items);

            return $styles;
        });
    }

    /**
     * @param array $allowedTags
     * @return void
     */
    public function registerFrontendScripts(array $allowedTags = []): void
    {
        hooks()->addFilter('register_scripts', function (CList $scripts) use ($allowedTags) {
            $allowedTags = array_filter(array_merge($allowedTags, ['bootstrap']));

            $items = [];
            foreach ($scripts->toArray() as $item) {
                if (
                    isset($item['src'], $item['tags']) &&
                    is_array($item['tags']) &&
                    count(array_intersect($item['tags'], $allowedTags))
                ) {
                    $items[] = $item;
                }
            }

            $scripts->clear();
            $scripts->mergeWith($items);

            return $scripts;
        });
    }

    /**
     * @param string $source
     * @return string
     */
    public function getAssetsPageUrl(string $source = ''): string
    {
        $uploadModel         = new ContentBuilderExtUpload();
        $uploadModel->source = $source;
        $assetsUrl           = $uploadModel->getStorageUrl(false);

        static $_hasRun = false;
        if ($_hasRun) {
            return $assetsUrl . '/assets.html';
        }
        $_hasRun = true;

        $assetsPath = $uploadModel->getStoragePath();

        // Get a list of images in the customer folder
        if (!file_exists($assetsPath) || !is_dir($assetsPath) || !is_readable($assetsPath)) {
            mkdir($assetsPath, 0777, true);
        }

        /** @var SplFileInfo[] $files */
        $files = (new Symfony\Component\Finder\Finder())
            ->files()
            ->name(['*.png', '*.jpg', '*.jpeg', '*.gif'])
            ->in($assetsPath);

        $html = [];
        foreach ($files as $file) {
            $html[] = sprintf('<button><img src="%s/%s" /></button>', $assetsUrl, $file->getFilename());
        }
        $html = implode(PHP_EOL, $html);

        // Get assets.html content into a string
        $assetsPageContent = FileSystemHelper::getFileContents($this->getSrcAssetsPath('static') . '/assets.html');

        // Replace the [CUSTOMER_ASSETS] tag with buttons having inside customer images
        $assetsPageContent = str_replace('[CUSTOMER_ASSETS]', $html, $assetsPageContent);

        // Write the obtained content into the customer assets folder
        file_put_contents($assetsPath . '/assets.html', $assetsPageContent);

        return $assetsUrl . '/assets.html';
    }

    /**
     * @return void
     */
    public function handleSnippetsUrls(): void
    {
        // Replacing the snippets images/videos src with the correct relative url
        $assetsUrl = $this->getAssetsRelativeUrl();

        $snippetConfigTemplateSrc = FileSystemHelper::getFileContents($this->getSrcAssetsPath('contentbuilder/assets/minimalist-blocks') . '/content.js');

        $content = preg_replace_callback("/<img[^>]*?src *= *[\"']?([^\"']*)/i", function ($matches) use ($assetsUrl) {
            if (isset($matches[1])) {
                return '<img src="' . $assetsUrl . '/contentbuilder/' . $matches[1];
            }
        }, $snippetConfigTemplateSrc);

        $content = preg_replace_callback(
            "/<source[^>]*?src *= *[\"']?([^\"']*)/i",
            function ($matches) use ($assetsUrl) {
                if (isset($matches[1])) {
                    return '<source src="' . $assetsUrl . '/contentbuilder/' . $matches[1];
                }
            },
            (string)$content
        );

        // We should copy the thumbnails to the content builder assets/minimalist-blocks/preview folder, since the builder will look ONLY there
        $contentBuilderCustomBlocksThumbnailsPath = $this->getSrcAssetsPath('contentbuilder/assets/minimalist-blocks/preview');
        $sourceCustomBlocksThumbnailsFolderPath = $this->getSrcAssetsPath('static/custom-blocks/preview');
        FileSystemHelper::copyDirectoryContents($sourceCustomBlocksThumbnailsFolderPath, $contentBuilderCustomBlocksThumbnailsPath);

        // We do not parse the src for the images and thumbnails relative to the content builder path. We expect to give the full url for the custom blocks
        $customBlocksConfigTemplateSrc = FileSystemHelper::getFileContents($this->getSrcAssetsPath('static/custom-blocks') . '/custom-snippets.js');

        // Parse the custom blocks tags
        $tagsMapping = $this->getCustomBlocksTags();
        $customContent = str_replace(array_keys($tagsMapping), array_values($tagsMapping), $customBlocksConfigTemplateSrc);

        file_put_contents($this->getSrcAssetsPath('static') . '/snippet-config.js', $content . $customContent);
    }

    /**
     * @return array
     */
    public function getCustomBlocksTags(): array
    {
        $listSubscribeFormBlockInnerDiv = CHtml::tag('div', [
            'data-noedit'       => true,
            'encode'            => false,
            'class'             => 'column full',
            'data-module'       => 'mw-lists-subscribe-form-module',
            'data-dialog-width' => '500px',
            'data-module-desc'  => $this->_extension->t('Insert email list subscribe form'),
            'data-html'         => sprintf(
                '${encodeURIComponent(`<h2>%s</h2>`)}',
                $this->_extension->t('Insert subscribe form from the block settings')
            ),
            'data-settings'     => '${encodeURIComponent(`{list_uid: "", list_subscribe_form_html: ""}`)}',
        ], true);
        $listSubscribeFormBlockHtml = CHtml::tag('div', ['class' => 'row'], $listSubscribeFormBlockInnerDiv);

        return [
            '[LIST_SUBSCRIBE_FORM_BLOCK_HTML]' => $listSubscribeFormBlockHtml,
        ];
    }

    /**
     * @return void
     */
    public function handleCustomModules(): void
    {
        $contentBuilderModulesPath = $this->getSrcAssetsPath('contentbuilder/assets/modules');

        $sourceCustomModulesFolderPath = $this->getSrcAssetsPath('static/custom-modules');
        /** @var SplFileInfo[] $files */
        $files = (new Symfony\Component\Finder\Finder())
            ->files()
            ->name(['*.html'])
            ->in($sourceCustomModulesFolderPath);

        foreach ($files as $file) {
            if (!is_dir($contentBuilderModulesPath)) {
                mkdir($contentBuilderModulesPath, 0777, true);
            }

            $sourceFilePath      = $sourceCustomModulesFolderPath . '/' . $file->getFilename();
            $destinationFilePath = $contentBuilderModulesPath . '/' . $file->getFilename();

            $customModuleFileContent = FileSystemHelper::getFileContents($sourceFilePath);
            // Parse the custom modules tags
            $tagsMapping             = $this->getCustomModulesTags();
            $customModuleFileContent = str_replace(
                array_keys($tagsMapping),
                array_values($tagsMapping),
                $customModuleFileContent
            );

            file_put_contents($destinationFilePath, $customModuleFileContent);
        }
    }

    /**
     * @return array
     */
    public function getCustomModulesTags(): array
    {
        return [
            '[CUSTOMER_LISTS_SUBSCRIBE_FORMS_SETUP_ELEMENT]' => CHtml::tag('span', [
                'id'            => 'mw-customer-subscribe-lists-forms-setup{id}',
                'data-url'      => apps()->getAppUrl('customer', 'content-builder/customer-lists-forms'),
                'data-form-url' => apps()->getAppUrl('customer', 'content-builder/get-list-subscribe-form'),
                'style'         => 'display:none',
            ]),
            '[MODULE_BASE_HREF]' => apps()->getAppUrl(apps()->getCurrentAppName(), '', true, true),
        ];
    }
}
