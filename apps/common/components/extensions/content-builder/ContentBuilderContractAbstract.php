<?php declare(strict_types=1);

/**
 * This class can be extended so one can make use of the content builder extension in
 * different application areas
 */
abstract class ContentBuilderContractAbstract extends ExtensionInit
{
    /**
     * @var ContentBuilder
     */
    protected $_builder;

    /**
     * @return string[]
     */
    abstract public function getBuilderRoutes(): array;

    /**
     * @return string[]
     */
    abstract public function getFrontendControllersIds(): array;

    /**
     * @return string[]
     */
    abstract public function getFrontendControllersActions(): array;

    /**
     * @return string[]
     */
    abstract public function getFrontendRoutes(): array;

    /**
     * @return string[]
     */
    abstract public function getFrontendAllowedStyleTags(): array;

    /**
     * @return string[]
     */
    abstract public function getFrontendAllowedScriptTags(): array;

    /**
     * @return string
     */
    abstract public function getFrontendControllerModelVariableName(): string;

    /**
     * @param Article|Page|LandingPageRevisionVariant $model
     * @param Controller $controller
     * @return string
     */
    abstract public function getModelContentForFrontendControllerView(
        ActiveRecord $model,
        Controller $controller
    ): string;

    /**
     * @return string
     */
    abstract public function getBuilderControllerModelVariableName(): string;

    /**
     * @return string[]
     */
    abstract public function getBuilderControllerAdditionalModelsVariablesNames(): array;

    /**
     * @param ActiveRecord $model
     * @return string
     */
    abstract public function getModelUniqueId(ActiveRecord $model): string;

    /**
     * @param string $filePath
     * @param string $source
     * @return string
     */
    abstract public function getBuilderStoragePath(string $filePath, string $source): string;

    /**
     * @param string $fileUrl
     * @param string $source
     * @return string
     */
    abstract public function getBuilderStorageUrl(string $fileUrl, string $source): string;

    /**
     * @inheritDoc
     */
    public function run()
    {
        hooks()->addAction('controller_action_can_render', [$this, '_showNotification']);
    }

    /**
     * @param CAttributeCollection $collection
     * @return void
     */
    public function _showNotification(CAttributeCollection $collection): void
    {
        /** @var Controller $controller */
        $controller = $collection->itemAt('controller');

        if (!in_array($controller->getRoute(), $this->getBuilderRoutes())) {
            return;
        }

        if (!$this->isAppName('backend')) {
            return;
        }

        /** @var ContentBuilderExt|null $contentBuilderExtension */
        $contentBuilderExtension = extensionsManager()->getExtensionInstance('content-builder');
        if ($contentBuilderExtension && !$contentBuilderExtension->getIsEnabled()) {
            $text = $this->t('Please be aware that you can use the Content Builder extension to create content, by enabling it from {here}.', [
                '{here}' => CHtml::link(t('app', 'here'), createUrl('extensions/enable', ['id' => 'content-builder']), ['target' => '_blank']),
            ]);
            notify()->addWarning($text);
        }
    }

    /**
     * @return void
     */
    public function afterEnable()
    {
        /**
         * Add action to remove the landing page content builder templates. The action is executed when in ContentBuilder
         * is called the removeTemplates() method that is calling this action
         */
        hooks()->addAction('ext_content_builder_remove_templates', [$this, '_removeTemplates']);

        /**
         * Add action to handle landing page content builder templates. This action is executed after the ContentBuilder
         * extension is enabled
         */
        hooks()->addAction('ext_content_builder_insert_templates', [$this, '_insertTemplates']);


        // Make sure the Content Builder extension is enabled
        /** @var ContentBuilderExt|null $extension */
        $extension = extensionsManager()->getExtensionInstance('content-builder');
        if (empty($extension)) {
            notify()->addError(t('extensions', 'The Content Builder extension not found'));
            return;
        }

        if (!$extension->getIsEnabled() && !extensionsManager()->enableExtension('content-builder')) {
            notify()->addError(extensionsManager()->getErrors());
            return;
        }
        $message = t('extensions', 'The extension "{name}" has been successfully enabled!', [
            '{name}' => html_encode((string)$extension->name),
        ]);
        notify()->addSuccess($message);
    }

