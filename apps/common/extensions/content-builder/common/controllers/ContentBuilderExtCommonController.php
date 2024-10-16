<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * ContentBuilderExtCommonController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class ContentBuilderExtCommonController extends ExtensionController
{
    /**
     * @return array
     */
    public function filters()
    {
        $filters = [
            'postOnly + upload',
        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * @param string $source
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpload(string $source)
    {
        $model         = new ContentBuilderExtUpload();
        $model->source = (string)$source;
        $filesPath     = $model->getStoragePath();
        $filesUrl      = $model->getStorageUrl();

        if (!$filesUrl || !$filesPath) {
            throw new CHttpException(403, t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        if (!file_exists($filesPath) || !is_dir($filesPath)) {
            mkdir($filesPath, 0777, true);
        }

        $model->image    = (string)request()->getPost('image', '');
        $model->filename = (string)request()->getPost('filename', '');

        if (!$model->validate()) {
            $this->renderJson([
                'error' => CHtml::errorSummary($model),
            ]);
        }

        $newFileName = StringHelper::random(4, true) . '-' . $model->filename;
        $success     = file_put_contents($filesPath . '/' . $newFileName, base64_decode($model->image));

        $this->renderJson([
            'success' => $success,
            'fileUrl' => $filesUrl . '/' . $newFileName,
        ]);
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionCustomer_lists_forms()
    {
        /** @var ContentBuilderExt $extension */
        $extension = $this->getExtension();
        if (!$extension->isAppName('customer')) {
            $this->renderJson([
                'html' => '',
            ]);
            return;
        }

        /** @var Customer $customer */
        $customer = customer()->getModel();

        $listsArray = CMap::mergeArray(['' => t('app', 'Choose')], $customer->getListsDropDownArray());

        $this->renderJson([
            'html' => $this->renderPartial($extension->getPathAlias('common.views.common.customer-lists-forms'), [
                'extension'    => $extension,
                'customer'     => $customer,
                'listsArray'   => $listsArray,
            ], true, true),
        ]);
    }

    /**
     * @param string $list_uid
     * @return void
     * @throws CException
     */
    public function actionGet_list_subscribe_form(string $list_uid)
    {
        /** @var ContentBuilderExt $extension */
        $extension = $this->getExtension();
        if (!$extension->isAppName('customer')) {
            $this->renderJson([
                'html' => '',
            ]);
            return;
        }

        $list = Lists::model()->findByUid($list_uid);

        if (empty($list)) {
            $this->renderJson([
                'html' => '',
            ]);
            return;
        }

        $subscribeUrl   = apps()->getAppUrl('frontend', 'lists/' . $list->list_uid . '/subscribe', true);

        try {
            $subscribeHtml = (string)(new GuzzleHttp\Client())->get($subscribeUrl)->getBody();
        } catch (Exception $e) {
            $subscribeHtml = $e->getMessage();
        }

        libxml_use_internal_errors(true);

        try {
            $query = qp($subscribeHtml, 'body', [
                'ignore_parser_warnings'    => true,
                'convert_to_encoding'       => app()->charset,
                'convert_from_encoding'     => app()->charset,
                'use_parser'                => 'html',
            ]);

            $query->top()->find('form')->attr('action', $subscribeUrl);
            $query->top()->find('form')->find('input[name="csrf_token"]')->remove();
            $subscribeForm = (string)$query->top()->find('form')->html();

            if (preg_match('#(<textarea[^>]+)/>#i', $subscribeForm)) {
                $subscribeForm = (string)preg_replace('#(<textarea[^>]+)/>#i', '$1></textarea>', $subscribeForm);
            }
        } catch (Exception $e) {
            $subscribeForm = $e->getMessage();
        }

        $this->renderJson([
            'html' => $subscribeForm,
        ]);
    }
}
