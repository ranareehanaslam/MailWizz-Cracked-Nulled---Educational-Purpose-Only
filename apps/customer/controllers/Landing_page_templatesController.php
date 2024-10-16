<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Landing_page_variantsController
 *
 * Handles the actions for customer landing pages templates related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class Landing_page_templatesController extends Controller
{
    /**
     * @var string
     */
    public $layout = 'full-page';

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        /** @var Customer $customer */
        $customer = customer()->getModel();
        if ((int)$customer->getGroupOption('landing_pages.max_landing_pages', -1) === 0) {
            $this->redirect(['dashboard/index']);
        }

        // make sure the parent account has allowed access for this subaccount
        if (is_subaccount() && !subaccount()->canManageLandingPages()) {
            $this->redirect(['dashboard/index']);
        }
    }

    /**
     * @return array
     * @throws CException
     */
    public function filters()
    {
        $filters = [

        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all landing page variants
     *
     * @param string $template_id
     * @return void
     * @throws CException
     */
    public function actionView($template_id)
    {
        $template = $this->loadLandingPageTemplateModel($template_id);

        $this->render('view', [
            'template' => $template,
        ]);
    }

    /**
     * @param string $id
     * @return LandingPageTemplate
     * @throws CHttpException
     */
    public function loadLandingPageTemplateModel(string $id): LandingPageTemplate
    {
        $template = LandingPageTemplate::model()->findByAttributes([
            'template_id' => $id,
        ]);

        if (empty($template)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        return $template;
    }
}
