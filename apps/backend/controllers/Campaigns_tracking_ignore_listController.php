<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Campaigns_tracking_ignore_listController
 *
 * Handles the actions for tracking ignore list
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.0
 */

class Campaigns_tracking_ignore_listController extends Controller
{
    /**
     * @return array
     */
    public function filters()
    {
        $filters = [
            'postOnly + delete',
        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * Display the tracking ignore list
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $model = new CampaignTrackingIgnoreList('search');
        $model->unsetAttributes();

        $model->attributes = (array)request()->getQuery($model->getModelName(), []);

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . t('app', 'View campaigns tracking ignore list'),
            'pageHeading'     => t('app', 'View campaigns tracking ignore list'),
            'pageBreadcrumbs' => [
                t('app', 'Campaigns tracking ignore list'),
            ],
        ]);

        $this->render('index', compact('model'));
    }

    /**
     * Toggle the status of a tracking ignore list item
     *
     * @param int $id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionToggle_status($id)
    {
        $model = CampaignTrackingIgnoreList::model()->findByPk((int)$id);

        if (empty($model)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $model->toggleStatus();

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('app', 'The item has been successfully updated!'));
            $redirect = request()->getPost('returnUrl', ['campaigns_tracking_ignore_list/index']);
        }

        if ($redirect) {
            $this->redirect($redirect);
        }
    }

    /**
     * Delete an existing tracking ignore list item
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
        $model = CampaignTrackingIgnoreList::model()->findByPk((int)$id);

        if (empty($model)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $model->delete();

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('app', 'The item has been successfully deleted!'));
            $redirect = request()->getPost('returnUrl', ['campaigns_tracking_ignore_list/index']);
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
}
