<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Handle AI Assistant settings
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 */

class AiAssistantExtBackendSettingsController extends ExtensionController
{
    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->getExtension()->getPathOfAlias('backend.views.settings');
    }

    /**
     * Common settings for OpenAI
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        /** @var AiAssistantExtCommon $model */
        $model = container()->get(AiAssistantExtCommon::class);

        if (request()->getIsPostRequest()) {
            $model->attributes = (array)request()->getPost($model->getModelName(), []);
            if ($model->save()) {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            } else {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . $this->t('AI Assistant'),
            'pageHeading'     => $this->t('AI Assistant'),
            'pageBreadcrumbs' => [
                t('app', 'Extensions') => createUrl('extensions/index'),
                $this->t('AI Assistant') => $this->getExtension()->createUrl('settings/index'),
            ],
        ]);

        $this->render('index', compact('model'));
    }
}
