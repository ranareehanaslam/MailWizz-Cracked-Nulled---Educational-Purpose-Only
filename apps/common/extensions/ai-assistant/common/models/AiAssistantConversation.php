<?php declare(strict_types=1);

if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AiAssistantConversation
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

/**
 * This is the model class for table "ai_assistant_conversation".
 *
 * The followings are the available columns in table 'ai_assistant_conversation':
 * @property integer $conversation_id
 * @property integer|null $topic_id
 * @property integer|string $user_id
 * @property integer|string $customer_id
 * @property string $name
 * @property string $meta_data
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property AiAssistantTopic $topic
 * @property User $user
 * @property Customer $customer
 * @property AiAssistantConversationMessage[] $messages
 */
class AiAssistantConversation extends ActiveRecord
{
    /**
     * Use the needed traits
     */
    use AddShortcutMethodsFromCurrentExtensionTrait;

    /**
     * @var string
     */
    public $open_ai_model = AiAssistantExtCommon::OPEN_AI_MODEL_GPT_3_5;

    /**
     * @var int
     */
    public $open_ai_max_tokens = 256;

    /**
     * @var float
     */
    public $open_ai_temperature = 0.9;

    /**
     * @var float
     */
    public $open_ai_frequency_penalty = 0.0;

    /**
     * @var float
     */
    public $open_ai_presence_penalty = 0.6;

    /**
     * @var array
     */
    public $open_ai_stop = [PHP_EOL, AiAssistantExtCommon::OPEN_AI_HUMAN_STOP, AiAssistantExtCommon::OPEN_AI_AI_STOP];

    /**
     * @var string
     */
    public $summary = '';

