<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Api_keysController
 *
 * Handles the actions for api keys related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @version 1.0
 * @since 1.0
 */

class Api_keysController extends Controller
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

        /** @var OptionCommon $common */
        $common = container()->get(OptionCommon::class);

        if (!$common->getIsApiOnline()) {
            $this->redirect(['dashboard/index']);
            return;
        }
        if ($customer->getGroupOption('api.enabled', 'yes') != 'yes') {
            $this->redirect(['dashboard/index']);
            return;
        }

        // make sure the parent account has allowed access for this subaccount
        if (is_subaccount() && !subaccount()->canManageApiKeys()) {
            $this->redirect(['dashboard/index']);
        }

        $this->addPageScripts([
            ['src' => AssetsUrl::js('api-keys.js')],
        ]);
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
     * List available api keys
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $model = new CustomerApiKey('search');
        $model->attributes = (array)request()->getQuery($model->getModelName(), []);
        $model->customer_id = customer()->getId();

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('api_keys', 'Api keys'),
            'pageHeading'     => t('api_keys', 'Api keys'),
            'pageBreadcrumbs' => [
                t('api_keys', 'Api keys') => createUrl('api_keys/index'),
                t('app', 'View all'),
            ],
        ]);

        $this->render('list', compact('model'));
    }

    /**
     * Generate a new api key
     *
     * @return void
     */
    public function actionGenerate()
    {
        $model = new CustomerApiKey();
        $model->customer_id = customer()->getId();
        $model->save();

        notify()->addInfo(t('api_keys', 'A new API access has been added:<br />Key: {key}', [
            '{key}'  => $model->key,
        ]));

        $this->redirect(['api_keys/update', 'id' => $model->key_id]);
    }

    /**
     * Update existing keys
     *
     * @param int $id
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpdate($id)
    {
        $model = CustomerApiKey::model()->findByAttributes([
            'key_id'        => (int)$id,
            'customer_id'   => (int)customer()->getId(),
        ]);

        if (empty($model)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        // since 2.2.9
        $criteria = new CDbCriteria();
        $criteria->order = 'name ASC';
        /** @var CustomerApiKeyPermission[] $permissionsList */
        $permissionsList = CustomerApiKeyPermission::model()->findAll($criteria);

        /** @var integer[] $selectedPermissions */
        $selectedPermissions = array_map(function (CustomerApiKeyPermission $permission): int {
            return (int)$permission->permission_id;
        }, !empty($model->permissions) ? $model->permissions : []);
        //

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($model->getModelName(), []))) {

            /** @var integer[] $selectedPermissions */
            $selectedPermissions = (array) request()->getPost('permissions', []);

            $model->attributes  = $attributes;
            $model->customer_id = customer()->getId();
            if (!$model->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                if ($model->getPermissionsEnabled() && count($selectedPermissions) === 0) {
                    notify()->addWarning(t('api_keys', 'Permissions are enabled but there is no permission selected for this api key!'));
                } else {
                    notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
                }

                // since 2.2.9
                CustomerApiKeyToPermission::model()->deleteAllByAttributes([
                    'key_id' => (int)$model->key_id,
                ]);
                foreach ($selectedPermissions as $selectedPermission) {
                    /** @var CustomerApiKeyPermission|null $permission */
                    $permission = CustomerApiKeyPermission::model()->findByPk((int)$selectedPermission);
                    if (empty($permission)) {
                        continue;
                    }
                    $relation = new CustomerApiKeyToPermission();
                    $relation->key_id        = (int)$model->key_id;
                    $relation->permission_id = (int)$permission->permission_id;
                    $relation->save();
                }
                //
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'model'     => $model,
            ]));
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('api_keys', 'Update api keys'),
            'pageHeading'     => t('api_keys', 'Update api keys'),
            'pageBreadcrumbs' => [
                t('api_keys', 'Api keys') => createUrl('api_keys/index'),
                t('app', 'Update'),
            ],
        ]);

        $this->render('form', [
            'model'                => $model,
            'permissionsList'      => $permissionsList,
            'selectedPermissions'  => $selectedPermissions,
        ]);
    }

    /**
     * Delete existing api key
     *
     * @param int $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionDelete($id)
    {
        $model = CustomerApiKey::model()->findByAttributes([
            'key_id'        => (int)$id,
            'customer_id'   => (int)customer()->getId(),
        ]);

        if (empty($model)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $model->delete();

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('api_keys', 'Requested API access has been successfully removed!'));
        }

        // since 1.3.5.9
        hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'model'      => $model,
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
        $models = CustomerApiKey::model()->findAllByAttributes([
            'customer_id' => (int)customer()->getId(),
        ]);

        if (empty($models)) {
            notify()->addError(t('app', 'There is no item available for export!'));
            $this->redirect(['index']);
            return;
        }

        // Set the download headers
        HeaderHelper::setDownloadHeaders('api-keys.csv');

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