    /**
     * @param ContentBuilder $builder
     * @return void
     */
    public function _handleBuilderArea(ContentBuilder $builder): void
    {
        $this->setBuilder($builder);

        /**
         * This hook stops the controller actions like::actionUpdate() and actionCreate() and renders the builder specific view and layout
         */
        hooks()->addAction('controller_action_can_render', [$this, '_renderBuilder']);

        hooks()->addFilter('ext_content_builder_upload_storage_url', [$this, 'getBuilderStorageUrl']);
        hooks()->addFilter('ext_content_builder_upload_storage_path', [$this, 'getBuilderStoragePath']);
    }

    /**
     * @param CAttributeCollection $collection
     * @return void
     */
    final public function _renderBuilder(CAttributeCollection $collection): void
    {
        /** @var Controller $controller */
        $controller = $collection->itemAt('controller');

        if (!in_array($controller->getRoute(), $this->getBuilderRoutes())) {
            return;
        }

        if (!$collection->itemAt('canRender')) {
            return;
        }

        if (!$this->prepareBuilder($controller)) {
            return;
        }

        $this->getBuilder()->renderContentBuilder($controller);

        $collection->add('canRender', false);
    }

    /**
     * @param ContentBuilder $builder
     * @return void
     */
    public function _handleFrontendArea(ContentBuilder $builder): void
    {
        $this->setBuilder($builder);

        /**
         * Handle the builder for frontend area, in the landing page controller
         * It targets the view action, and it is setting the view layout to the builder specific one and also loads
         * the builder specific assets
         */
        foreach ($this->getFrontendControllersIds() as $controllersId) {
            hooks()->addAction(
                sprintf('frontend_controller_%s_before_action', $controllersId),
                [$this, '_frontendControllerBeforeAction']
            );
        }

        /**
         * Handle the builder for frontend area, in the landing_pages and landing_page_variants controllers.
         * For the view route it will replace the default view content with the view content specific for each builder
         */

        hooks()->addAction(
            'before_view_file_content',
            [$this, '_frontendControllerBeforeViewFileContent']
        );
    }

    /**
     * @param CAction $action
     * @return void
     */
    final public function _frontendControllerBeforeAction(CAction $action): void
    {
        if (!in_array($action->getId(), $this->getFrontendControllersActions())) {
            return;
        }

        $this->getBuilder()->registerFrontendStyles($this->getFrontendAllowedStyleTags());
        $this->getBuilder()->registerFrontendScripts($this->getFrontendAllowedScriptTags());

        $action->getController()->layout = $this->getBuilder()->getFrontendLayoutAlias();
    }

    /**
     * @param CAttributeCollection $collection
     * @return void
     * @throws CException
     */
    final public function _frontendControllerBeforeViewFileContent(CAttributeCollection $collection)
    {
        /** @var Controller $controller */
        $controller = $collection->itemAt('controller');

        if (!in_array($controller->getRoute(), $this->getFrontendRoutes())) {
            return;
        }

        $collection->add('renderContent', false);

        /** @var Article|Page|LandingPageRevisionVariant $model */
        $model   = $controller->getData($this->getFrontendControllerModelVariableName());
        $content = $this->getModelContentForFrontendControllerView($model, $controller);

        hooks()->addAction(
            'ext_content_builder_frontend_view_content',
            function (CAttributeCollection $collection) use ($content) {
                echo $content;
            }
        );

        $this->addFrontendContentAfterViewContent($collection, $model);

        $this->getBuilder()->renderFrontendView($controller);
    }

    /**
     * @return ContentBuilder
     */
    public function getBuilder(): ContentBuilder
    {
        return $this->_builder;
    }

    /**
     * @param ContentBuilder $builder
     */
    public function setBuilder(ContentBuilder $builder): void
    {
        $this->_builder = $builder;
    }

    /**
     * @param CAttributeCollection $collection
     * @return void
     */
    public function _removeTemplates(CAttributeCollection $collection): void
    {
    }

    /**
     * We compare the files signature to see if any template has changed
     *
     * @param CAttributeCollection $collection
     * @return void
     */
    public function _insertTemplates(CAttributeCollection $collection): void
    {
    }

