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
class AiAssistantExtCommonConversationsController extends ExtensionController
{
    /**
     * Get all conversations, should accept GET
     *
     * @return void
     * @throws CException
     */
    public function actionIndex()
    {
        /** @var AiAssistantConversation[] $conversationModels */
        $conversationModels = $this->findAllConversationModels();

        $conversations = [];
        foreach ($conversationModels as $conversationModel) {
            $conversations[] = $conversationModel->getAttributesForApi();
        }

        $this->renderJson($conversations);
    }

    /**
     * Creates a conversation, should accept POST
     *
     * @return void
     * @throws CException
     * @throws Exception
     */
    public function actionCreate()
    {
        $model = new AiAssistantConversation();

        $attributes        = (array)request()->getPost($model->getModelName(), []);
        $model->name       = t('ai_assistant_ext', 'New conversation');
        $model->attributes = $attributes;
        $model->attributes = $this->getOwnerAttributes();

        /** @var AiAssistantExtCommon $commonSettings */
        $commonSettings = container()->get(AiAssistantExtCommon::class);

        /** @var AiAssistantExtCustomer|AiAssistantExtCommon $settings */
        $settings = $this->getOwnerSettingsInstance();

        $model->open_ai_model             = $commonSettings->getOpenAiModel();
        $model->open_ai_max_tokens        = $settings->getOpenAiMaxTokens();
        $model->open_ai_temperature       = $settings->getOpenAiTemperature();
        $model->open_ai_frequency_penalty = $settings->getOpenAiFrequencyPenalty();
        $model->open_ai_presence_penalty  = $settings->getOpenAiPresencePenalty();
        $model->open_ai_stop              = $settings->getOpenAiStop();

        if (!$model->save()) {
            $this->renderJson(array_merge($model->getAttributesForApi(), [
                'errors' => $model->shortErrors->getAll(),
            ]), 422);
            return;
        }

        $this->renderJson($model->getAttributesForApi(), 201);
    }

    /**
     * @param int $conversation_id
     * @return void
     * @throws CException
     */
    public function actionUpdate($conversation_id)
    {
        $model = $this->findOneConversationModel((int)$conversation_id);

        if (empty($model)) {
            $this->renderJson([
                'error' => t('ai_assistant_ext', 'Requested conversation does not exist.'),
            ], 404);
            return;
        }

        $model->attributes  = (array)request()->getPost($model->getModelName(), []);
        $model->attributes = $this->getOwnerAttributes();

        if (!$model->save()) {
            $this->renderJson(array_merge($model->getAttributesForApi(), [
                'errors' => $model->shortErrors->getAll(),
            ]), 422);
            return;
        }

        $this->renderJson($model->getAttributesForApi());
    }

    /**
     * @param int $conversation_id
     * @return void
     * @throws CException
     */
    public function actionCreate_message($conversation_id)
    {
        $conversation = $this->findOneConversationModel((int)$conversation_id);

        if (empty($conversation)) {
            $this->renderJson([
                'error' => t('ai_assistant_ext', 'Requested conversation does not exist.'),
            ], 404);
            return;
        }

        $model             = new AiAssistantConversationMessage();
        $model->attributes = (array)request()->getPost($model->getModelName(), []);

        // The message supports markdown only
        /** @var array $post */
        $post = (array)request()->getOriginalPost($model->getModelName(), []);
        if (isset($post['message'])) {
            $model->message = $post['message'];
        }

        $model->conversation_id = $conversation->conversation_id;

        if (!$model->save()) {
            $this->renderJson(array_merge($model->getAttributesForApi(), [
                'errors' => $model->shortErrors->getAll(),
            ]), 422);
            return;
        }

        $this->renderJson($model->getAttributesForApi(), 201);
    }

    /**
     * @param int $conversation_id
     * @return void
     * @throws CException
     */
    public function actionGet_open_ai_response($conversation_id)
    {
        $conversation = $this->findOneConversationModel((int)$conversation_id);

        if (empty($conversation)) {
            $this->renderJson([
                'error' => t('ai_assistant_ext', 'Requested conversation does not exist.'),
            ], 404);
            return;
        }

        /** @var AiAssistantConversationMessage $model */
        /** @var bool $success */
        /** @var string $errorMessage */
        /** @var int $errorCode */
        [$model, $success, $errorMessage, $errorCode] = $conversation->getOpenAiResponseAsMessage();

        if (!$success) {
            $model->addError('message', (string)$errorMessage);
            $this->renderJson(array_merge($model->getAttributesForApi(), [
                'errors' => $model->shortErrors->getAll(),
            ]), (int)$errorCode);
            return;
        }

        $this->renderJson($model->getAttributesForApi(), 201);
    }

