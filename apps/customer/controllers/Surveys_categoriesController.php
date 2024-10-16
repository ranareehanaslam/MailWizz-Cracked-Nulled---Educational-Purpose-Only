<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Surveys_categoriesController
 *
 * Handles the actions for surveys categories related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.2
 */

class Surveys_categoriesController extends Controller
{
    /**
     * @return void
     * @throws CException
     */
    public function init()
    {
        /** @var Customer|null $customer */
        $customer = customer()->getModel();

        if (empty($customer)) {
            $this->redirect(['guest/index']);
            return;
        }

        if ((int)$customer->getGroupOption('surveys.max_surveys', -1) == 0) {
            $this->redirect(['dashboard/index']);
            return;
        }

        // make sure the parent account has allowed access for this subaccount
        if (is_subaccount() && !subaccount()->canManageSurveys()) {
            $this->redirect(['dashboard/index']);
            return;
        }

        parent::init();
    }

    /**
     * @return array
     * @throws CException
     */
    public function filters()
    {
        return CMap::mergeArray([
            'postOnly + delete',
        ], parent::filters());
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $category = new SurveyCategory('search');
        $category->unsetAttributes();

        // for filters.
        $category->attributes  = (array)request()->getQuery($category->getModelName(), []);
        $category->customer_id = (int)customer()->getId();

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('surveys', 'View categories'),
            'pageHeading'     => t('surveys', 'View categories'),
            'pageBreadcrumbs' => [
                t('surveys', 'Surveys') => createUrl('surveys/index'),
                t('surveys', 'Categories') => createUrl('surveys_categories/index'),
                t('app', 'View all'),
            ],
        ]);

        $this->render('list', compact('category'));
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionCreate()
    {
        $category = new SurveyCategory();
        $category->customer_id = (int)customer()->getId();

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($category->getModelName(), []))) {
            $category->attributes  = $attributes;
            $category->customer_id = (int)customer()->getId();

            if (!$category->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'category'   => $category,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['surveys_categories/index']);
                return;
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('surveys', 'Create new category'),
            'pageHeading'     => t('surveys', 'Create new category'),
            'pageBreadcrumbs' => [
                t('surveys', 'Surveys') => createUrl('surveys/index'),
                t('surveys', 'Categories') => createUrl('surveys_categories/index'),
                t('app', 'Create new'),
            ],
        ]);

        $this->render('form', compact('category'));
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
        $category = SurveyCategory::model()->findByAttributes([
            'category_id' => (int)$id,
            'customer_id' => (int)customer()->getId(),
        ]);

        if (empty($category)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($category->getModelName(), []))) {
            $category->attributes  = $attributes;
            $category->customer_id = (int)customer()->getId();

            if (!$category->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller' => $this,
                'success'    => notify()->getHasSuccess(),
                'category'   => $category,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect(['surveys_categories/index']);
                return;
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('surveys', 'Update category'),
            'pageHeading'     => t('surveys', 'Update category'),
            'pageBreadcrumbs' => [
                t('surveys', 'Surveys') => createUrl('surveys/index'),
                t('surveys', 'Categories') => createUrl('surveys_categories/index'),
                t('app', 'Update'),
            ],
        ]);

        $this->render('form', compact('category'));
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
        $category = SurveyCategory::model()->findByAttributes([
            'category_id' => (int)$id,
            'customer_id' => (int)customer()->getId(),
        ]);

        if (empty($category)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $category->delete();

        $redirect = '';
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('app', 'The item has been successfully deleted!'));
            $redirect = request()->getPost('returnUrl', ['surveys_categories/index']);
        }

        // since 1.3.5.9
        hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'model'      => $category,
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
        $models = SurveyCategory::model()->findAllByAttributes([
            'customer_id' => (int)customer()->getId(),
        ]);

        if (empty($models)) {
            notify()->addError(t('app', 'There is no item available for export!'));
            $this->redirect(['index']);
            return;
        }

        // Set the download headers
        HeaderHelper::setDownloadHeaders('surveys-categories.csv');

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
