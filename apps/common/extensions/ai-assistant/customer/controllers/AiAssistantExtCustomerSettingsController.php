<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Controller file for AI Assistant.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 */

class AiAssistantExtCustomerSettingsController extends ExtensionController
{
    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->getExtension()->getPathOfAlias('customer.views.settings');
    }

    /**
     * Common settings for AI Assistant
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        /** @var AiAssistantExtCustomer $model */
        $model = container()->get(AiAssistantExtCustomer::class);

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
                t('app', 'Settings') => 'javascript:;',
                $this->t('AI Assistant') => $this->getExtension()->createUrl('settings/index'),
            ],
        ]);

        $this->render('index', compact('model'));
    }
}