    /**
     * @param Controller $controller
     * @return bool
     */
    final protected function prepareBuilder(Controller $controller): bool
    {
        /** @var Article|Page|LandingPageRevisionVariant|null $model */
        $model = $controller->getData($this->getBuilderControllerModelVariableName());

        $additionalModels              = [];
        $additionalModelsVariableNames = $this->getBuilderControllerAdditionalModelsVariablesNames();
        foreach ($additionalModelsVariableNames as $modelName) {
            $additionalModels[$modelName] = $controller->getData($modelName);
        }

        if (empty($model) || !is_object($model) || !($model instanceof ActiveRecord)) {
            return false;
        }

        foreach ($additionalModels as $additionalModel) {
            if (empty($additionalModel) || !is_object($additionalModel) || !($additionalModel instanceof ActiveRecord)) {
                return false;
            }
        }

        // Add actions that will hook into the areas of the builder. Each should do a render internal of its part like below
        hooks()->addAction(
            'ext_content_builder_before_content_builder_form',
            function (CAttributeCollection $collection) use ($controller, $model) {
                $formRoute     = [$controller->getRoute()];
                $modelUniqueId = $this->getModelUniqueId($model);
                if (!empty($modelUniqueId)) {
                    $formRoute = array_merge($formRoute, ['id' => $modelUniqueId]);
                }
                $collection->add('formAction', $formRoute);
                $collection->add('formModelContentName', $model->getModelName() . '[content]');
                $collection->add('formModelContent', $model->content);
            }
        );

        hooks()->addAction(
            'ext_content_builder_top_bar_left_items',
            function (CAttributeCollection $collection) use ($model, $additionalModels) {
                /** @var Controller $controller */
                $controller = $collection->itemAt('controller');

                /** @var CActiveForm $form */
                $form = $collection->itemAt('form');

                $controller->renderInternal($this->getPathOfAlias(sprintf(
                    '%s.views._top-bar-left-items',
                    apps()->getCurrentAppName()
                )) . '.php', array_merge([
                    'article' => $model,
                    'form'    => $form,
                ], $additionalModels));
            }
        );

        hooks()->addAction(
            'ext_content_builder_before_form_end',
            function (CAttributeCollection $collection) use ($model, $additionalModels) {
                /** @var Controller $controller */
                $controller = $collection->itemAt('controller');

                /** @var CActiveForm $form */
                $form = $collection->itemAt('form');

                $controller->renderInternal($this->getPathOfAlias(sprintf(
                    '%s.views._form-controls',
                    apps()->getCurrentAppName()
                )) . '.php', array_merge([
                    'article' => $model,
                    'form'    => $form,
                ], $additionalModels));
            }
        );

        hooks()->addAction(
            'ext_content_builder_top_bar_right_items',
            function (CAttributeCollection $collection) use ($model, $additionalModels) {
                /** @var Controller $controller */
                $controller = $collection->itemAt('controller');

                $controller->renderInternal($this->getPathOfAlias(sprintf(
                    '%s.views._top-bar-right-items',
                    apps()->getCurrentAppName()
                )) . '.php', array_merge([
                    'article' => $model,
                ], $additionalModels));
            }
        );

        hooks()->addAction(
            'ext_content_builder_add_container_content',
            function (CAttributeCollection $collection) use ($model) {
                // We don't add the wrapper for empty content, so the builder can add its own placeholder
                echo (string)$model->content;
            }
        );

        hooks()->addAction(
            'ext_content_builder_add_panel_items',
            function (CAttributeCollection $collection) use ($model, $additionalModels) {
                /** @var Controller $controller */
                $controller = $collection->itemAt('controller');
                $controller->renderInternal($this->getPathOfAlias(sprintf(
                    '%s.views._panel-items',
                    apps()->getCurrentAppName()
                )) . '.php', array_merge([
                    'article' => $model,
                ], $additionalModels));
            }
        );

        hooks()->addAction('ext_content_builder_after_view_file_content', function (CAttributeCollection $collection) use ($model) {
            /** @var Controller $controller */
            $controller = $collection->itemAt('controller');
            $controller->renderInternal($this->getPathOfAlias(sprintf(
                '%s.views._after-content-builder',
                apps()->getCurrentAppName()
            )) . '.php', [
                'saveModelAttributesUrl' => createUrl($controller->getId() . '/save_attributes', ['id' => $this->getModelUniqueId($model)]),
            ]);
        });

        return true;
    }

    /**
     * @param CAttributeCollection $collection
     * @param ActiveRecord $model
     * @return void
     */
    protected function addFrontendContentAfterViewContent(CAttributeCollection $collection, ActiveRecord $model): void
    {
    }
}
