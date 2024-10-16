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
class LandingPagesContentBuilderExt extends ContentBuilderContractAbstract
{
    /**
     * @var string
     */
    public $name = 'Content Builder for Landing Pages';

    /**
     * @var string
     */
    public $description = 'Drag and Drop Content Builder For Landing Pages';

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

        if ($this->isAppName('customer')) {
            hooks()->addAction('ext_content_builder_customer_area_handler', [$this, '_handleBuilderArea']);
        }

        if ($this->isAppName('frontend')) {
            hooks()->addAction('ext_content_builder_frontend_area_handler', [$this, '_handleFrontendArea']);
        }

        // If this needs to be executed for all content builder extensions,
        // we need to move it in the parent "run" method.
        if ($this->isAppName('backend')) {
            hooks()->addFilter('extensions_manager_can_disable_extension', function (bool $canDisable, ExtensionInit $extension) {
                if ($extension->getDirName() !== 'content-builder') {
                    return $canDisable;
                }

                notify()->addError($this->t('This extension {name} cannot be disabled because other enabled extensions depend on it!', [
                    '{name}' =>  html_encode((string)$extension->name),
                ]));
                return false;
            });
        }
    }

    /**
     * @param ContentBuilder $builder
     * @return void
     */
    public function _handleBuilderArea(ContentBuilder $builder): void
    {
        parent::_handleBuilderArea($builder);

        /**
         * This hook add the template items specific for each builder and extension
         */
        hooks()->addFilter('landing_page_templates_grid_items', [$this, '_addLandingPageTemplatesItems']);
    }

    /**
     * @param array $templates
     *
     * @return array
     */
    public function _addLandingPageTemplatesItems(array $templates): array
    {
        return array_merge(
            $templates,
            LandingPageTemplate::model()->findAllByAttributes(['builder_id' => $this->getBuilder()->getId()])
        );
    }

    /**
     * @param CAttributeCollection $collection
     * @return void
     */
    public function _removeTemplates(CAttributeCollection $collection): void
    {
        /** @var ContentBuilder $builder */
        $builder = $collection->itemAt('builder');
        LandingPageTemplate::model()->deleteAllByAttributes(['builder_id' => $builder->getId()]);
    }

    /**
     * We compare the files signature to see if any template has changed
     *
     * @param CAttributeCollection $collection
     * @return void
     * @throws CException
     */
    public function _insertTemplates(CAttributeCollection $collection): void
    {
        /** @var ContentBuilder $builder */
        $builder       = $collection->itemAt('builder');
        $templatesPath = (string)$collection->itemAt('templatesPath');

        $criteria = new CDbCriteria();
        $criteria->compare('builder_id', $builder->getId());
        $criteria->order = 'title ASC';
        $templates       = LandingPageTemplate::model()->findAll($criteria);

        $dbSignatures = [];

        /** @var LandingPageTemplate $template */
        foreach ($templates as $template) {
            $dbSignatures[] = $template->getSignature();
        }
        $dbTemplatesSignature = sha1(implode('', $dbSignatures));

        /** @var SplFileInfo[] $files */
        $files = (new Symfony\Component\Finder\Finder())
            ->files()
            ->name(['*.html'])
            ->sortByName()
            ->in($templatesPath);

        $diskFilesSignatures = [];
        $newTemplates        = [];
        foreach ($files as $file) {
            $slug                 = $file->getBasename('.html');
            $template             = new LandingPageTemplate();
            $template->builder_id = $builder->getId();
            $template->title      = ucwords((string)str_replace(['_', '-'], ' ', $slug));
            $template->content    = FileSystemHelper::getFileContents($file->getRealPath());
            $template->content    = str_replace('[BUILDER_ASSETS_RELATIVE_URL]', $builder->getAssetsRelativeUrl(), $template->content);
            $template->screenshot = $builder->getAssetsRelativeUrl() . '/templates/' . $slug . '.png';

            $newTemplates[]        = $template;
            $diskFilesSignatures[] = $template->getSignature();
        }
        $diskFilesSignature = sha1(implode('', $diskFilesSignatures));

        if ($dbTemplatesSignature === $diskFilesSignature) {
            return;
        }

        $builder->removeTemplates();

        foreach ($newTemplates as $newTemplate) {
            $newTemplate->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function getBuilderRoutes(): array
    {
        return ['landing_page_variants/update'];
    }

    /**
     * @inheritDoc
     */
    public function getFrontendControllersIds(): array
    {
        return ['landing_pages', 'landing_page_variants'];
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
        return ['landing_pages/view', 'landing_page_variants/view'];
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
        return 'variant';
    }

    /**
     * @param LandingPageRevisionVariant $model
     * @param Controller $controller
     * @return string
     */
    public function getModelContentForFrontendControllerView(ActiveRecord $model, Controller $controller): string
    {
        $content = $model->content;
        if ($controller->getId() === 'landing_pages') {
            $content = $model->getParsedContent();
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function getBuilderControllerModelVariableName(): string
    {
        return 'variant';
    }

    /**
     * @inheritDoc
     */
    public function getBuilderControllerAdditionalModelsVariablesNames(): array
    {
        return [];
    }

    /**
     * @param LandingPageRevisionVariant $model
     * @return string
     */
    public function getModelUniqueId(ActiveRecord $model): string
    {
        return (string)$model->getHashId();
    }

    public function getBuilderStoragePath(string $filePath, string $source): string
    {
        return $filePath;
    }

    public function getBuilderStorageUrl(string $fileUrl, string $source): string
    {
        return $fileUrl;
    }
}
