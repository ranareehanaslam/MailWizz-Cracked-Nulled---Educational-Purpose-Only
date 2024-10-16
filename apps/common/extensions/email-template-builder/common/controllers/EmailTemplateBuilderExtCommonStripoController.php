<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExtCommonStripoController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
class EmailTemplateBuilderExtCommonStripoController extends ExtensionController
{
    /**
     * @return array
     */
    public function filters()
    {
        $filters = [];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionToken()
    {
        /** @var EmailTemplateBuilderExtStripoCommon $settings */
        $settings = container()->get(EmailTemplateBuilderExtStripoCommon::class);

        $response = [
            'token' => '',
            'error' => '',
        ];

        try {
            $httpResponse = (new GuzzleHttp\Client())->post('https://plugins.stripo.email/api/v1/auth', [
                'json' => [
                    'pluginId'  => $settings->plugin_id,
                    'secretKey' => $settings->getSecretKey(),
                ],
            ]);

            $responseJson      = (array)json_decode($httpResponse->getBody()->getContents(), true);
            $response['token'] = $responseJson['token'] ?? '';

            $statusCode = 200;
        } catch (Exception $e) {
            $response['error'] = $this->t('Error retrieving the token. Please check the Stripo Plugin ID and Secret Key');

            $statusCode = $e->getCode() ?: 400;
        }

        $this->renderJson($response, $statusCode);
    }
}
