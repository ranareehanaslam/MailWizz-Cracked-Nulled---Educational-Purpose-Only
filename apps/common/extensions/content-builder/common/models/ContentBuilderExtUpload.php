<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * ContentBuilderExtUpload
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class ContentBuilderExtUpload extends FormModel
{
    /**
     * @var string
     */
    public $image;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $source = '';

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
            ['image, filename', 'required'],
            ['image', '_validateBase64AsImage', 'params' => ['mimeTypes' => $imageMimes]],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'image'   => t('app', 'Image'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        $filesPath = '';

        /** @var ExtensionInit $extension */
        $extension = extensionsManager()->getExtensionInstance('content-builder');
        if ($extension->isAppName('backend') && user()->getId() > 0) {
            $alias = 'root.frontend.assets.files.images';
            $filesPath = (string)Yii::getPathOfAlias($alias);
        } elseif ($extension->isAppName('customer') && customer()->getId() > 0) {

            /** @var Customer $customer */
            $customer = customer()->getModel();

            // this is a customer requesting files.
            $customerFolderName = $customer->customer_uid;

            $alias = 'root.frontend.assets.files.customer.' . $customerFolderName . '.content-builder';
            $filesPath = (string)Yii::getPathOfAlias($alias);
        }

        return (string)hooks()->applyFilters('ext_content_builder_upload_storage_path', $filesPath, $this->source);
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getStorageUrl(bool $absolute = true): string
    {
        $filesUrl = '';

        /** @var ExtensionInit $extension */
        $extension = extensionsManager()->getExtensionInstance('content-builder');
        if ($extension->isAppName('backend') && user()->getId() > 0) {
            $url = 'frontend/assets/files/images';
            $filesUrl  = apps()->getAppUrl('frontend', $url, true, true);
        } elseif ($extension->isAppName('customer') && customer()->getId() > 0) {

            /** @var Customer $customer */
            $customer = customer()->getModel();

            // this is a customer requesting files.
            $customerFolderName = $customer->customer_uid;

            $url = 'frontend/assets/files/customer/' . $customerFolderName . '/content-builder';
            $filesUrl  = apps()->getAppUrl('frontend', $url, $absolute, true);
        }

        return (string)hooks()->applyFilters('ext_content_builder_upload_storage_url', $filesUrl, $this->source);
    }

    /**
     * @param string $attribute
     * @param array $params
     * @return void
     */
    public function _validateBase64AsImage(string $attribute, array $params)
    {
        $data = (string)ioFilter()->purify($this->$attribute);

        $data = base64_decode($data);

        if (empty($data)) {
            $this->addError($attribute, t('app', 'Not a valid image'));
            return;
        }

        $imagePath = FileSystemHelper::getTmpDirectory() . '/' . StringHelper::random() . '.png';
        if (!file_put_contents($imagePath, $data)) {
            $this->addError($attribute, t('landing_pages', 'Cannot write file to disk'));
            return;
        }

        $image = ImageHelper::getImageSize($imagePath);

        unlink($imagePath);

        if (empty($image)) {
            $this->addError($attribute, t('app', 'Not a valid image'));
            return;
        }

        $allowedMimeTypes = $params['mimeTypes'] ?? ['image/png', 'image/jpeg'];
        if (!in_array($image['mime'], $allowedMimeTypes)) {
            $this->addError($attribute, t('app', 'Not a valid image mime type'));
            return;
        }
    }
}
