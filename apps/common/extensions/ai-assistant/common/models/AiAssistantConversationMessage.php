<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AiAssistantConversationMessage
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

/**
 * This is the model class for table "ai_assistant_conversation_message".
 *
 * The followings are the available columns in table 'ai_assistant_conversation_message':
 * @property integer $message_id
 * @property integer $conversation_id
 * @property string $type
 * @property string $message
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property AiAssistantConversation $conversation
 */
class AiAssistantConversationMessage extends ActiveRecord
{
    /**
     * Use the needed traits
     */
    use AddShortcutMethodsFromCurrentExtensionTrait;

    const TYPE_REQUEST = 'request';
    const TYPE_RESPONSE = 'response';

    const OPEN_AI_ROLE_SYSTEM = 'system';
    const OPEN_AI_ROLE_USER = 'user';
    const OPEN_AI_ROLE_ASSISTANT = 'assistant';

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{ai_assistant_conversation_message}}';
    }

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['message', 'required'],
            ['type', 'in', 'range' => array_keys($this->getTypesList())],
            ['type', 'default', 'value' => self::TYPE_REQUEST],
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
            'conversation' => [self::BELONGS_TO, AiAssistantConversation::class, 'conversation_id'],
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
            'message_id'      => $this->t('Message'),
            'conversation_id' => $this->t('Conversation'),
            'message'         => $this->t('Message'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return AiAssistantConversationMessage the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var AiAssistantConversationMessage $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return array
     */
    public function getTypesList(): array
    {
        return [
            self::TYPE_REQUEST  => $this->t('Request'),
            self::TYPE_RESPONSE => $this->t('Response'),
        ];
    }

    /**
     * @return bool
     */
    public function getIsRequest(): bool
    {
        return $this->type === self::TYPE_REQUEST;
    }

    /**
     * @return bool
     */
    public function getIsResponse(): bool
    {
        return $this->type === self::TYPE_RESPONSE;
    }

    /**
     * @return string
     */
    public function getOpenAiRole(): string
    {
        return $this->getIsRequest() ? self::OPEN_AI_ROLE_USER : self::OPEN_AI_ROLE_ASSISTANT;
    }

    /**
     * @return string[]
     */
    public function getAttributesForApi(): array
    {
        $this->refresh();
        $allowedAttributes = ['message_id', 'conversation_id', 'type', 'date_added', 'last_updated'];
        return array_merge($this->getAttributes($allowedAttributes), [
            'message' => $this->getParsedMessage(),
        ]);
    }

    /**
     * The message contains only Markdown syntax
     *
     * @return string
     */
    public function getParsedMessage(): string
    {
        return (string) $this->message;
    }
}
