<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListSubscriberMetaData
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.3
 */

/**
 * This is the model class for table "list_subscriber_meta_data".
 *
 * The followings are the available columns in table 'list_subscriber_meta_data':
 * @property integer $subscriber_id
 * @property string $key
 * @property mixed $value
 * @property integer $is_serialized
 * @property integer $is_private
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListSubscriber $subscriber
 */
class ListSubscriberMetaData extends ActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{list_subscriber_meta_data}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'subscriber' => [self::BELONGS_TO, 'ListSubscriber', 'subscriber_id'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     *
     * @param string $className active record class name.
     *
     * @return ListSubscriberMetaData the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var ListSubscriberMetaData $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        $this->is_serialized = 0;
        $this->is_private = 1;
        if ($this->value !== null && !is_string($this->value)) {
            $this->value = SerializeHelper::serialize($this->value);
            $this->is_serialized = 1;
        }
        return parent::beforeSave();
    }

    /**
     * @return void
     */
    protected function afterSave()
    {
        if ($this->is_serialized) {
            $this->value = SerializeHelper::unserialize((string)$this->value);
        }
        parent::afterSave();
    }

    /**
     * @return void
     */
    protected function afterFind()
    {
        if ($this->is_serialized) {
            $this->value = SerializeHelper::unserialize((string)$this->value);
        }
        parent::afterFind();
    }
}
