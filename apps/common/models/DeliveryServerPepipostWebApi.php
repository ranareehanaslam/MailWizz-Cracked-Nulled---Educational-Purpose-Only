<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DeliveryServerPepipostWebApi
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.1
 *
 */

class DeliveryServerPepipostWebApi extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'pepipost-web-api';

    /**
     * @var string
     */
    protected $_providerUrl = 'https://www.pepipost.com/';

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['password', 'required'],
            ['password', 'length', 'max' => 255],
        ];
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'password' => t('servers', 'Api key'),
        ];
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'password' => t('servers', 'One of your pepipost api keys.'),
        ];

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'password' => t('servers', 'Api key'),
        ];

        return CMap::mergeArray(parent::attributePlaceholders(), $placeholders);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return DeliveryServerPepipostWebApi the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var DeliveryServerPepipostWebApi $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return array
     * @throws CException
     */
    public function send(array $params = []): array
    {
        /** @var array $params */
        $params = (array)hooks()->applyFilters('delivery_server_before_send_email', $this->getParamsArray($params), $this);

        if (!ArrayHelper::hasKeys($params, ['from', 'to', 'subject', 'body'])) {
            return [];
        }

        [$toEmail, $toName]     = $this->getMailer()->findEmailAndName($params['to']);
        [$fromEmail, $fromName] = $this->getMailer()->findEmailAndName($params['from']);

        if (!empty($params['fromName'])) {
            $fromName = $params['fromName'];
        }

        $replyToEmail = $replyToName = null;
        if (!empty($params['replyTo'])) {
            [$replyToEmail, $replyToName] = $this->getMailer()->findEmailAndName($params['replyTo']);
        }

        $metaData  = [
            'message_id' => StringHelper::randomSha1(),
        ];

        if (!empty($params['campaignUid'])) {
            $metaData['campaign_uid'] = $params['campaignUid'];
        }

        if (!empty($params['subscriberUid'])) {
            $metaData['subscriber_uid'] = $params['subscriberUid'];
        }

        $sent = [];

        try {
            $body = new \PepipostLib\Models\Send();
            $body->from = new \PepipostLib\Models\From();
            $body->from->email = (string)$fromEmail;
            $body->from->name = (string)$fromName;
            $body->subject = (string)$params['subject'];
            $body->replyTo = !empty($replyToEmail) ? (string)$replyToEmail : (string)$fromEmail;

            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;

            $body->content = [];
            if (!$onlyPlainText) {
                $htmlContent = new \PepipostLib\Models\Content();
                $htmlContent->type = \PepipostLib\Models\TypeEnum::HTML;
                $htmlContent->value = (string)$params['body'];
                $body->content[] = $htmlContent;
            } else {
                // They don't support plain text...
                $plainTextContent = new \PepipostLib\Models\Content();
                $plainTextContent->type = \PepipostLib\Models\TypeEnum::HTML;
                $plainTextContent->value = !empty($params['plainText']) ? (string)$params['plainText'] : CampaignHelper::htmlToText((string)$params['body']);
                $body->content[] = $plainTextContent;
            }

            $to = new \PepipostLib\Models\EmailStruct();
            $to->name = (string)$toName;
            $to->email = (string)$toEmail;
            $personalizations = new \PepipostLib\Models\Personalizations();
            $personalizations->to = [$to];
            /**
             * See 'token_to' at https://cpaasdocs.netcorecloud.com/docs/pepipost-api/c2d895762f832-send-an-email
             * See 'X-APIHEADER' at https://cpaasdocs.netcorecloud.com/docs/pepipost-api/ZG9jOjQyMTU3NjU0-sent-webhook
             */
            $personalizations->tokenTo = (string)json_encode($metaData);
            $body->personalizations = [$personalizations];

            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $body->attachments = [];
                $attachments = array_unique($params['attachments']);
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $attach = new \PepipostLib\Models\Attachments();
                        $attach->name = basename($attachment);
                        $attach->content = base64_encode((string)file_get_contents($attachment));
                        $body->attachments[] = $attach;
                    }
                }
            }

            $body->settings = new \PepipostLib\Models\Settings();
            $body->settings->footer = false;
            $body->settings->unsubscribeTrack = false;
            $body->settings->clickTrack = false;
            $body->settings->openTrack = false;
            $body->settings->hepf = false;

            $result = (object)$this->getClient()->getSend()->createGenerateTheMailSendRequest($body);

            if (empty($result->data->message_id)) {
                throw new Exception((string)json_encode($result));
            }

            $this->getMailer()->addLog('OK');
            $sent = [
                'message_id' => $result->data->message_id,
            ];
        } catch (Exception $e) {
            $this->getMailer()->addLog($e->getMessage());
            if ($e instanceof \PepipostLib\APIException) {
                $this->getMailer()->addLog($e->getContext()->getResponse()->getRawBody());
            }
        }

        if ($sent) {
            $this->logUsage();
        }

        hooks()->doAction('delivery_server_after_send_email', $params, $this, $sent);

        return (array)$sent;
    }

    /**
     * @inheritDoc
     */
    public function getParamsArray(array $params = []): array
    {
        $params['transport'] = self::TRANSPORT_PEPIPOST_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @return \PepipostLib\PepipostClient
     */
    public function getClient(): PepipostLib\PepipostClient
    {
        static $client;
        if ($client !== null) {
            return $client;
        }

        return $client = new \PepipostLib\PepipostClient($this->password);
    }

    /**
     * @inheritDoc
     */
    public function getFormFieldsDefinition(array $fields = []): array
    {
        return parent::getFormFieldsDefinition(CMap::mergeArray([
            'username'                => null,
            'hostname'                => null,
            'port'                    => null,
            'protocol'                => null,
            'timeout'                 => null,
            'signing_enabled'         => null,
            'max_connection_messages' => null,
            'bounce_server_id'        => null,
            'force_sender'            => null,
        ], $fields));
    }

    /**
     * @return void
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->hostname = 'web-api.pepipost.com';
    }
}
