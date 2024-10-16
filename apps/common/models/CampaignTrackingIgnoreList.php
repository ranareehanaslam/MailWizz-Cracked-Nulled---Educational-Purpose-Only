<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CampaignTrackingIgnoreList
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.9
 */

/**
 * This is the model class for table "campaign_tracking_ignore_list".
 *
 * The followings are the available columns in table 'campaign_tracking_ignore_list':
 * @property string $id
 * @property string $action
 * @property string $status
 * @property string $ip_address
 * @property string $reason
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 */
class CampaignTrackingIgnoreList extends ActiveRecord
{
    const ACTION_CLICK = 'click';
    const ACTION_OPEN = 'open';

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{campaign_tracking_ignore_list}}';
    }

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['ip_address', 'required'],
            ['ip_address', 'unique'],
            ['action, status, ip_address, reason, date_added, last_updated', 'safe', 'on' => 'search'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'action'     => t('campaigns', 'Action'),
            'ip_address' => t('campaigns', 'Ip address'),
            'reason'     => t('campaigns', 'Reason'),
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
        $criteria = new CDbCriteria();

        $criteria->compare('t.action', $this->action);
        $criteria->compare('t.status', $this->status);
        $criteria->compare('t.ip_address', $this->ip_address, true);
        $criteria->compare('t.reason', $this->reason, true);

        $criteria->order = 't.id DESC';

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort' => [
                'defaultOrder' => [
                    't.id' => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CampaignTrackingIgnoreList the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var CampaignTrackingIgnoreList $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return array
     */
    public function getActionsList(): array
    {
        return [
            self::ACTION_CLICK => t('campaigns', 'Click'),
            self::ACTION_OPEN  => t('campaigns', 'Open'),
        ];
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function toggleStatus(): bool
    {
        return $this->saveStatus($this->getStatusIs(self::STATUS_ACTIVE) ? self::STATUS_INACTIVE : self::STATUS_ACTIVE);
    }

    /**
     * @return string
     */
    public function getDisplayIpAddress(): string
    {
        if (is_cli()) {
            return html_encode($this->ip_address);
        }

        $requestIP = request()->getUserHostAddress();
        if ($requestIP !== $this->ip_address) {
            return html_encode($this->ip_address);
        }

        return CHtml::tag('span', [
            'class' => 'btn btn-flat btn-sm btn-info',
            'title' => t('app', 'Your IP Address'),
        ], html_encode($this->ip_address));
    }
}
