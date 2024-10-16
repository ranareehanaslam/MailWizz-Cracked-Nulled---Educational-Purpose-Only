<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Landing_page_variantsController
 *
 * Handles the actions for customer landing pages variants related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class Landing_page_variantsController extends Controller
{
    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        /** @var Customer $customer */
        $customer = customer()->getModel();
        if ((int)$customer->getGroupOption('landing_pages.max_landing_pages', -1) === 0) {
            $this->redirect(['dashboard/index']);
        }

        // make sure the parent account has allowed access for this subaccount
        if (is_subaccount() && !subaccount()->canManageLandingPages()) {
            $this->redirect(['dashboard/index']);
        }
    }

    /**
     * @return array
     * @throws CException
     */
    public function filters()
    {
        $filters = [
            'postOnly + create, delete, copy, toggle_active, save_attributes',
        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all landing page variants
     *
     * @param string $page_id
     * @return void
     * @throws CException
     */
    public function actionIndex($page_id)
    {
        $page         = $this->loadLandingPageModel($page_id);
        $lastRevision = $page->lastRevision;

        if (empty($lastRevision)) {
            $this->renderJson([
                'html' => '',
            ]);
            return;
        }

        $activeVariants   = $lastRevision->activeVariants;
        $inactiveVariants = $lastRevision->inactiveVariants;

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_pages', 'Landing page variants'),
            'pageHeading'     => t('landing_pages', 'Landing page variants'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                $page->getTitle() . ' '             => createUrl(
                    'landing_pages/overview',
                    ['id' => $page->getHashId()]
                ),
                t('landing_pages', 'Variants'),
            ],
        ]);

        $this->renderJson([
            'html' => $this->renderPartial('list', compact('page', 'activeVariants', 'inactiveVariants'), true, true),
        ]);
    }

    /**
     * Add a new Landing page variant
     * Works only with POST
     *
     * @param string $page_id
     * @return void
     * @throws CException
     */
    public function actionCreate($page_id)
    {
        $page = $this->loadLandingPageModel($page_id);

        $variant = new LandingPageRevisionVariant();
        $variant->loadDefaults();

        $form = new LandingPageAddVariantForm();

        $attributes = (array)request()->getPost($form->getModelName(), []);

        $form->attributes = $attributes;

        if (!$form->validate()) {
            notify()->addError(t('app', CHtml::errorSummary($form)));
            $this->redirect(['landing_pages/overview', 'id' => $page->getHashId()]);
        }

        if ($formVariant = $form->getVariant()) {
            $variant = $formVariant->copy(LandingPageRevisionVariant::TEXT_NO, false, true, true, false);
        } elseif ($formTemplate = $form->getTemplate()) {
            $variant->title   = $formTemplate->title;
            $variant->content = $formTemplate->content;
        }

        $transaction = db()->beginTransaction();
        $success     = false;

        try {
            $revision = $page->getRevisionFromLastRevision();

            if (empty($revision)) {
                throw new Exception(t('landing_pages', 'Cannot create the revision'));
            }

            $variant->revision_id = $revision->revision_id;

            if (!$variant->save()) {
                throw new Exception(t('landing_pages', 'Cannot save the revision variant'));
            }

            // Copy the urls if we duplicate an existing variant
            if ($formVariant && !$variant->copyTrackUrlsFromVariant($formVariant)) {
                throw new Exception(t('landing_pages', 'Cannot save the variant urls'));
            }
            $transaction->commit();
            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        if (!$success) {
            notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
        } else {
            notify()->addSuccess(t(
                'landing_pages',
                'The variant {name} has been successfully created! You can edit it from the Inactive variants list.',
                [
                    '{name}' => $variant->title,
                ]
            ));
        }

        hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'success'    => notify()->getHasSuccess(),
            'page'       => $page,
            'variant'    => $variant,
        ]));

        $this->redirect(['landing_pages/overview', 'id' => $page->getHashId()]);
    }

    /**
     * Update a Landing page variant
     *
     * @param string $id
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpdate($id)
    {
        $variant             = $this->loadLandingPageVariantModel($id);
        $variantLastRevision = $variant->revision;
        $page                = $this->loadLandingPageModel(hashIds()->encode((int)$variantLastRevision->page_id));

        // Doing some checks to be sure we are editing the correct variant
        $pageLastRevision = $page->lastRevision;
        if ($page->getIsStandard() && !empty($pageLastRevision) && (int)$variantLastRevision->revision_id !== (int)$pageLastRevision->revision_id) {
            $activeVariant = $pageLastRevision->activeVariant;
            if (empty($activeVariant)) {
                notify()->addWarning(t(
                    'landing_pages',
                    'Something went wrong. The active variant should not be missing.'
                ));
                $this->redirect(['overview', 'id' => $page->getHashId()]);
                return;
            }
            notify()->addWarning(t(
                'landing_pages',
                'You are not editing the last revision as it should for a standard page. You were redirected to it'
            ));
            $this->redirect(['update', 'id' => $activeVariant->getHashId()]);
            return;
        }

        $initialVariantHash  = $variant->getSignature();
        $initialVariantTitle = $variant->title;

        if (
            request()->getIsPostRequest() && ($attributes = (array)request()->getPost($variant->getModelName(), []))
        ) {
            /** @var array $post */
            $post                = (array)request()->getOriginalPost('', []);
            $variant->attributes = $attributes;

            if (isset($post[$variant->getModelName()]['content'])) {
                $variant->content = (string)$post[$variant->getModelName()]['content'];
            } else {
                $variant->content = '';
            }

            $postVariantHash  = $variant->getSignature();
            $postVariantTitle = $variant->title;
            $formHasChanges   = ($initialVariantHash !== $postVariantHash);
            $success          = false;
            $errorMessage     = t('app', 'Your form has a few errors, please fix them and try again!');

            $transaction = db()->beginTransaction();

            try {
                if (!$formHasChanges) {
                    $success = true;
                    // Save the title though if it has changes
                    if ($initialVariantTitle !== $postVariantTitle) {
                        $success = $variant->save();
                    }

                    throw new Exception(t('landing_pages', 'Form has no changes'));
                }

                // If the page is unpublished the revision is the last one, otherwise, we will get a new one with all
                // the variants except the one we edit that we will add later
                $revision = $page->getRevisionFromLastRevision([$variant->variant_id]);

                if (empty($revision)) {
                    throw new Exception(t('landing_pages', 'Cannot create the new revision'));
                }

                // This is happening when the page is unpublished, and we just edit the last revision
                if ($revision->revision_id === $variantLastRevision->revision_id) {
                    if (!$variant->save()) {
                        throw new Exception(t('landing_pages', 'Cannot save the variant'));
                    }
                } else { // We are having a new revision here
                    // Since we skipped the current variant when generating the revision, we add it here
                    $newVariant              = $variant->copy('', false, true, false, false);
                    $newVariant->revision_id = $revision->revision_id;

                    if (!$newVariant->save()) {
                        $variant->addErrors($newVariant->getErrors());
                        throw new Exception(t('landing_pages', 'Cannot save the variant'));
                    }

                    if ($newVariant->getIsActive()) {
                        // The page has unpublished changes
                        $page->has_unpublished_changes = LandingPage::TEXT_YES;
                        if (!$page->save(false)) {
                            throw new Exception(t('landing_pages', 'Cannot save the page'));
                        }
                    }

                    // Handle the urls
                    if (!$newVariant->copyTrackUrlsFromVariant($variant)) {
                        throw new Exception(t('landing_pages', 'Cannot save the variant urls'));
                    }
                    $newVariant->removeTrackedUrlsNotFoundInTheContent();

                    $variant = $newVariant;
                }

                $success = true;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                Yii::log($errorMessage, CLogger::LEVEL_ERROR);
            }

            if (!$success) {
                $transaction->rollback();
                notify()->addError($errorMessage);
            } else {
                $transaction->commit();
                notify()->addSuccess(t('landing_pages', 'Your variant has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller' => $this,
                'success'    => notify()->getHasSuccess(),
                'page'       => $page,
                'variant'    => $variant,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['landing_page_variants/update', 'id' => $variant->getHashId()]);
                return;
            }
        }

        $this->setData([
            'pageMetaTitle' => $this->getData('pageMetaTitle') . ' | ' . t('landing_pages', 'Update variant'),
            'page'          => $page,
            'variant'       => $variant,
        ]);

        hooks()->doAction('controller_action_can_render', $collection = new CAttributeCollection([
            'controller' => $this,
            'canRender'  => true,
        ]));

        if (!$collection->itemAt('canRender')) {
            return;
        }

        $variant->fieldDecorator->onHtmlOptionsSetup = [$this, '_setDefaultEditorForContent'];

        $this->setData([
            'pageHeading'     => t('landing_pages', 'Update'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                $page->getTitle() . ' '             => createUrl(
                    'landing_pages/overview',
                    ['id' => $page->getHashId()]
                ),
                t('app', 'Update'),
            ],
        ]);

        $this->render('form', compact('page', 'variant'));
    }

    /**
     * @param string $id
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUrls($id)
    {
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(['landing_pages/index']);
        }

        $variant  = $this->loadLandingPageVariantModel($id);
        $revision = $variant->revision;

        $contentUrls = $variant->getContentUrls();

        $landingPageUrlModel = LandingPageUrl::model();

        /** @var LandingPageUrl[] $variantUrlModels */
        $variantUrlModels = (array)$landingPageUrlModel->findAllByAttributes([
            'variant_id' => $variant->variant_id,
        ]);

        $urlDestinations = [];
        foreach ($variantUrlModels as $urlModel) {
            $urlDestinations[] = $urlModel->destination;
        }

        foreach ($contentUrls as $url) {
            if (in_array($url, $urlDestinations)) {
                continue;
            }
            $model              = new LandingPageUrl();
            $model->variant_id  = (int)$variant->variant_id;
            $model->revision_id = $variant->revision_id;
            $model->page_id     = $revision->page_id;
            $model->destination = $url;
            $model->hash        = sha1($url);

            $variantUrlModels[] = $model;
        }

        if (request()->getIsPostRequest()) {
            $attributes = (array)request()->getPost($landingPageUrlModel->getModelName(), []);

            $urlsToAdd    = array_diff($attributes, $urlDestinations);
            $urlsToRemove = array_diff($urlDestinations, $attributes);

            foreach ($variantUrlModels as $model) {
                if (in_array($model->destination, $urlsToAdd)) {
                    $model->save();
                }

                if (in_array($model->destination, $urlsToRemove)) {
                    $model->delete();
                    $model->url_id = null;
                }
            }
        }

        $this->renderPartial(
            '_variant_urls',
            compact('variant', 'landingPageUrlModel', 'contentUrls', 'variantUrlModels')
        );
    }

    /**
     * @param string $id
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionSave_content($id)
    {
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(['landing_pages/index']);
        }

        $content = (string)request()->getOriginalPost('content', '');

        $variant = $this->loadLandingPageVariantModel($id);

        if (!$variant->saveContent($content)) {
            $this->renderJson([
                'status'  => 'error',
                'message' => t('landing_pages', 'Cannot save the variant content'),
            ]);
        }

        $this->renderJson([
            'status'  => 'success',
            'message' => t('landing_pages', 'Your content was saved successfully'),
        ]);
    }

    /**
     * @param string $id
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionSave_attributes($id)
    {
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(['landing_pages/index']);
        }

        $title = (string)request()->getPost('title', '');

        $variant = $this->loadLandingPageVariantModel($id);
        $variant->title = $title;
        if (!$variant->validate(['title'])) {
            $this->renderJson([
                'status'  => 'error',
                'message' => t('landing_pages', 'Cannot save the variant title'),
            ]);
            return;
        }

        $variant->saveAttributes(['title']);

        $this->renderJson([
            'status'  => 'success',
            'message' => t('landing_pages', 'Your title was saved successfully'),
        ]);
    }

    /**
     * Delete a landing page variant.
     *
     * @param string $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionDelete($id)
    {
        $variant      = $this->loadLandingPageVariantModel($id);
        $lastRevision = $variant->revision;
        $page         = $this->loadLandingPageModel(hashIds()->encode((int)$lastRevision->page_id));

        if ($variant->getCanBeDeleted()) {
            $transaction = db()->beginTransaction();
            $success     = false;

            try {
                $revision = $page->getRevisionFromLastRevision([$variant->variant_id]);
                if (empty($revision)) {
                    throw new Exception(t('landing_pages', 'Cannot create the new revision'));
                }

                // This is happening when the page is unpublished and we just edit the last revision
                if ($revision->revision_id === $lastRevision->revision_id) {
                    if (!$variant->delete()) {
                        throw new Exception(t('landing_pages', 'Cannot delete the variant'));
                    }
                }

                $transaction->commit();
                $success = true;
            } catch (Exception $e) {
                $transaction->rollback();
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }

            if ($success) {
                notify()->addSuccess(t('landing_pages', 'Variant has been successfully deleted!'));
            } else {
                notify()->addError(t('landing_pages', 'Unable to delete the variant!'));
            }
        }

        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', ['landing_pages/overview', 'id' => $page->page_id]));
        }
    }

    /**
     * @param string $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionRevert($id)
    {
        $variant          = $this->loadLandingPageVariantModel($id);
        $lastRevision     = $variant->revision;
        $page             = $this->loadLandingPageModel(hashIds()->encode((int)$lastRevision->page_id));
        $publishedVariant = $page->pickPublishedVariant();

        $transaction = db()->beginTransaction();
        $success     = false;

        try {
            if (empty($publishedVariant)) {
                throw new Exception(t('landing_pages', 'There is no published variant'));
            }
            $variant->attributes = $publishedVariant->attributes;
            $variant->save();

            // We remove all the variants urls
            $criteria = new CDbCriteria();
            $criteria->compare('variant_id', $variant->variant_id);
            LandingPageUrl::model()->deleteAll($criteria);

            // We add to the variant the published variant's urls
            if (!$variant->copyTrackUrlsFromVariant($publishedVariant)) {
                throw new Exception(t('landing_pages', 'Cannot save the variant url'));
            }

            $transaction->commit();
            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        if ($success) {
            notify()->addSuccess(t('landing_pages', 'Variant has been successfully reverted!'));
        } else {
            notify()->addError(t('landing_pages', 'Unable to revert the variant!'));
        }

        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', ['landing_pages/overview', 'id' => $page->page_id]));
        }
    }

    /**
     * Change the active status of a landing page variant.
     *
     * @param string $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionToggle_active($id)
    {
        $variant      = $this->loadLandingPageVariantModel($id);
        $lastRevision = $variant->revision;
        $page         = $this->loadLandingPageModel(hashIds()->encode((int)$lastRevision->page_id));

        $transaction = db()->beginTransaction();
        $success     = false;

        try {
            $revision = $page->getRevisionFromLastRevision();
            if (empty($revision)) {
                throw new Exception(t('landing_pages', 'Cannot create the new revision'));
            }

            // This is happening when the page is unpublished, and we just edit the last revision
            if ($revision->revision_id === $lastRevision->revision_id) {
                if (!$variant->toggleActive()) {
                    throw new Exception(t('landing_pages', 'Cannot toggle active'));
                }
            } else { // We are having a new revision here
                $newVariant = LandingPageRevisionVariant::model()->findByAttributes([
                    'revision_id'  => $revision->revision_id,
                    'created_from' => $variant->variant_id,
                ]);

                // TODO - Should we reset the stats? TBD

                if (!$newVariant || !$newVariant->toggleActive()) {
                    throw new Exception(t('landing_pages', 'Cannot toggle active'));
                }

                // The page has unpublished changes
                $page->has_unpublished_changes = LandingPage::TEXT_YES;
                if (!$page->save(false)) {
                    throw new Exception(t('landing_pages', 'Cannot save the page'));
                }
            }

            $transaction->commit();
            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        if ($success) {
            notify()->addSuccess(t('landing_pages', 'Variant active status has been successfully changed!'));
        } else {
            notify()->addError(t('landing_pages', 'Unable to change the variant active status!'));
        }

        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', ['landing_pages/overview', 'id' => $page->page_id]));
        }
    }

    /**
     * @param string $id
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionCopy($id)
    {
        $variant      = $this->loadLandingPageVariantModel($id);
        $lastRevision = $variant->revision;
        $page         = $this->loadLandingPageModel(hashIds()->encode((int)$lastRevision->page_id));

        $transaction = db()->beginTransaction();
        $success     = false;

        try {
            $revision = $page->getRevisionFromLastRevision();
            if (empty($revision)) {
                throw new Exception(t('landing_pages', 'Cannot create the new revision'));
            }

            $newVariant              = $variant->copy(LandingPageRevisionVariant::TEXT_NO, false, true, true, false);
            $newVariant->revision_id = $revision->revision_id;

            if (!$newVariant->save()) {
                throw new Exception(t('landing_pages', 'Cannot create the new variant'));
            }

            // Handle the New Variant urls
            if (!$newVariant->copyTrackUrlsFromVariant($variant)) {
                throw new Exception(t('landing_pages', 'Cannot save the variant url'));
            }

            $transaction->commit();
            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        if ($success) {
            notify()->addSuccess(t('landing_pages', 'Variant has been successfully copied!'));
        } else {
            notify()->addError(t('landing_pages', 'Unable to copy the variant!'));
        }
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(request()->getPost('returnUrl', ['landing_pages/overview', 'id' => $page->page_id]));
        }
    }

    /**
     * @param string $id
     * @return LandingPage
     * @throws CHttpException
     */
    public function loadLandingPageModel(string $id): LandingPage
    {
        $page = LandingPage::model()->findByAttributes([
            'page_id'     => (int)LandingPage::decodeHashId($id),
            'customer_id' => (int)customer()->getId(),
        ]);

        if (empty($page)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        return $page;
    }

    /**
     * @param string $id
     * @return LandingPageRevisionVariant
     * @throws CHttpException
     */
    public function loadLandingPageVariantModel(string $id): LandingPageRevisionVariant
    {
        $variant = LandingPageRevisionVariant::model()->findByAttributes([
            'variant_id' => (int)LandingPageRevisionVariant::decodeHashId($id),
        ]);

        if (empty($variant)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        return $variant;
    }

    /**
     * @param CEvent $event
     *
     * @return void
     */
    public function _setDefaultEditorForContent(CEvent $event)
    {
        if ($event->params['attribute'] == 'content') {
            $options = [];
            if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
                $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
            }

            $options['id']             = CHtml::activeId($event->sender->owner, 'content');
            $options['fullPage']       = false;
            $options['allowedContent'] = true;
            $options['contentsCss']    = [];
            $options['height']         = 800;

            $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
        }
    }
}