    /**
     * @var bool
     */
    public $use_summary = false;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{ai_assistant_conversation}}';
    }

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['user_id, customer_id, topic_id', 'numerical', 'integerOnly' => true],
            ['name', 'length', 'max' => 100],
            [
                'customer_id',
                'exist',
                'className' => Customer::class,
                'criteria'  => ['condition' => '`status` = :st', 'params' => [':st' => Customer::STATUS_ACTIVE]],
            ],
            [
                'user_id',
                'exist',
                'className' => User::class,
                'criteria'  => ['condition' => '`status` = :st', 'params' => [':st' => User::STATUS_ACTIVE]],
            ],
            ['topic_id', 'exist', 'className' => AiAssistantTopic::class],

            // The following rule is used by search().
            ['user_id, customer_id, topic_id, name', 'safe', 'on' => 'search'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     * @throws CException
     */
    public function relations()
    {
        $relations = [
            'topic'    => [self::BELONGS_TO, AiAssistantTopic::class, 'topic_id'],
            'user'     => [self::BELONGS_TO, User::class, 'user_id'],
            'customer' => [self::BELONGS_TO, Customer::class, 'customer_id'],
            'messages' => [self::HAS_MANY, AiAssistantConversationMessage::class, 'conversation_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeLabels()
    {
        $labels = [
            'conversation_id' => $this->t('Conversation'),
            'user_id'         => $this->t('Assigned to'),
            'customer_id'     => $this->t('Customer'),
            'topic_id'        => $this->t('Topic'),
            'name'            => $this->t('Name'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributePlaceholders()
    {
        $placeholders = [];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     *
     * @throws CException
     */
    public function search()
    {
        $criteria       = new CDbCriteria();
        $criteria->with = [];

        if (!empty($this->user_id)) {
            if (is_numeric($this->user_id)) {
                $criteria->compare('t.user_id', (int)$this->user_id);
            } else {
                $criteria->with['user'] = [
                    'select'    => false,
                    'joinType'  => 'INNER JOIN',
                    'together'  => true,
                    'condition' => '(user.email LIKE :user OR user.first_name LIKE :user OR user.last_name LIKE :user)',
                    'params'    => [':user' => '%' . $this->user_id . '%'],
                ];
            }
        }

        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', (int)$this->customer_id);
            } else {
                $criteria->with['customer'] = [
                    'select'    => false,
                    'joinType'  => 'INNER JOIN',
                    'together'  => true,
                    'condition' => '(customer.email LIKE :customer OR customer.first_name LIKE :customer OR customer.last_name LIKE :customer)',
                    'params'    => [':customer' => '%' . $this->customer_id . '%'],
                ];
            }
        }

        if (!empty($this->topic_id)) {
            if (is_numeric($this->topic_id)) {
                $criteria->compare('t.topic_id', (int)$this->topic_id);
            } else {
                $criteria->with['topic'] = [
                    'select'    => false,
                    'joinType'  => 'INNER JOIN',
                    'together'  => true,
                    'condition' => 'topic.subject LIKE :topic',
                    'params'    => [':topic' => '%' . $this->topic_id . '%'],
                ];
            }
        }

        $criteria->compare('t.name', $this->name, true);

        $criteria->order = 't.last_updated DESC';

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'       => [
                'defaultOrder' => [
                    't.conversation_id' => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return AiAssistantConversation the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var AiAssistantConversation $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @param string $append
     * @return string
     */
    public function getMessagesText(string $append = ''): string
    {
        $text = '';

        if ($this->use_summary && $this->summary) {
            $text = $this->summary . '###';

            // Get the last message of the user and append it at the end of the summary
            $criteria = new CDbCriteria();
            $criteria->compare('conversation_id', $this->conversation_id);
            $criteria->compare('type', AiAssistantConversationMessage::TYPE_REQUEST);

            $criteria->order = 'message_id DESC';
            $criteria->limit = 1;

            $lastMessage = AiAssistantConversationMessage::model()->find($criteria);
            if ($lastMessage) {
                $text .= PHP_EOL . AiAssistantExtCommon::OPEN_AI_HUMAN_STOP . $lastMessage->message . PHP_EOL . PHP_EOL;
            }
            return $text;
        }

        if ($this->topic_id) {
            if ($prompt = $this->topic->prompt) {
                $text .= $prompt . '###';
            }
        }

        $messages = $this->messages;
        foreach ($messages as $message) {
            if ($message->getIsRequest()) {
                $text .= PHP_EOL . AiAssistantExtCommon::OPEN_AI_HUMAN_STOP . $message->message . PHP_EOL . PHP_EOL;
            } elseif ($message->getIsResponse()) {
                $text .= PHP_EOL . PHP_EOL . AiAssistantExtCommon::OPEN_AI_AI_STOP . $message->message;
            }
        }

        if ($append) {
            $text .= PHP_EOL . AiAssistantExtCommon::OPEN_AI_HUMAN_STOP . $append . PHP_EOL . PHP_EOL;
        }

        return $text;
    }

    /**
     * @param string $append
     * @return array
     */
    public function getMessagesArrayForAi(string $append = ''): array
    {
        $messagesArray = [];

        if ($this->use_summary && $this->summary) {
            $messagesArray[] = [
                'role'    => AiAssistantConversationMessage::OPEN_AI_ROLE_SYSTEM,
                'content' => $this->summary,
            ];

            // Get the last 4 messages (user request and AI response) and append it at the end of the summary
            $criteria = new CDbCriteria();
            $criteria->compare('conversation_id', $this->conversation_id);

            $criteria->order = 'message_id DESC';
            $criteria->limit = 4;

            $lastMessages = AiAssistantConversationMessage::model()->findAll($criteria);
            if ($lastMessages) {
                $lastMessages = array_reverse($lastMessages);
                foreach ($lastMessages as $message) {
                    $messagesArray[] = [
                        'role'    => $message->getOpenAiRole(),
                        'content' => $message->message,
                    ];
                }
            }

            if ($append) {
                $messagesArray[] = [
                    'role'    => AiAssistantConversationMessage::OPEN_AI_ROLE_USER,
                    'content' => $append,
                ];
            }

            return $messagesArray;
        }

        if ($this->topic_id) {
            if ($prompt = $this->topic->prompt) {
                $messagesArray[] = [
                    'role'    => AiAssistantConversationMessage::OPEN_AI_ROLE_SYSTEM,
                    'content' => $prompt,
                ];
            }
        }

        $messages = $this->messages;
        foreach ($messages as $message) {
            $messagesArray[] = [
                'role'    => $message->getOpenAiRole(),
                'content' => $message->message,
            ];
        }

        if ($append) {
            $messagesArray[] = [
                'role'    => AiAssistantConversationMessage::OPEN_AI_ROLE_USER,
                'content' => $append,
            ];
        }

        return $messagesArray;
    }

    /**
     * @param string $append
     * @return array|string
     */
    public function getConversationPrompt(string $append = '')
    {
        if ($this->open_ai_model === AiAssistantExtCommon::OPEN_AI_MODEL_DA_VINCI) {
            return $this->getMessagesText($append);
        }
        if (in_array(
            $this->open_ai_model,
            [AiAssistantExtCommon::OPEN_AI_MODEL_GPT_4, AiAssistantExtCommon::OPEN_AI_MODEL_GPT_3_5]
        )) {
            return $this->getMessagesArrayForAi($append);
        }
        return '';
    }

    /**
     * @tokensCount
     * @param string $append
     * @return int
     */
    public function countTokens(string $append = ''): int
    {
        /** @var array|AiAssistantConversationMessage[]|string $prompt */
        $prompt = $this->getConversationPrompt($append);

        if (is_array($prompt)) {
            $promptKey = sha1((string)count($prompt));
        } else {
            $promptKey = sha1($prompt);
        }
        $cacheKey = sha1(sprintf(
            'ai-assistant.conversation:%s.prompt:%s.tokens.count',
            (string)$this->conversation_id,
            $promptKey
        ));

        if (($count = cache()->get($cacheKey))) {
            return $count;
        }

        if (is_string($prompt)) {
            // @phpstan-ignore-next-line
            $count = count(gpt_encode($prompt));
            cache()->set($cacheKey, $count, 600);

            return $count;
        }

        if (!is_array($prompt)) {
            return 0;
        }

        $tokensCount = 0;
        foreach ($prompt as $message) {
            $messageContent = '';
            if ($message instanceof AiAssistantConversationMessage) {
                $messageContent = $message->message;
            }

            if (isset($message['content'])) {
                $messageContent = $message['content'];
            }

            if (empty($messageContent)) {
                continue;
            }

            // @phpstan-ignore-next-line
            $tokensArray = gpt_encode($messageContent);
            $tokensCount += count($tokensArray);
        }

        cache()->set($cacheKey, $tokensCount, 600);

        return $tokensCount;
    }

    /**
     * @return array
     */
    public function getOpenAiResponseAsMessage(): array
    {
        $success = false;

        $model                  = new AiAssistantConversationMessage();
        $model->type            = AiAssistantConversationMessage::TYPE_RESPONSE;
        $model->conversation_id = $this->conversation_id;

        try {
            // @tokensCount
            // Initial count to see if we are going to use the summary TODO - TBD to see if we enforce it from here
            // $this->use_summary = !empty($this->summary) && !$this->getHasEnoughTokensForResponse();

            // Now getting the prompt based on the above calculation
            $prompt = $this->getConversationPrompt();

            /** @var string $responseText */
            /** @var bool $responseSuccess */
            /** @var string $errorMessage */
            /** @var int $errorCode */
            [$responseText, $responseSuccess, $errorMessage, $errorCode] = $this->getApiResponse($prompt);

            if (!$responseText || !$responseSuccess) {
                throw new Exception($errorMessage, (int)$errorCode);
            }

            $model->message = $responseText;
            if (!$model->validate()) {
                throw new Exception(
                    t('ai_assistant_ext', 'Something went wrong. Could not save response from the AI'),
                    422
                );
            }

            if (!$model->save(false)) {
                throw new Exception(
                    t('ai_assistant_ext', 'Something went wrong. Could not save response from the AI'),
                    (int)$errorCode
                );
            }

            $success = true;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $errorCode    = (int)$e->getCode();
        }

        return [$model, $success, $errorMessage, (int)$errorCode];
    }

    /**
     * @tokensCount
     * This is not yet used, but might be useful in the future
     * TODO - See from where to call this, since it might take a while
     * @return bool
     */
    public function askAiToSummarize(): bool
    {
        $success       = false;
        $messages      = $this->messages;
        $messagesCount = count($messages);

        $cacheKey = sha1(sprintf(
            'ai-assistant.conversation:%s.messages-count:%s',
            (string)$this->conversation_id,
            (string)$messagesCount
        ));

        if ((bool)cache()->get($cacheKey)) {
            return false;
        }

        // TODO - This need TBD
        $mustSummarize = ($messagesCount && $messagesCount > 5 && $messagesCount % 5 === 0) || $this->countTokens() > 400;
        cache()->set($cacheKey, $mustSummarize);

        if (!$mustSummarize) {
            return false;
        }

        try {
            // Make sure we send all the messages, not only the summary
            $this->use_summary = false;
            $this->summary     = '';
            $prompt            = $this->getConversationPrompt('Please try to summarize this conversation as simple as possible');
            [$responseText, $responseSuccess, $errorMessage, $errorCode] = $this->getApiResponse($prompt);

            if (!$responseText || !$responseSuccess) {
                throw new Exception((string)$errorMessage, (int)$errorCode);
            }

            $this->summary     = (string)$responseText;
            $this->use_summary = true;

            if (!$this->save(false)) {
                throw new Exception(t(
                    'ai_assistant_ext',
                    'Something went wrong. Could not save the summary from the AI'
                ));
            }
            $success = true;
        } catch (Exception $e) {
        }

        return $success;
    }


    /**
     * @return \Tectalic\OpenAi\Client
     * @throws Exception
     */
    public function getOpenAiClient(): Tectalic\OpenAi\Client
    {
        /** @var \Tectalic\OpenAi\Client|null $client */
        static $client;
        if ($client) {
            return $client;
        }

        if (apps()->isAppName('backend')) {
            /** @var AiAssistantExtCommon $settings */
            $settings  = container()->get(AiAssistantExtCommon::class);
            $secretKey = $settings->getSecretAccessKey();
        } elseif (apps()->isAppName('customer')) {
            /** @var AiAssistantExtCustomer $settings */
            $settings  = container()->get(AiAssistantExtCustomer::class);
            $secretKey = $settings->getSecretAccessKey();
        }

        if (empty($secretKey)) {
            throw new Exception(t('ai_assistant_ext', 'Please set the secret key'));
        }

        return $client = Tectalic\OpenAi\Manager::build(
            new Symfony\Component\HttpClient\Psr18Client(),
            new Tectalic\OpenAi\Authentication($secretKey)
        );
    }

    /**
     * @param string|array $prompt
     * @return (string|int|bool)[]
     */
    public function getApiResponse($prompt): array
    {
        $success      = false;
        $errorMessage = '';
        $errorCode    = 500;
        $responseText = '';

        try {
            $maxTokens = $this->getOpenAiMaxTokensForResponse();
            if ($maxTokens <= 0) {
                throw new Exception(t('ai_assistant_ext', 'Conversation max tokens limit is reached.'));
            }
            $this->getOpenAiClient();
            $response = null;
            if ($this->open_ai_model === AiAssistantExtCommon::OPEN_AI_MODEL_DA_VINCI) {
                $handler = new \Tectalic\OpenAi\Handlers\Completions();

                try {
                    /** @var Tectalic\OpenAi\Models\Completions\CreateResponse $response */
                    $response = $handler->create(
                        new Tectalic\OpenAi\Models\Completions\CreateRequest([
                            'model'             => $this->open_ai_model,
                            'prompt'            => $prompt,
                            'max_tokens'        => $this->getOpenAiMaxTokensForResponse(),
                            'temperature'       => $this->open_ai_temperature,
                            'frequency_penalty' => $this->open_ai_frequency_penalty,
                            'presence_penalty'  => $this->open_ai_presence_penalty,
                            'stop'              => implode(PHP_EOL, $this->open_ai_stop),
                        ])
                    )->toModel();
                } catch (Tectalic\OpenAi\ClientException $e) {
                    // Error response received. Retrieve the HTTP response code and response body.
                    $responseError = $handler->toArray();
                    $errorArray = isset($responseError['error']) ? (array)$responseError['error'] : [];
                    if (!empty($errorArray)) {
                        $errorMessage = isset($errorArray['message']) ? (string)$errorArray['message'] : '';
                    }
                    $errorCode = $handler->getResponse()->getStatusCode();
                }
            } elseif (in_array(
                $this->open_ai_model,
                [AiAssistantExtCommon::OPEN_AI_MODEL_GPT_4, AiAssistantExtCommon::OPEN_AI_MODEL_GPT_3_5]
            )) {
                $handler = new \Tectalic\OpenAi\Handlers\ChatCompletions();

                try {
                    /** @var Tectalic\OpenAi\Models\ChatCompletions\CreateResponse $response */
                    $response = $handler->create(
                        new \Tectalic\OpenAi\Models\ChatCompletions\CreateRequest([
                            'messages'          => $prompt,
                            'model'             => $this->open_ai_model,
                            'max_tokens'        => $this->getOpenAiMaxTokensForResponse(),
                            'temperature'       => $this->open_ai_temperature,
                            'frequency_penalty' => $this->open_ai_frequency_penalty,
                            'presence_penalty'  => $this->open_ai_presence_penalty,
                        ])
                    )->toModel();
                } catch (Tectalic\OpenAi\ClientException $e) {
                    // Error response received. Retrieve the HTTP response code and response body.
                    $responseError = $handler->toArray();
                    $errorArray = isset($responseError['error']) ? (array)$responseError['error'] : [];
                    if (!empty($errorArray)) {
                        $errorMessage = isset($errorArray['message']) ? (string)$errorArray['message'] : '';
                    }
                    $errorCode = $handler->getResponse()->getStatusCode();
                }
            }

            if (!empty($errorMessage)) {
                throw new Exception(t(
                    'ai_assistant_ext',
                    'Something went wrong. {error}',
                    ['{error}' => $errorMessage]
                ), $errorCode);
            }

            if (empty($response)) {
                throw new Exception(
                    t('ai_assistant_ext', 'Something went wrong. Empty response from the AI'),
                    $errorCode
                );
            }

            $choices = $response->choices;
            if (empty($choices)) {
                throw new Exception(t('ai_assistant_ext', 'Something went wrong. No response from the AI'), $errorCode);
            }

            $choice = reset($choices);
            if (empty($choice->message->content)) {
                throw new Exception(
                    t('ai_assistant_ext', 'Something went wrong. Empty response text from the AI'),
                    $errorCode
                );
            }

            $responseText = $choice->message->content;

            $success = true;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $errorCode    = (int)$e->getCode();

            // TODO - If the response error is because of the reached max tokens, maybe we should retry with a new prompt
        }
        $errorMessage = !empty($errorMessage) ? $errorMessage : t(
            'ai_assistant_ext',
            'Something went wrong. No response from the AI'
        );
        return [$responseText, $success, $errorMessage, $errorCode];
    }

    /**
     * @tokensCount
     * @return int
     */
    public function getOpenAiMaxTokensForResponse(): int
    {
        if (!$this->getMustCountTokens()) {
            return $this->open_ai_max_tokens;
        }

        $tokensCount                = $this->countTokens();
        $remainingTokensForResponse = $this->getOpenAiModelTokensLimit() - $tokensCount;
        // TODO - Allow with 5% less token maybe?
        return (int)(min($this->open_ai_max_tokens, $remainingTokensForResponse) * 1);
    }

    /**
     * @tokensCount
     * @return int
     */
    public function getOpenAiModelTokensLimit(): int
    {
        return !isset(AiAssistantExtCommon::getOpenAiModelsTokensLimit()[$this->open_ai_model]) ? AiAssistantExtCommon::OPEN_AI_MODEL_GPT_3_5_MAX_TOKENS_LIMIT : AiAssistantExtCommon::getOpenAiModelsTokensLimit()[$this->open_ai_model];
    }

    /**
     * @tokensCount
     * @return int
     */
    public function countTokensWithoutSummary(): int
    {
        $useSummary        = $this->use_summary;
        $this->use_summary = false;

        $count             = $this->countTokens();
        $this->use_summary = $useSummary;

        return $count;
    }

    /**
     * @tokensCount
     * @return int
     */
    public function countTokensWithSummary(): int
    {
        $useSummary        = $this->use_summary;
        $this->use_summary = true;

        $count             = $this->countTokens();
        $this->use_summary = $useSummary;

        return $count;
    }

    /**
     * @tokensCount
     * @return int
     */
    public function getAvailableTokensCountForResponse(): int
    {
        return $this->getOpenAiModelTokensLimit() - $this->countTokens();
    }

    /**
     * @tokensCount
     * @return bool
     */
    public function getHasEnoughTokensForResponse(): bool
    {
        return ($this->countTokens() + $this->open_ai_max_tokens) < $this->getOpenAiModelTokensLimit();
    }

    /**
     * @tokensCount
     * @return bool
     */
    public function getIsCloseToLimit(): bool
    {
        return $this->getAvailableTokensCountForResponse() - $this->getOpenAiMaxTokensForResponse() < 20;
    }

    /**
     * @tokensCount
     * @return bool
     */
    public function getMustCountTokens(): bool
    {
        /** @var AiAssistantExtCommon $commonSettings */
        $commonSettings = container()->get(AiAssistantExtCommon::class);
        return (bool)$commonSettings->must_count_tokens;
    }

    /**
     * @return string[]
     */
    public function getAttributesForApi(): array
    {
        $this->refresh();

        $allowedAttributes = [
            'conversation_id',
            'topic_id',
            'customer_id',
            'user_id',
            'name',
            'date_added',
            'last_updated',
        ];

        // @tokensCount
        $tokensRelatedAttributes = [];
        if ($this->getMustCountTokens()) {
            $tokensRelatedAttributes = [
                'tokens'                        => $this->countTokensWithoutSummary(),
                'tokens_with_summary'           => $this->countTokensWithSummary(),
                'tokens_available_for_response' => $this->getAvailableTokensCountForResponse(),
                'open_ai_max_tokens_response'   => $this->getOpenAiMaxTokensForResponse(),
                'is_close_to_limit'             => $this->getIsCloseToLimit(),
                'summary'                       => $this->summary,
                'use_summary'                   => $this->use_summary,
            ];
        }

        $attributes = $this->getAttributes($allowedAttributes);

        if (!empty($tokensRelatedAttributes)) {
            $attributes = array_merge($attributes, $tokensRelatedAttributes);
        }
        return $attributes;
    }

    /**
     * @return bool
     * @throws CException
     */
    protected function beforeSave()
    {
        $this->modelMetaData->getModelMetaData()->add('open_ai_model', (string)$this->open_ai_model);
        $this->modelMetaData->getModelMetaData()->add('open_ai_max_tokens', (int)$this->open_ai_max_tokens);
        $this->modelMetaData->getModelMetaData()->add(
            'open_ai_frequency_penalty',
            (float)$this->open_ai_frequency_penalty
        );
        $this->modelMetaData->getModelMetaData()->add('open_ai_stop', (array)$this->open_ai_stop);
        $this->modelMetaData->getModelMetaData()->add(
            'open_ai_presence_penalty',
            (float)$this->open_ai_presence_penalty
        );
        $this->modelMetaData->getModelMetaData()->add('open_ai_temperature', (float)$this->open_ai_temperature);
        $this->modelMetaData->getModelMetaData()->add('summary', (string)$this->summary);
        $this->modelMetaData->getModelMetaData()->add('use_summary', (bool)$this->use_summary);


        return parent::beforeSave();
    }

    /**
     * @return void
     * @throws CException
     */
    protected function afterFind()
    {
        $this->open_ai_model             = (string)$this->modelMetaData->getModelMetaData()->itemAt('open_ai_model');
        $this->open_ai_max_tokens        = (int)$this->modelMetaData->getModelMetaData()->itemAt('open_ai_max_tokens');
        $this->open_ai_frequency_penalty = (float)$this->modelMetaData->getModelMetaData()->itemAt('open_ai_frequency_penalty');
        $this->open_ai_stop              = (array)$this->modelMetaData->getModelMetaData()->itemAt('open_ai_stop');
        $this->open_ai_presence_penalty  = (float)$this->modelMetaData->getModelMetaData()->itemAt('open_ai_presence_penalty');
        $this->open_ai_temperature       = (float)$this->modelMetaData->getModelMetaData()->itemAt('open_ai_temperature');
        $this->summary                   = (string)$this->modelMetaData->getModelMetaData()->itemAt('summary');
        $this->use_summary               = (bool)$this->modelMetaData->getModelMetaData()->itemAt('use_summary');

        parent::afterFind();
    }
}
