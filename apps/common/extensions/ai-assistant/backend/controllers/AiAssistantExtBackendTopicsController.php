<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AiAssistantExtBackendTopicsController
 *
 * Handles the actions for topics related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */

class AiAssistantExtBackendTopicsController extends ExtensionController
{
    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->getExtension()->getPathOfAlias('backend.views.topics');
    }

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
     * List all available topics
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        $topic = new AiAssistantTopic('search');
        $topic->unsetAttributes();
        $topic->attributes = (array)request()->getQuery($topic->getModelName(), []);

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . $this->t('View topics'),
            'pageHeading'     => $this->t('View topics'),
            'pageBreadcrumbs' => [
                $this->t('Topics') => $this->getExtension()->createUrl('topics/index'),
                t('app', 'View all'),
            ],
        ]);

        $this->render('list', compact('topic'));
    }

    /**
     * Create a new topic
     *
     * @return void
     * @throws CException
     */
    public function actionCreate()
    {
        $topic = new AiAssistantTopic();

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($topic->getModelName(), []))) {
            $topic->attributes = $attributes;

            if (!$topic->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'topic'   => $topic,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect($this->getExtension()->createUrl('topics/index'));
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . $this->t('Create new topic'),
            'pageHeading'     => $this->t('Create new topic'),
            'pageBreadcrumbs' => [
                $this->t('Topics') => $this->getExtension()->createUrl('topics/index'),
                t('app', 'Create new'),
            ],
        ]);

        $this->render('form', compact('topic'));
    }

    /**
     * Update existing topic
     *
     * @param int $id
     *
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpdate($id)
    {
        $topic = AiAssistantTopic::model()->findByPk((int)$id);

        if (empty($topic)) {
            throw new CHttpException(404, t('app', 'The requested topic does not exist.'));
        }

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($topic->getModelName(), []))) {
            $topic->attributes = $attributes;

            if (!$topic->save()) {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            }

            hooks()->doAction('controller_action_save_data', $collection = new CAttributeCollection([
                'controller'=> $this,
                'success'   => notify()->getHasSuccess(),
                'topic'      => $topic,
            ]));

            if ($collection->itemAt('success')) {
                $this->redirect($this->getExtension()->createUrl('topics/index'));
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . $this->t('Update topic'),
            'pageHeading'     => $this->t('Update topic'),
            'pageBreadcrumbs' => [
                $this->t('Topics') => $this->getExtension()->createUrl('topics/index'),
                t('app', 'Update'),
            ],
        ]);

        $this->render('form', compact('topic'));
    }

    /**
     * Delete an existing topic
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
        $topic = AiAssistantTopic::model()->findByPk((int)$id);

        if (empty($topic)) {
            throw new CHttpException(404, t('app', 'The requested topic does not exist.'));
        }

        $topic->delete();

        $redirect = null;
        if (!request()->getQuery('ajax')) {
            notify()->addSuccess(t('app', 'The item has been successfully deleted!'));
            $redirect = request()->getPost('returnUrl', $this->getExtension()->createUrl('topics/index'));
        }

        // since 1.3.5.9
        hooks()->doAction('controller_action_delete_data', $collection = new CAttributeCollection([
            'controller' => $this,
            'model'      => $topic,
            'redirect'   => $redirect,
            'success'    => true,
        ]));

        if ($collection->itemAt('redirect')) {
            $this->redirect($collection->itemAt('redirect'));
        }
    }
}
