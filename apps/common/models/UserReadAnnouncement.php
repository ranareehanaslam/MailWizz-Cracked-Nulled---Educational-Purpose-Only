<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * UserReadAnnouncement
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.3
 */

/**
 * This is the model class for table "user_read_announcement".
 *
 * The followings are the available columns in table 'user_read_announcement':
 * @property integer $user_id
 * @property integer $announcement_id
 * @property string|CDbExpression $date_added
 *
 * The followings are the available model relations:
 * @property User $user
 * @property Announcement $announcement
 */
class UserReadAnnouncement extends ActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{user_read_announcement}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['announcement_id, user_id', 'required'],
            ['announcement_id, user_id', 'numerical', 'integerOnly'=>true],
            ['announcement_id', 'exist', 'className' => Announcement::class],
            ['user_id', 'exist', 'className' => User::class],

            // The following rule is used by search().
            ['announcement_id', 'safe', 'on'=>'search'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'user'         => [self::BELONGS_TO, User::class, 'user_id'],
            'announcement' => [self::BELONGS_TO, Announcement::class, 'announcement_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'user_id'         => t('announcements', 'User'),
            'announcement_id' => t('announcements', 'Announcement'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'user_id'         => t('announcements', 'User'),
            'announcement_id' => t('announcements', 'Announcement'),
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
     * @throws CException
     */
    public function search()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('announcement_id', $this->announcement_id);

        return new CActiveDataProvider(get_class($this), [
            'criteria'      => $criteria,
            'pagination'    => [
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ],
            'sort'  => [
                'defaultOrder'  => [
                    'announcement_id'   => CSort::SORT_ASC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return UserReadAnnouncement the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var UserReadAnnouncement $model */
        $model = parent::model($className);

        return $model;
    }
}
