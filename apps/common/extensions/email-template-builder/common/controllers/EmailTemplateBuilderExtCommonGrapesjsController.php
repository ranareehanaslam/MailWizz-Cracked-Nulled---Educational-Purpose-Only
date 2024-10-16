<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExtCommonGrapesjsController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class EmailTemplateBuilderExtCommonGrapesjsController extends ExtensionController
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
     * @return void
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpload()
    {
        $model     = new EmailTemplateBuilderExtGrapesjsBuilderUpload();
        $filesPath = $model->getStoragePath();
        $filesUrl  = $model->getStorageUrl();

        if (!$filesUrl || !$filesPath) {
            throw new CHttpException(403, t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        if (!file_exists($filesPath) || !is_dir($filesPath)) {
            mkdir($filesPath, 0777, true);
        }

        $model->attributes = (array)request()->getPost($model->getModelName(), []);
        if (!$model->validate()) {
            $this->renderJson([]);
        }

        /** @var CUploadedFile[] $files */
        $files = CUploadedFile::getInstances($model, 'files');

        if (empty($files)) {
            $this->renderJson([]);
        }

        $filesList = [];
        foreach ($files as $file) {
            $newFileName = StringHelper::random(4, true) . '-' . $file->getName();
            if (!$file->saveAs($filesPath . '/' . $newFileName)) {
                continue;
            }

            $filesList[] = sprintf('%s/%s', $filesUrl, $newFileName);
        }

        $this->renderJson([
            'data' => $filesList,
        ]);
    }
}
