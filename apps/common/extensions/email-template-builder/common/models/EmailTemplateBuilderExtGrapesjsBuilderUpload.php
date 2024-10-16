<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExtGrapesjsBuilderUpload
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class EmailTemplateBuilderExtGrapesjsBuilderUpload extends FormModel
{
    /** @var CUploadedFile[] */
    public $files;

    /**
     * @return array
     */
    public function rules()
    {
        $imageMimes = null;
        if (CommonHelper::functionExists('finfo_open')) {

            /** @var FileExtensionMimes $extensionMimes */
            $extensionMimes = app()->getComponent('extensionMimes');

            /** @var array $imageMimes */
            $imageMimes = $extensionMimes->get(['png', 'jpg', 'jpeg', 'gif'])->toArray();
        }

        $rules = [
            ['files', 'file', 'types' => ['png', 'jpg', 'jpeg', 'gif'], 'mimeTypes' => $imageMimes, 'allowEmpty' => false, 'maxFiles' => 10],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'files'   => t('app', 'Files'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        $filesPath = '';

        /** @var EmailTemplateBuilderExt $extension */
        $extension = extensionsManager()->getExtensionInstance('email-template-builder');

        if ($extension->isAppName('backend') && user()->getId() > 0) {

            // this is a user requesting files.
            $filesPath = Yii::getPathOfAlias('root.frontend.assets.files.email-templates');
        } elseif ($extension->isAppName('customer') && customer()->getId() > 0) {

            /** @var Customer $customer */
            $customer = customer()->getModel();

            // this is a customer requesting files.
            $customerFolderName = $customer->customer_uid;

            $filesPath = Yii::getPathOfAlias('root.frontend.assets.files.customer.' . $customerFolderName . '.email-templates');
        }

        return (string)$filesPath;
    }

    /**
     * @return string
     */
    public function getStorageUrl(): string
    {
        $filesUrl = '';

        /** @var EmailTemplateBuilderExt $extension */
        $extension = extensionsManager()->getExtensionInstance('email-template-builder');

        if ($extension->isAppName('backend') && user()->getId() > 0) {

            // this is a user requesting files.
            $filesUrl  = apps()->getAppUrl('frontend', 'frontend/assets/files/email-templates', true, true);
        } elseif ($extension->isAppName('customer') && customer()->getId() > 0) {

            /** @var Customer $customer */
            $customer = customer()->getModel();

            // this is a customer requesting files.
            $customerFolderName = $customer->customer_uid;

            $filesUrl  = apps()->getAppUrl('frontend', 'frontend/assets/files/customer/' . $customerFolderName . '/email-templates', true, true);
        }

        return $filesUrl;
    }
}
