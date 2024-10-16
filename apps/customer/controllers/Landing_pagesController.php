<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Landing_pagesController
 *
 * Handles the actions for customer landing pages related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class Landing_pagesController extends Controller
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

        $this->addPageScript(['src' => AssetsUrl::js('landing-pages.js')]);
    }

    /**
     * @return array
     * @throws CException
     */
    public function filters()
    {
        $filters = [
            'postOnly + delete',
        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all landing pages
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $page = new LandingPage('search');
        $page->unsetAttributes();

        // for filters.
        $page->attributes  = (array)request()->getQuery($page->getModelName(), []);
        $page->customer_id = (int)customer()->getId();

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_pages', 'Landing pages'),
            'pageHeading'     => t('landing_pages', 'Landing pages'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                t('app', 'View all'),
            ],
        ]);

        $this->render('list', compact('page'));
    }

    /**
     * Add a new Landing page
     *
     * @return void
     * @throws CException
     */
    public function actionCreate()
    {
        /** @var Customer $customer */
        $customer = customer()->getModel();

        if (($limit = (int)$customer->getGroupOption('landing_pages.max_landing_pages', -1)) > -1) {
            $count = LandingPage::model()->countByAttributes(['customer_id' => (int)$customer->customer_id]);
            if ($count >= $limit) {
                notify()->addWarning(t('landing_pages', 'You have reached the maximum number of allowed landing pages!'));
                $this->redirect(['landing_pages/index']);
            }
        }

        $page     = new LandingPage();
        $revision = new LandingPageRevision();
        // We will always create an active variant when creating a new page
        $variant = new LandingPageRevisionVariant();

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($revision->getModelName(), []))) {
            $revision->attributes = $attributes;
            $page->customer_id    = (int)customer()->getId();

            $transaction = db()->beginTransaction();
            $success     = false;

            try {
                if (!$page->save(false)) {
                    throw new Exception(t('landing_pages', 'Cannot create the page'));
                }

                $revision->page_id = $page->page_id;
                if (!$revision->save()) {
                    throw new Exception(t('landing_pages', 'Cannot create the page revision'));
                }

                // We make the revision as the page published one
                $page->revision_id = $revision->revision_id;
                $page->slug        = $page->generateSlug($revision->title);
                if (!$page->save()) {
                    throw new Exception(t('landing_pages', 'Cannot assign the revision to the page'));
                }

                $variant->revision_id = $revision->revision_id;
                $title                = t('landing_pages', '{page} Variant', ['{page}' => $revision->title]);
                $content              = $this->renderFile(
                    dirname(__FILE__) . '/../views/landing_page_variants/_default_template.php',
                    null,
                    true
                );

                // If we are using a template
                if (!empty($revision->template_id)) {
                    $template = LandingPageTemplate::model()->findByPk((int)$revision->template_id);
                    if (!empty($template)) {
                        $content = $template->content;
                    }
                }

                $variant->title   = $title;
                $variant->content = $content;

                // The default for active is NO, because this is the only case when we want it as active at creation
                $variant->active = LandingPageRevisionVariant::TEXT_YES;
                if (!$variant->save()) {
                    throw new Exception(t('landing_pages', 'Cannot save the page variant'));
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
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller' => $this,
                'success'    => notify()->getHasSuccess(),
                'page'       => $page,
                'revision'   => $revision,
                'variant'    => $variant,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['landing_pages/overview', 'id' => $page->getHashId()]);
                return;
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_pages', 'Landing pages'),
            'pageHeading'     => t('landing_pages', 'Add a new landing page'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                t('app', 'Create new'),
            ],
        ]);

        $this->render('create', compact('page', 'revision'));
    }

    /**
     * Update a Landing page
     * @param string $id
     *
     * @return void
     * @throws CException
     */
    public function actionUpdate($id)
    {
        $page         = $this->loadLandingPageModel($id);
        $lastRevision = $page->lastRevision;

        if (empty($lastRevision)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $initialRevisionHash = $lastRevision->getSignature();

        $revision             = new LandingPageRevision();
        $revision->attributes = $lastRevision->attributes;

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($revision->getModelName(), []))) {
            $revision->attributes = $attributes;
            $revision->page_id    = $page->page_id;
            $pageAttributes       = (array)request()->getPost($page->getModelName(), []);
            $page->attributes     = $pageAttributes;
            $page->customer_id    = (int)customer()->getId();

            $postRevisionHash = $revision->getSignature();
            $formHasChanges   = ($initialRevisionHash !== $postRevisionHash);
            $success          = false;

            $transaction = db()->beginTransaction();

            try {
                if (!$revision->validate()) {
                    throw new Exception(t('landing_pages', 'The revision is having some validation errors'));
                }

                // Saving the slug. The generateSlug will generate again only if the slug is empty
                $page->slug = $page->generateSlug($revision->title);
                if (!$page->save()) {
                    throw new Exception(t('landing_pages', 'Cannot save the page'));
                }

                if (!$formHasChanges) {
                    $success = true;
                    throw new Exception(t('landing_pages', 'Form has no changes'));
                }

                // If the page is unpublished the revision is the last one, otherwise is a new one with its variants
                $newRevision = $page->getRevisionFromLastRevision();
                if (empty($newRevision)) {
                    throw new Exception(t('landing_pages', 'Cannot create the new revision'));
                }
                $newRevision->attributes = $revision->attributes;
                $newRevision->page_id    = $page->page_id;

                // This is a new revision so we keep track
                if ($newRevision->revision_id !== $lastRevision->revision_id) {
                    $revision->created_from = $lastRevision->revision_id;
                }

                if (!$newRevision->save()) {
                    throw new Exception(t('landing_pages', 'Cannot create the page revision'));
                }

                $success = true;
            } catch (Exception $e) {
            }

            if (!$success) {
                $transaction->rollback();
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $transaction->commit();
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller' => $this,
                'success'    => notify()->getHasSuccess(),
                'page'       => $page,
                'revision'   => $revision,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['landing_pages/overview', 'id' => $page->getHashId()]);
                return;
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_pages', 'Landing pages'),
            'pageHeading'     => t('landing_pages', 'Update a landing page'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                t('app', 'Update'),
            ],
        ]);

        $this->render('update', compact('page', 'revision'));
    }

    /**
     * Overview of a Landing page
     *
     * @param string $id
     *
     * @return void
     * @throws CException
     */
    public function actionOverview($id)
    {
        $page = $this->loadLandingPageModel($id);

        $lastRevision = $page->lastRevision;

        if (empty($lastRevision)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $addVariantForm = new LandingPageAddVariantForm();

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_pages', 'Overview'),
            'pageHeading'     => $page->getTitle(),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                $page->getTitle() . ' ' => createUrl('landing_pages/overview', ['id' => $page->getHashId()]),
                t('landing_pages', 'Overview'),
            ],
        ]);

        $this->render('overview', compact('page', 'lastRevision', 'addVariantForm'));
    }

    /**
     * Publish a landing page.
     *
     * @param string $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionPublish($id)
    {
        $page = $this->loadLandingPageModel($id);

        if ($page->getIsPublished()) {
            $lastRevision = $page->lastRevision;
            if (empty($lastRevision)) {
                throw new CHttpException(404, t('app', 'The requested page does not exist.'));
            }
            $page->revision_id = $lastRevision->revision_id;
        }

        $page->status                  = LandingPage::STATUS_PUBLISHED;
        $page->has_unpublished_changes = LandingPage::TEXT_NO;
        $page->save(false);

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('landing_pages', 'The page has been successfully published!'));
            $redirect = request()->getPost('returnUrl', ['landing_pages/overview', 'id' => $page->getHashId()]);
        }

        hooks()->doAction('controller_action_publish_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'page'       => $page,
            'redirect'   => $redirect,
        ]));

        if ($collection->itemAt('redirect')) {
            $this->redirect($collection->itemAt('redirect'));
        }
    }

    /**
     * Unpublish a landing page.
     *
     * @param string $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     * @throws Exception
     */
    public function actionUnpublish($id)
    {
        $page = $this->loadLandingPageModel($id);

        $page->saveStatus(LandingPage::STATUS_UNPUBLISHED);

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('landing_pages', 'The page has been successfully unpublished!'));
            $redirect = request()->getPost('returnUrl', ['landing_pages/overview', 'id' => $page->getHashId()]);
        }

        hooks()->doAction('controller_action_unpublish_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'page'       => $page,
            'redirect'   => $redirect,
        ]));

        if ($collection->itemAt('redirect')) {
            $this->redirect($collection->itemAt('redirect'));
        }
    }

    /**
     * Delete a landing page.
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
        $page = $this->loadLandingPageModel($id);

        $page->delete();

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('landing_pages', 'The page has been successfully deleted!'));
            $redirect = request()->getPost('returnUrl', ['landing_pages/index']);
        }

        hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'page'       => $page,
            'redirect'   => $redirect,
        ]));

        if ($collection->itemAt('redirect')) {
            $this->redirect($collection->itemAt('redirect'));
        }
    }

    /**
     * Run a bulk action against the selected landing pages
     *
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function actionBulk_action()
    {
        $action = request()->getPost('bulk_action');
        $items  = array_unique((array)request()->getPost('bulk_item', []));

        if ($action == LandingPage::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                $page = LandingPage::model()->findByAttributes([
                    'page_id'     => (int)$item,
                    'customer_id' => (int)customer()->getId(),
                ]);

                if (empty($page)) {
                    continue;
                }

                $page->delete();
                $affected++;
            }
            if ($affected) {
                notify()->addSuccess(t('app', 'The action has been successfully completed!'));
            }
        }

        $defaultReturn = request()->getServer('HTTP_REFERER', ['landing_pages/index']);
        $this->redirect(request()->getPost('returnUrl', $defaultReturn));
    }

    /**
     * Generate the slug for a page based on the page title
     *
     * @return void
     * @throws CException
     */
    public function actionSlug()
    {
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(['landing_pages/index']);
        }

        $page          = new LandingPage();
        $page->page_id = LandingPage::decodeHashId((string)request()->getPost('page_id', ''));
        $page->slug    = (string)request()->getPost('string');
        $page->slug    = $page->generateSlug($page->slug);

        $this->renderJson([
            'result' => 'success',
            'slug'   => $page->slug,
        ]);
    }

    /**
     * @param string $id
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionSave_domain(string $id)
    {
        if (!request()->getIsAjaxRequest()) {
            $this->redirect(['landing_pages/index']);
        }

        $domainId = request()->getPost('domain_id');
        $domainId = !empty($domainId) ? (int)$domainId : null;

        $page            = $this->loadLandingPageModel($id);
        $page->domain_id = $domainId;
        $success         = $page->save();

        $this->renderJson([
            'success'     => $success,
            'message'     => $success ? t('app', 'Your form has been successfully saved!') : t('app', 'Your form has a few errors, please fix them and try again!'),
            'permalink'   => $page->getPermalink(),
            'page_status' => $page->status,
        ]);
    }

    /**
     * @param CAction $action
     *
     * @return bool
     * @throws CException
     */
    protected function beforeAction($action)
    {
        // We make sure we publish the assets after the cache has been cleared
        $actions = ['create'];
        if (in_array($action->getId(), $actions) && extensionsManager()->isExtensionEnabled('content-builder')) {
            /** @var ContentBuilderExtCommon $settings */
            $settings       = container()->get(ContentBuilderExtCommon::class);
            $currentBuilder = $settings->getCurrentBuilderInstance();
            $currentBuilder->publishAssets();
        }

        return parent::beforeAction($action);
    }

    /**
     * @param string $id
     * @return LandingPage
     * @throws CHttpException
     */
    protected function loadLandingPageModel(string $id): LandingPage
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
}
