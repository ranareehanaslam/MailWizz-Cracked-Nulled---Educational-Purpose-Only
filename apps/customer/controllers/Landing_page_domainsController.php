<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Landing_page_domainsController
 *
 * Handles the actions for landing page domains related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */

class Landing_page_domainsController extends Controller
{
    /**
     * @return void
     * @throws CException
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
            'postOnly + delete',
        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $domain = new LandingPageDomain('search');
        $domain->unsetAttributes();

        $domain->attributes  = (array)request()->getQuery($domain->getModelName(), []);
        $domain->customer_id = customer()->getId();

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_page_domains', 'View domains'),
            'pageHeading'     => t('landing_page_domains', 'View domains'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                t('landing_page_domains', 'Domains') => createUrl('landing_page_domains/index'),
                t('app', 'View all'),
            ],
        ]);

        $this->render('list', compact('domain'));
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionCreate()
    {
        $domain = new LandingPageDomain();

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($domain->getModelName(), []))) {
            $domain->attributes  = $attributes;
            $domain->customer_id = customer()->getId();
            if (!$domain->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'domain'    => $domain,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['landing_page_domains/update', 'id' => $domain->domain_id]);
                return;
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_page_domains', 'Create new domain'),
            'pageHeading'     => t('landing_page_domains', 'Create new domain'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                t('landing_page_domains', 'Domains') => createUrl('landing_page_domains/index'),
                t('app', 'Create new'),
            ],
        ]);

        $this->render('form', compact('domain'));
    }

    /**
     * @param int $id
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpdate($id)
    {
        $domain = LandingPageDomain::model()->findByAttributes([
            'domain_id'   => (int)$id,
            'customer_id' => customer()->getId(),
        ]);

        if (empty($domain)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($domain->getModelName(), []))) {
            $domain->attributes  = $attributes;
            $domain->customer_id = customer()->getId();
            if (!$domain->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'domain'    => $domain,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['landing_page_domains/update', 'id' => $domain->domain_id]);
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('landing_page_domains', 'Update domain'),
            'pageHeading'     => t('landing_page_domains', 'Update domain'),
            'pageBreadcrumbs' => [
                t('landing_pages', 'Landing pages') => createUrl('landing_pages/index'),
                t('landing_page_domains', 'Domains') => createUrl('landing_page_domains/index'),
                t('app', 'Update'),
            ],
        ]);

        $this->render('form', compact('domain'));
    }

    /**
     * @param int $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionDelete($id)
    {
        $domain = LandingPageDomain::model()->findByAttributes([
            'domain_id'   => (int)$id,
            'customer_id' => customer()->getId(),
        ]);

        if (empty($domain)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $domain->delete();

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('app', 'The item has been successfully deleted!'));
            $redirect = request()->getPost('returnUrl', ['landing_page_domains/index']);
        }

        // since 1.3.5.9
        hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'model'      => $domain,
            'redirect'   => $redirect,
            'success'    => true,
        ]));

        if ($collection->itemAt('redirect')) {
            $this->redirect($collection->itemAt('redirect'));
        }
    }

    /**
     * Export
     *
     * @return void
     */
    public function actionExport()
    {
        $models = LandingPageDomain::model()->findAllByAttributes([
            'customer_id' => (int)customer()->getId(),
        ]);

        if (empty($models)) {
            notify()->addError(t('app', 'There is no item available for export!'));
            $this->redirect(['index']);
            return;
        }

        // Set the download headers
        HeaderHelper::setDownloadHeaders('landing-page-domains.csv');

        try {
            $csvWriter  = League\Csv\Writer::createFromPath('php://output', 'w');
            $attributes = AttributeHelper::removeSpecialAttributes($models[0]->attributes);

            /** @var callable $callback */
            $callback   = [$models[0], 'getAttributeLabel'];
            $attributes = array_map($callback, array_keys($attributes));

            $csvWriter->insertOne($attributes);

            foreach ($models as $model) {
                $attributes = AttributeHelper::removeSpecialAttributes($model->attributes);
                $csvWriter->insertOne(array_values($attributes));
            }
        } catch (Exception $e) {
        }

        app()->end();
    }
}
