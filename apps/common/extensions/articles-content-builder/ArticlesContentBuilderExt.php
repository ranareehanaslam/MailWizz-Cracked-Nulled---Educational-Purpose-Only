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
class ArticlesContentBuilderExt extends ContentBuilderContractAbstract
{
    /**
     * @var string
     */
    public $name = 'Content Builder for Articles';

    /**
     * @var string
     */
    public $description = 'Drag and Drop Content Builder For Articles';

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
    public $allowedApps = ['backend', 'frontend'];

    /**
     * @var int
     */
    public $priority = 1000;

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
        parent::run();

        if ($this->isAppName('backend')) {
            hooks()->addAction('ext_content_builder_backend_area_handler', [$this, '_handleBuilderArea']);
        }

        if ($this->isAppName('frontend')) {
            hooks()->addAction('ext_content_builder_frontend_area_handler', [$this, '_handleFrontendArea']);
        }
    }

    /**
     * @inheritDoc
     */
    public function getBuilderRoutes(): array
    {
        return ['articles/create', 'articles/update'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendControllersIds(): array
    {
        return ['articles'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendControllersActions(): array
    {
        return ['view'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendRoutes(): array
    {
        return ['articles/view'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendAllowedStyleTags(): array
    {
        return ['style'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendAllowedScriptTags(): array
    {
        return [];
    }

    public function getFrontendControllerModelVariableName(): string
    {
        return 'article';
    }

    /**
     * @param Article $model
     * @param Controller $controller
     * @return string
     */
    public function getModelContentForFrontendControllerView(ActiveRecord $model, Controller $controller): string
    {
        return $model->content;
    }

    /**
     * @inheritDoc
     */
    public function getBuilderControllerModelVariableName(): string
    {
        return 'article';
    }

    /**
     * @inheritDoc
     */
    public function getBuilderControllerAdditionalModelsVariablesNames(): array
    {
        return ['articleToCategory'];
    }

    /**
     * @param Article $model
     * @return string
     */
    public function getModelUniqueId(ActiveRecord $model): string
    {
        return (string)$model->article_id;
    }

    /**
     * @inheritDoc
     */
    public function getBuilderStoragePath(string $filePath, string $source): string
    {
        if ($source === 'articles') {
            $filePath .= '/' . $source;
        }

        return $filePath;
    }

    /**
     * @inheritDoc
     */
    public function getBuilderStorageUrl(string $fileUrl, string $source): string
    {
        if ($source === 'articles') {
            $fileUrl .= '/' . $source;
        }

        return $fileUrl;
    }

    /**
     * @param CAttributeCollection $collection
     * @param Article $model
     * @return void
     */
    protected function addFrontendContentAfterViewContent(CAttributeCollection $collection, ActiveRecord $model): void
    {
        hooks()->addAction(
            'ext_content_builder_frontend_after_view_content',
            function (CAttributeCollection $collection) use ($model) {
                /** @var Controller $controller */
                $controller = $collection->itemAt('controller');
                $controller->renderFile($this->getPathOfAlias('frontend.views.view') . '.php', [
                    $this->getFrontendControllerModelVariableName() => $model,
                ]);
            }
        );
    }
}
