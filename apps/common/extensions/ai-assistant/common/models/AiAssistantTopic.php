<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AiAssistantTopic
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

/**
 * This is the model class for table "ai_assistant_topic".
 *
 * The followings are the available columns in table 'ai_assistant_topic':
 * @property integer $topic_id
 * @property string $subject
 * @property string $prompt
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property AiAssistantConversation[] $conversations
 */
class AiAssistantTopic extends ActiveRecord
{
    /**
     * Use the needed traits
     */
    use AddShortcutMethodsFromCurrentExtensionTrait;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{ai_assistant_topic}}';
    }

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['subject, prompt', 'required'],
            ['subject', 'length', 'max' => 100],
            ['prompt', 'length', 'max' => 1000],

            // The following rule is used by search().
            ['subject', 'safe', 'on' => 'search'],
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
            'conversations' => [self::HAS_MANY, AiAssistantConversation::class, 'topic_id'],
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
            'topic_id' => $this->t('Topic'),
            'subject'  => $this->t('Subject'),
            'prompt'   => $this->t('Prompt'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'topic_id' => $this->t('Topic'),
            'subject'  => $this->t('The subject will appear when starting a new conversation.'),
            'prompt'   => $this->t('This is the prompt that will always be sent as context for the conversation.'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
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
        $criteria = new CDbCriteria();
        $criteria->compare('subject', $this->subject, true);

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'       => [
                'defaultOrder' => [
                    't.topic_id' => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return AiAssistantTopic the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var AiAssistantTopic $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return string[]
     */
    public function getAttributesForApi(): array
    {
        $this->refresh();
        $allowedAttributes = ['topic_id', 'subject', 'prompt', 'date_added', 'last_updated'];
        return $this->getAttributes($allowedAttributes);
    }
}
