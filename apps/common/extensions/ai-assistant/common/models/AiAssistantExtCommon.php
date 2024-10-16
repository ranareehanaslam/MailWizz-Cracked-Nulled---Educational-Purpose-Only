<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AiAssistantExtCommon
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
class AiAssistantExtCommon extends ExtensionModel
{
    const OPEN_AI_HUMAN_STOP = 'Human: ';
    const OPEN_AI_AI_STOP = 'AI: ';

    /**
     * Completion OpenAI model
     */
    const OPEN_AI_MODEL_DA_VINCI = 'text-davinci-003';

    /**
     * Chat GPT 3.5 OpenAI model
     */
    const OPEN_AI_MODEL_GPT_3_5 = 'gpt-3.5-turbo';

    /**
     * Chat GPT 4 OpenAI model
     */
    const OPEN_AI_MODEL_GPT_4 = 'gpt-4';

    /**
     * Chat GPT 3.5 OpenAI model max tokens limit
     */
    const OPEN_AI_MODEL_GPT_3_5_MAX_TOKENS_LIMIT = 4096;

    /**
     * Chat GPT 4 OpenAI model max tokens limit
     */
    const OPEN_AI_MODEL_GPT_4_MAX_TOKENS_LIMIT = 8000;

    /**
     * @var string
     */
    public $enabled = self::TEXT_NO;

    /**
     * @var string
     */
    public $secret_access_key = '';

    /**
     * @var string
     */
    public $customers_enabled = self::TEXT_YES;

    /**
     * @var string
     */
    public $customers_use_system = self::TEXT_YES;

    /**
     * @var string
     */
    public $customers_add_account = self::TEXT_NO;

    /**
     * @var array
     */
    public $groups = [];

    /**
     * @var string
     */
    public $open_ai_model = self::OPEN_AI_MODEL_GPT_3_5;

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
    public $open_ai_stop = [self::OPEN_AI_HUMAN_STOP, self::OPEN_AI_AI_STOP];

    /**
     * @tokensCount - Set this to true to start using tokens counting logic
     * @var bool
     */
    public $must_count_tokens = false;

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['secret_access_key', 'safe'],
            ['open_ai_model', 'in', 'range' => array_keys($this->getOpenAiModelsList())],
            [
                'enabled, customers_enabled, customers_use_system, customers_add_account',
                'in',
                'range' => array_keys($this->getYesNoOptions()),
            ],
            ['open_ai_max_tokens', 'numerical', 'min' => 50, 'max' => 2000],
            ['open_ai_temperature', 'numerical', 'min' => 0, 'max' => 2],
            ['open_ai_frequency_penalty', 'numerical', 'min' => -2.0, 'max' => 2.0],
            ['open_ai_presence_penalty', 'numerical', 'min' => -2.0, 'max' => 2.0],
            ['groups', '_handleGroups'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeLabels()
    {
        $labels = [
            'enabled'                   => t('app', 'Enabled'),
            'customers_enabled'         => $this->t('Enabled for customers'),
            'customers_use_system'      => $this->t('Customers use system OpenAI account'),
            'customers_add_account'     => $this->t('Customers add own OpenAI account'),
            'groups'                    => $this->t('Provide AI Assistant only for these customer groups'),
            'secret_access_key'         => $this->t('Secret key'),
            'open_ai_model'             => $this->t('Model'),
            'open_ai_max_tokens'        => $this->t('Max tokens'),
            'open_ai_temperature'       => $this->t('Temperature'),
            'open_ai_frequency_penalty' => $this->t('Frequency penalty'),
            'open_ai_presence_penalty'  => $this->t('Presence penalty'),
            'open_ai_stop'              => $this->t('Stop'),
        ];
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'secret_access_key' => '',
        ];
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @inheritDoc
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'enabled'                   => t('app', 'Whether the feature is enabled'),
            'secret_access_key'         => $this->t('The access secret key for OpenAI API'),
            'customers_enabled'         => $this->t('Whether the customers can use this feature'),
            'customers_use_system'      => $this->t('Whether customer can use the system OpenAI account if they don\'t have their own account'),
            'customers_add_account'     => $this->t('Whether customer can add their own OpenAI account'),
            'open_ai_max_tokens'        => $this->t('The maximum number of tokens to generate in the completion. The token count of your prompt plus max_tokens cannot exceed the models context length. Most models have a context length of 2048 tokens (except for the newest models, which support 4096). We limited this to 2000 to make sure there are enough tokens for the response.'),
            'open_ai_temperature'       => $this->t('What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic. We generally recommend altering this or top_p but not both.'),
            'open_ai_frequency_penalty' => $this->t('Number between -2.0 and 2.0. Positive values penalize new tokens based on their existing frequency in the text so far, decreasing the models likelihood to repeat the same line verbatim. See more information about frequency and presence penalties.'),
            'open_ai_presence_penalty'  => $this->t('Number between -2.0 and 2.0. Positive values penalize new tokens based on whether they appear in the text so far, increasing the models likelihood to talk about new topics. See more information about frequency and presence penalties.'),
            'open_ai_stop'              => $this->t('Up to 4 sequences where the API will stop generating further tokens. The returned text will not contain the stop sequence.'),
        ];
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @inheritDoc
     */
    public function getCategoryName(): string
    {
        return '';
    }

