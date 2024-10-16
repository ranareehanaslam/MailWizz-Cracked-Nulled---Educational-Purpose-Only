<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * Email template settings
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class EmailTemplateBuilderExtBackendSettingsController extends ExtensionController
{
    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->getExtension()->getPathOfAlias('backend.views.settings');
    }

    /**
     * Default action.
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        /** @var EmailTemplateBuilderExtCommon $model */
        $model = container()->get(EmailTemplateBuilderExtCommon::class);

        /** @var EmailTemplateBuilderExtStripoCommon $stripoSettings */
        $stripoSettings = container()->get(EmailTemplateBuilderExtStripoCommon::class);

        if (request()->getIsPostRequest() && ($attributes = (array)request()->getPost($model->getModelName(), []))) {
            $model->attributes          = $attributes;
            $stripoSettings->attributes = (array)request()->getPost($stripoSettings->getModelName(), []);

            if ($model->save() && $stripoSettings->save()) {
                notify()->addSuccess(t('app', 'Your form has been successfully saved!'));
            } else {
                notify()->addError(t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }

        $this->setData([
            'pageMetaTitle'   => $this->getData('pageMetaTitle') . ' | ' . $this->t('Email template builder'),
            'pageHeading'     => $this->t('Email template builder'),
            'pageBreadcrumbs' => [
                t('app', 'Extensions') => createUrl('extensions/index'),
                $this->t('Email template builder'),
            ],
        ]);

        $this->render('index', compact('model', 'stripoSettings'));
    }
}