    /**
     * @param int $conversation_id
     * @return void
     * @throws CException
     */
    public function actionGet_messages($conversation_id)
    {
        $conversation = $this->findOneConversationModel((int)$conversation_id);

        if (empty($conversation)) {
            $this->renderJson([
                'error' => t('ai_assistant_ext', 'Requested conversation does not exist.'),
            ], 404);
            return;
        }

        $messages = [];
        foreach ($conversation->messages as $messageModel) {
            $messages[] = $messageModel->getAttributesForApi();
        }

        $this->renderJson($messages);
    }

    /**
     * @param int $conversation_id
     * @return void
     * @throws CException
     */
    public function actionDelete($conversation_id)
    {
        $model = $this->findOneConversationModel((int)$conversation_id);

        if (empty($model)) {
            $this->renderJson([
                'error' => t('ai_assistant_ext', 'Requested conversation does not exist.'),
            ], 404);
            return;
        }

        if (!$model->delete()) {
            $this->renderJson([
                'error' => t('ai_assistant_ext', 'The conversation could not be deleted'),
            ], 422);
            return;
        }

        $this->renderJson($model->getAttributesForApi());
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionTopics()
    {
        /** @var AiAssistantTopic[] $topicsModels */
        $topicsModels = AiAssistantTopic::model()->findAll();

        $topics = [];
        foreach ($topicsModels as $topicsModel) {
            $topics[] = $topicsModel->getAttributesForApi();
        }

        $this->renderJson($topics);
    }

    /**
     * @return void
     * @throws CException
     * @throws Exception
     */
    public function actionSettings()
    {
        /** @var AiAssistantExtCustomer|AiAssistantExtCommon $model */
        $model = $this->getOwnerSettingsInstance();

        if (request()->getIsPostRequest()) {
            $model->attributes = (array)request()->getPost($model->getModelName(), []);
            if (!$model->save()) {
                $this->renderJson(array_merge($model->getAttributesForApi(), [
                    'errors' => $model->shortErrors->getAll(),
                ]), 422);
                return;
            }

            $this->renderJson($model->getAttributesForApi());
            return;
        }

        $this->renderJson($model->getAttributesForApi());
    }
    /**
     * @param $action
     * @return bool
     * @throws CException
     * @throws CHttpException
     */
    protected function beforeAction($action)
    {
        /** @var AiAssistantExtCommon $commonSettings */
        $commonSettings = container()->get(AiAssistantExtCommon::class);

        /** @var Customer $customer */
        $customer = customer()->getModel();

        if (apps()->isAppName('customer') && !$commonSettings->checkCustomerAccess($customer)) {
            throw new CHttpException(403, t('app', 'Your access to this resource is forbidden.'));
        }

        return parent::beforeAction($action);
    }

    /**
     * @param int $conversation_id
     * @return AiAssistantConversation|null
     */
    protected function findOneConversationModel(int $conversation_id): ?AiAssistantConversation
    {
        $attributes = [
            'conversation_id' => (int)$conversation_id,
        ];

        $ownerAttributes = $this->getOwnerAttributes();
        if (empty($ownerAttributes)) {
            return null;
        }

        return AiAssistantConversation::model()->findByAttributes(array_merge($attributes, $ownerAttributes));
    }

    /**
     * @return AiAssistantConversation[]|null
     */
    protected function findAllConversationModels(): ?array
    {
        $ownerAttributes = $this->getOwnerAttributes();
        if (empty($ownerAttributes)) {
            return [];
        }

        return AiAssistantConversation::model()->findAllByAttributes($ownerAttributes);
    }

    /**
     * @return array
     */
    protected function getOwnerAttributes(): array
    {
        $array = [];

        if (apps()->isAppName('backend')) {
            $user = user()->getModel();
            if ($user) {
                $array = ['user_id' => $user->user_id];
            }
        }

        if (apps()->isAppName('customer')) {
            $customer = customer()->getModel();
            if ($customer) {
                $array = ['customer_id' => $customer->customer_id];
            }
        }

        return $array;
    }

    /**
     * @return AiAssistantExtCommon|AiAssistantExtCustomer|null
     * @throws Exception
     */
    protected function getOwnerSettingsInstance()
    {
        /** @var AiAssistantExtCustomer|AiAssistantExtCommon|null $settings */
        $settings = null;
        if (apps()->isAppName('backend')) {
            /** @var AiAssistantExtCustomer|AiAssistantExtCommon|null $settings */
            $settings = container()->get(AiAssistantExtCommon::class);
        }

        if (apps()->isAppName('customer')) {
            /** @var AiAssistantExtCustomer|AiAssistantExtCommon|null $settings */
            $settings = container()->get(AiAssistantExtCustomer::class);
        }

        return $settings;
    }
}
