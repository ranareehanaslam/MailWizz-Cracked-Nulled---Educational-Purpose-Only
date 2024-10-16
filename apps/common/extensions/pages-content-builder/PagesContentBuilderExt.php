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
class PagesContentBuilderExt extends ContentBuilderContractAbstract
{
    /**
     * @var string
     */
    public $name = 'Content Builder for Pages';

    /**
     * @var string
     */
    public $description = 'Drag and Drop Content Builder For Pages';

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
        return ['pages/create', 'pages/update'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendControllersIds(): array
    {
        return ['pages'];
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
        return ['pages/view'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendAllowedStyleTags(): array
    {
        return [];
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
        return 'page';
    }

    /**
     * @param Page $model
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
        return 'page';
    }

    /**
     * @inheritDoc
     */
    public function getBuilderControllerAdditionalModelsVariablesNames(): array
    {
        return [];
    }

    /**
     * @param Page $model
     * @return string
     */
    public function getModelUniqueId(ActiveRecord $model): string
    {
        return (string)$model->page_id;
    }

    /**
     * @inheritDoc
     */
    public function getBuilderStoragePath(string $filePath, string $source): string
    {
        if ($source === 'pages') {
            $filePath .= '/' . $source;
        }

        return $filePath;
    }

    /**
     * @inheritDoc
     */
    public function getBuilderStorageUrl(string $fileUrl, string $source): string
    {
        if ($source === 'pages') {
            $fileUrl .= '/' . $source;
        }

        return $fileUrl;
    }
}
