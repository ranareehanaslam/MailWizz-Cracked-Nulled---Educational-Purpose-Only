<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AiAssistantExtCustomer
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 */

class AiAssistantExtCustomer extends AiAssistantExtCommon
{
    /**
     * @inheritDoc
     */
    public function __construct($scenario='')
    {
        if (!app()->hasComponent('customer') || !customer()->getId()) {
            throw new CException('This class has to be instantiated only for logged in customers!');
        }

        parent::__construct($scenario);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryName(): string
    {
        return 'customer.id' . intval(customer()->getId()) . '.';
    }

    /**
     * @inheritDoc
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'secret_access_key'         => $this->t('The access secret key for OpenAI API'),
            'open_ai_max_tokens'        => $this->t('The maximum number of tokens to generate in the completion. The token count of your prompt plus max_tokens cannot exceed the models context length. Most models have a context length of 2048 tokens (except for the newest models, which support 4096).'),
            'open_ai_temperature'       => $this->t('What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic. We generally recommend altering this or top_p but not both.'),
            'open_ai_frequency_penalty' => $this->t('Number between -2.0 and 2.0. Positive values penalize new tokens based on their existing frequency in the text so far, decreasing the models likelihood to repeat the same line verbatim. See more information about frequency and presence penalties.'),
            'open_ai_presence_penalty'  => $this->t('Number between -2.0 and 2.0. Positive values penalize new tokens based on whether they appear in the text so far, increasing the models likelihood to talk about new topics. See more information about frequency and presence penalties.'),
        ];
        return CMap::mergeArray($texts, []);
    }

    /**
     * @return string
     */
    public function getSecretAccessKey(): string
    {
        /** @var AiAssistantExtCommon $commonSettingsModel */
        $commonSettingsModel = container()->get(AiAssistantExtCommon::class);

        if (!empty($this->secret_access_key) && $commonSettingsModel->getCanCustomersAddAccount()) {
            return $this->secret_access_key;
        }

        if (!$commonSettingsModel->getCanCustomersAddAccount() && $commonSettingsModel->getCustomersCanUseSystem()) {
            return $commonSettingsModel->getSecretAccessKey();
        }

        if (empty($this->secret_access_key) && $commonSettingsModel->getCustomersCanUseSystem()) {
            return $commonSettingsModel->getSecretAccessKey();
        }

        return $this->secret_access_key;
    }

    /**
     * @return int
     */
    public function getOpenAiMaxTokens(): int
    {
        /** @var AiAssistantExtCommon $commonSettingsModel */
        $commonSettingsModel = container()->get(AiAssistantExtCommon::class);

        return empty($this->open_ai_max_tokens) ? $commonSettingsModel->getOpenAiMaxTokens() : (int)$this->open_ai_max_tokens;
    }

    /**
     * @return float
     */
    public function getOpenAiTemperature(): float
    {
        /** @var AiAssistantExtCommon $commonSettingsModel */
        $commonSettingsModel = container()->get(AiAssistantExtCommon::class);

        return empty($this->open_ai_temperature) ? $commonSettingsModel->getOpenAiTemperature() : (float)$this->open_ai_temperature;
    }

    /**
     * @return float
     */
    public function getOpenAiFrequencyPenalty(): float
    {
        /** @var AiAssistantExtCommon $commonSettingsModel */
        $commonSettingsModel = container()->get(AiAssistantExtCommon::class);

        return empty($this->open_ai_frequency_penalty) ? $commonSettingsModel->getOpenAiFrequencyPenalty() : (float)$this->open_ai_frequency_penalty;
    }

    /**
     * @return float
     */
    public function getOpenAiPresencePenalty(): float
    {
        /** @var AiAssistantExtCommon $commonSettingsModel */
        $commonSettingsModel = container()->get(AiAssistantExtCommon::class);

        return empty($this->open_ai_presence_penalty) ? $commonSettingsModel->getOpenAiPresencePenalty() : (float)$this->open_ai_presence_penalty;
    }

    /**
     * @return array|string[]
     */
    public function getOpenAiStop(): array
    {
        /** @var AiAssistantExtCommon $commonSettingsModel */
        $commonSettingsModel = container()->get(AiAssistantExtCommon::class);

        return empty($this->open_ai_stop) ? $commonSettingsModel->getOpenAiStop() : $this->open_ai_stop;
    }
}