    /**
     * @return array
     */
    public function getCustomerGroupsOptions(): array
    {
        static $options;
        if ($options !== null) {
            return $options;
        }
        $options = [];
        $groups  = CustomerGroup::model()->findAll();
        foreach ($groups as $group) {
            $options[$group->group_id] = $group->name;
        }
        return $options;
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function _handleGroups(string $attribute, array $params = []): void
    {
        if (empty($this->$attribute) || !is_array($this->$attribute)) {
            $this->$attribute = [];
        }
        $this->$attribute = array_map('intval', $this->$attribute);
        $this->$attribute = array_filter($this->$attribute);
    }

    /**
     * @return string
     */
    public function getSecretAccessKey(): string
    {
        return (string)$this->secret_access_key;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->enabled === self::TEXT_YES;
    }

    /**
     * @return bool
     */
    public function getIsCustomersEnabled(): bool
    {
        return $this->customers_enabled === self::TEXT_YES;
    }

    /**
     * @return bool
     */
    public function getCustomersCanUseSystem(): bool
    {
        return $this->customers_use_system === self::TEXT_YES;
    }

    /**
     * @return bool
     */
    public function getCanCustomersAddAccount(): bool
    {
        return $this->customers_add_account === self::TEXT_YES;
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function checkCustomerAccess(Customer $customer): bool
    {
        if (!$this->getIsEnabled() || !$this->getIsCustomersEnabled()) {
            return false;
        }

        /** @var int $groupsCount */
        $groupsCount = !empty($this->groups) && is_array($this->groups) ? count($this->groups) : 0;
        if ($groupsCount && !in_array($customer->group_id, $this->groups)) {
            return false;
        }

        // subaccounts don't have access here TODO - TBD
        if (is_subaccount()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getOpenAiModel(): string
    {
        return $this->open_ai_model;
    }

    /**
     * @return array
     */
    public function getOpenAiModelsList(): array
    {
        return [
            self::OPEN_AI_MODEL_GPT_3_5  => $this->t('Chat GPT 3.5 turbo'),
            self::OPEN_AI_MODEL_GPT_4    => $this->t('Chat GPT 4'),
            self::OPEN_AI_MODEL_DA_VINCI => $this->t('Text completion DaVinci 003'),
        ];
    }

    /**
     * @return int
     */
    public function getOpenAiMaxTokens(): int
    {
        return (int)$this->open_ai_max_tokens;
    }

    /**
     * @return float
     */
    public function getOpenAiTemperature(): float
    {
        return (float)$this->open_ai_temperature;
    }

    /**
     * @return float
     */
    public function getOpenAiFrequencyPenalty(): float
    {
        return (float)$this->open_ai_frequency_penalty;
    }

    /**
     * @return float
     */
    public function getOpenAiPresencePenalty(): float
    {
        return (float)$this->open_ai_presence_penalty;
    }

    /**
     * @return array|string[]
     */
    public function getOpenAiStop(): array
    {
        return $this->open_ai_stop;
    }

    /**
     * @return int[]
     */
    public static function getOpenAiModelsTokensLimit(): array
    {
        return [
            self::OPEN_AI_MODEL_GPT_4    => self::OPEN_AI_MODEL_GPT_4_MAX_TOKENS_LIMIT,
            self::OPEN_AI_MODEL_GPT_3_5  => self::OPEN_AI_MODEL_GPT_3_5_MAX_TOKENS_LIMIT,
            self::OPEN_AI_MODEL_DA_VINCI => self::OPEN_AI_MODEL_GPT_3_5_MAX_TOKENS_LIMIT,
        ];
    }

    /**
     * @return array
     * @throws CException
     */
    public function getAttributesForApi(): array
    {
        $this->refresh();
        return [
            'secret_access_key'         => $this->secret_access_key,
            'open_ai_max_tokens'        => $this->getOpenAiMaxTokens(),
            'open_ai_temperature'       => $this->getOpenAiTemperature(),
            'open_ai_frequency_penalty' => $this->getOpenAiFrequencyPenalty(),
            'open_ai_presence_penalty'  => $this->getOpenAiPresencePenalty(),
            'attributeHelpTexts'        => $this->attributeHelpTexts(),
        ];
    }
}
