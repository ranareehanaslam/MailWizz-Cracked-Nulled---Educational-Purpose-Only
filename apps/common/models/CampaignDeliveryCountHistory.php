<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CampaignDeliveryCountHistory
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.2
 */

/**
 * This is the model class for table "campaign_delivery_count_history".
 *
 * The followings are the available columns in table 'campaign_delivery_count_history':
 * @property integer|null $id
 * @property integer $customer_id
 * @property integer $campaign_id
 *
 * @property integer $total
 * @property integer $success_total
 * @property integer $error_total
 * @property integer $giveup_total
 * @property integer $blacklisted_total
 * @property integer $dp_reject_total
 *
 * @property integer $hard_bounce_total
 * @property integer $soft_bounce_total
 * @property integer $internal_bounce_total
 *
 * @property integer $complaint_total
 *
 * @property integer $success_hourly
 * @property integer $error_hourly
 * @property integer $giveup_hourly
 * @property integer $blacklisted_hourly
 * @property integer $dp_reject_hourly
 *
 * @property integer $hard_bounce_hourly
 * @property integer $soft_bounce_hourly
 * @property integer $internal_bounce_hourly
 *
 * @property integer $complaint_hourly
 *
 * @property string|CDbExpression $date_added
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property Customer $customer
 */
class CampaignDeliveryCountHistory extends ActiveRecord
{
    /**
     * @var int
     */
    public $successSum = 0;

    /**
     * @var int
     */
    public $errorSum = 0;

    /**
     * @var int
     */
    public $giveupSum = 0;

    /**
     * @var int
     */
    public $blacklistedSum = 0;

    /**
     * @var int
     */
    public $dpRejectSum = 0;

    /**
     * @var int
     */
    public $hardBounceSum = 0;

    /**
     * @var int
     */
    public $softBounceSum = 0;

    /**
     * @var int
     */
    public $internalBounceSum = 0;

