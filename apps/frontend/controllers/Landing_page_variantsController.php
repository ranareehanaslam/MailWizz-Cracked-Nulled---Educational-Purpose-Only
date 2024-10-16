<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Landing_page_variantsController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class Landing_page_variantsController extends Controller
{
    /**
     * @var string
     */
    public $layout = 'full-page';

    /**
     * @param string $id
     * @return void
     * @throws CHttpException
     * @throws Exception
     */
    public function actionView($id)
    {
        $variant  = $this->loadLandingPageVariantModel($id);
        $revision = $variant->revision;
        $page     = $this->loadLandingPageModel((int)$revision->page_id);

        $this->setData([
            'pageMetaTitle'       => $this->getData('pageMetaTitle') . ' | ' . $variant->title,
            'pageMetaDescription' => StringHelper::truncateLength($revision->description, 150),
        ]);

        $this->render('view', compact('page', 'revision', 'variant'));
    }

    /**
     * @param int $id
     * @return LandingPage
     * @throws CHttpException
     */
    protected function loadLandingPageModel(int $id): LandingPage
    {
        $page = LandingPage::model()->findByAttributes([
            'page_id'     => (int)$id,
            'customer_id' => (int)customer()->getId(),
        ]);

        if (empty($page)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        return $page;
    }

    /**
     * @param string $id
     * @return LandingPageRevisionVariant
     * @throws CHttpException
     */
    protected function loadLandingPageVariantModel(string $id): LandingPageRevisionVariant
    {
        $variant = LandingPageRevisionVariant::model()->findByAttributes([
            'variant_id' => (int)LandingPageRevisionVariant::decodeHashId($id),
        ]);

        if (empty($variant)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        return $variant;
    }
}
