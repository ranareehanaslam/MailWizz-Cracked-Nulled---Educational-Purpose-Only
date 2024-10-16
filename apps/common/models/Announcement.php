<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Announcement
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.3
 */

/**
 * This is the model class for table "announcement".
 *
 * The followings are the available columns in table 'announcement':
 * @property integer $announcement_id
 * @property string $remote_id
 * @property string $title
 * @property string $message
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 *
 * @property UserReadAnnouncement[] $usersRead
 */
class Announcement extends ActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{announcement}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['remote_id, title, message', 'required'],
            ['remote_id', 'length', 'is' => 36],
            ['title', 'length', 'max' => 255],
            ['message', 'length', 'min' => 5],

            // The following rule is used by search().
            ['title, message', 'safe', 'on' => 'search'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        return CMap::mergeArray([
            'usersRead' => [self::HAS_MANY, UserReadAnnouncement::class, 'announcement_id'],
        ], parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'announcement_id' => t('announcements', 'Announcement'),
            'remote_id'       => t('announcements', 'Remote id'),
            'title'           => t('announcements', 'Title'),
            'message'         => t('announcements', 'Message'),
        ];
        return CMap::mergeArray($labels, parent::attributeLabels());
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
        $criteria       = new CDbCriteria();
        $criteria->with = [];

        $criteria->compare('t.title', $this->title, true);
        $criteria->compare('t.message', $this->message, true);

        $criteria->order = 't.announcement_id DESC';

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'       => [
                'defaultOrder' => [
                    't.announcement_id' => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Announcement the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var Announcement $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @param string $remoteId
     *
     * @return Announcement|null
     */
    public function findByUid(string $remoteId): ?self
    {
        return self::model()->findByAttributes([
            'remote_id' => $remoteId,
        ]);
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function markAsReadForUser(int $userId): bool
    {
        $wasRead = UserReadAnnouncement::model()->findByAttributes([
            'user_id'         => $userId,
            'announcement_id' => $this->announcement_id,
        ]);

        if (!empty($wasRead)) {
            return true;
        }

        $model                  = new UserReadAnnouncement();
        $model->user_id         = $userId;
        $model->announcement_id = $this->announcement_id;

        return $model->save();
    }
}