    /**
     * @var int
     */
    public $complaintSum = 0;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{campaign_delivery_count_history}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'customer' => [self::BELONGS_TO, Customer::class, 'customer_id'],
            'campaign' => [self::BELONGS_TO, Campaign::class, 'campaign_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'customer_id'            => $this->t('Customer'),
            'campaign_id'            => $this->t('Campaign'),

            'total'                  => $this->t('Total'),

            'success_total'          => $this->t('Success total'),
            'error_total'            => $this->t('Error total'),
            'giveup_total'           => $this->t('Giveup total'),
            'blacklisted_total'      => $this->t('Blacklisted total'),
            'dp_reject_total'        => $this->t('Domain policy reject total'),

            'hard_bounce_total'      => $this->t('Hard bounce total'),
            'soft_bounce_total'      => $this->t('Soft bounce total'),
            'internal_bounce_total'  => $this->t('Internal bounce total'),

            'complaint_total'        => $this->t('Complaint total'),

            'success_hourly'         => $this->t('Success hourly'),
            'error_hourly'           => $this->t('Error hourly'),
            'giveup_hourly'          => $this->t('Giveup hourly'),
            'blacklisted_hourly'     => $this->t('Blacklisted hourly'),
            'dp_reject_hourly'       => $this->t('Domain policy reject hourly'),

            'hard_bounce_hourly'     => $this->t('Hard bounce hourly'),
            'soft_bounce_hourly'     => $this->t('Soft bounce hourly'),
            'internal_bounce_hourly' => $this->t('Internal bounce hourly'),

            'complaint_hourly'       => $this->t('Complaint hourly'),
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
        $criteria->compare('t.customer_id', (int)$this->customer_id);
        $criteria->compare('t.campaign_id', (int)$this->campaign_id);

        return new CActiveDataProvider(get_class($this), [
            'criteria'      => $criteria,
            'pagination'    => [
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ],
            'sort'=>[
                'defaultOrder' => [
                    'id'    => CSort::SORT_ASC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CampaignDeliveryCountHistory the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var CampaignDeliveryCountHistory $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @param \Carbon\Carbon $dateStart
     * @param \Carbon\Carbon $dateEnd
     * @return bool
     */
    public function calculate(Carbon\Carbon $dateStart, Carbon\Carbon $dateEnd): bool
    {
        if (empty($this->campaign_id)) {
            return false;
        }

        $this->total                 = $this->countByStatusAndDateTime();
        $this->success_total         = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_SUCCESS);
        $this->error_total           = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_ERROR);
        $this->giveup_total          = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_GIVEUP);
        $this->blacklisted_total     = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_BLACKLISTED);
        $this->dp_reject_total       = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_DOMAIN_POLICY_REJECT);
        $this->hard_bounce_total     = $this->countByStatusAndDateTime(CampaignBounceLog::BOUNCE_HARD);
        $this->soft_bounce_total     = $this->countByStatusAndDateTime(CampaignBounceLog::BOUNCE_SOFT);
        $this->internal_bounce_total = $this->countByStatusAndDateTime(CampaignBounceLog::BOUNCE_INTERNAL);
        $this->complaint_total       = $this->countByStatusAndDateTime(EmailBlacklist::ABUSE_COMPLAINT_REASON);

        $this->success_hourly         = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_SUCCESS, $dateStart, $dateEnd);
        $this->error_hourly           = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_ERROR, $dateStart, $dateEnd);
        $this->giveup_hourly          = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_GIVEUP, $dateStart, $dateEnd);
        $this->blacklisted_hourly     = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_BLACKLISTED, $dateStart, $dateEnd);
        $this->dp_reject_hourly       = $this->countByStatusAndDateTime(CampaignDeliveryLog::STATUS_DOMAIN_POLICY_REJECT, $dateStart, $dateEnd);
        $this->hard_bounce_hourly     = $this->countByStatusAndDateTime(CampaignBounceLog::BOUNCE_HARD, $dateStart, $dateEnd);
        $this->soft_bounce_hourly     = $this->countByStatusAndDateTime(CampaignBounceLog::BOUNCE_SOFT, $dateStart, $dateEnd);
        $this->internal_bounce_hourly = $this->countByStatusAndDateTime(CampaignBounceLog::BOUNCE_INTERNAL, $dateStart, $dateEnd);
        $this->complaint_hourly       = $this->countByStatusAndDateTime(EmailBlacklist::ABUSE_COMPLAINT_REASON, $dateStart, $dateEnd);

        return true;
    }

    /**
     * Counts the number of campaign delivery logs by status and date range.
     *
     * @param string $status The status of the logs to be counted.
     * @param Carbon\Carbon|null $dateStart The start date of the date range. Defaults to null.
     * @param Carbon\Carbon|null $dateEnd The end date of the date range. Defaults to null.
     * @return int The number of campaign delivery logs that match the given status and date range.
     */
    public function countByStatusAndDateTime(string $status = '', ?Carbon\Carbon $dateStart = null, ?Carbon\Carbon $dateEnd = null): int
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.campaign_id', (int)$this->campaign_id);

        $model               = $this->campaign->getCampaignDeliveryLogModel();
        $columnToCompareWith = 't.status';
        $statusMapping       = $this->getStatusToCampaignLogModelMapping();

        if ($status && in_array($status, array_keys($statusMapping))) {
            $model = $statusMapping[$status];
            if ($model instanceof CampaignBounceLog) {
                $columnToCompareWith = 't.bounce_type';
            }

            if (!($model instanceof CampaignComplainLog)) {
                $criteria->compare($columnToCompareWith, $status);
            }
        }

        if ($dateStart && $dateEnd) {
            $criteria->addCondition('t.date_added >= :dateStart AND t.date_added < :dateEnd');
            $criteria->params[':dateStart'] = $dateStart->format('Y-m-d H:i:s');
            $criteria->params[':dateEnd']   = $dateEnd->format('Y-m-d H:i:s');
        }

        return (int)$model->count($criteria);
    }

    /**
     * @return array
     */
    public function getStatusToCampaignLogModelMapping(): array
    {
        return [
            CampaignDeliveryLog::STATUS_SUCCESS              => $this->campaign->getCampaignDeliveryLogModel(),
            CampaignDeliveryLog::STATUS_ERROR                => $this->campaign->getCampaignDeliveryLogModel(),
            CampaignDeliveryLog::STATUS_GIVEUP               => $this->campaign->getCampaignDeliveryLogModel(),
            CampaignDeliveryLog::STATUS_BLACKLISTED          => $this->campaign->getCampaignDeliveryLogModel(),
            CampaignDeliveryLog::STATUS_DOMAIN_POLICY_REJECT => $this->campaign->getCampaignDeliveryLogModel(),

            CampaignBounceLog::BOUNCE_INTERNAL               => CampaignBounceLog::model(),
            CampaignBounceLog::BOUNCE_SOFT                   => CampaignBounceLog::model(),
            CampaignBounceLog::BOUNCE_HARD                   => CampaignBounceLog::model(),

            EmailBlacklist::ABUSE_COMPLAINT_REASON           => CampaignComplainLog::model(),
        ];
    }
}
