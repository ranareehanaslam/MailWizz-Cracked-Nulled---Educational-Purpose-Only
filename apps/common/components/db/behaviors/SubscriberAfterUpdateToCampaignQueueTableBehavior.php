<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * SubscriberAfterUpdateToCampaignQueueTableBehavior
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.3
 *
 */

/**
 * @property ListSubscriber $owner
 */
class SubscriberAfterUpdateToCampaignQueueTableBehavior extends CActiveRecordBehavior
{
    /**
     * @var bool
     */
    private $_isNewRecord = false;

    /**
     * @var bool
     */
    private $_processed = false;

    /**
     * @param CModelEvent $event
     *
     * @return void
     */
    public function beforeSave($event)
    {
        parent::beforeSave($event);
        $this->_isNewRecord = $this->owner->getIsNewRecord();
    }

    /**
     * @param CEvent $event
     *
     * @return void
     * @throws CDbException
     *
     * @see https://github.com/onetwist-software/mailwizz/issues/470 for minTimeHour and minTimeMinute condition
     */
    public function afterSave($event)
    {
        parent::afterSave($event);

        // ref
        $owner = $this->owner;

        // stop if no email
        if (empty($owner->email)) {
            return;
        }

        if ($this->_processed) {
            return;
        }
        $this->_processed = true;

        // if it's an existing record, confirmed
        $allow = !$this->_isNewRecord && $owner->status == ListSubscriber::STATUS_CONFIRMED;

        if (!$allow) {
            return;
        }

        $criteria  = new CDbCriteria();
        $criteria->with = [];
        $criteria->compare('t.list_id', (int)$owner->list_id);
        $criteria->addCondition('t.segment_id IS NULL');
        $criteria->compare('t.type', Campaign::TYPE_AUTORESPONDER);
        $criteria->addNotInCondition('t.status', [Campaign::STATUS_SENT, Campaign::STATUS_DRAFT, Campaign::STATUS_PENDING_DELETE]);

        $criteria->with['option'] = [
            'together'  => true,
            'joinType'  => 'INNER JOIN',
            'select'    => 'option.autoresponder_include_imported, option.autoresponder_include_current, option.autoresponder_time_value, option.autoresponder_time_unit, option.autoresponder_time_min_hour, option.autoresponder_time_min_minute',
            'condition' => 'option.autoresponder_event = :evt',
            'params'    => [
                ':evt' => CampaignOption::AUTORESPONDER_EVENT_AFTER_PROFILE_UPDATE,
            ],
        ];

        /** @var Campaign[] $campaigns */
        $campaigns = Campaign::model()->findAll($criteria);

        foreach ($campaigns as $campaign) {

            // ref
            $campaignOption = $campaign->option;

            // if imported are not allowed to receive
            if ($owner->getIsImported() && !$campaignOption->getAutoresponderIncludeImported()) {
                continue;
            }

            // make sure they updated the profile by themselves
            if (!$owner->getCustomMetaData(ListSubscriber::META_DATA_PROFILE_LAST_UPDATED_KEY_NAME)) {
                continue;
            }

            $metaDataKey = sprintf('campaigns.autoresponder.event.after-update.campaign:%d', $campaign->campaign_id);
            if ($owner->getCustomMetaData($metaDataKey)) {
                continue;
            }
            $owner->setCustomMetaData($metaDataKey, 1);

            $minTimeHour   = !empty($campaignOption->autoresponder_time_min_hour) ? $campaignOption->autoresponder_time_min_hour : null;
            $minTimeMinute = !empty($campaignOption->autoresponder_time_min_minute) ? $campaignOption->autoresponder_time_min_minute : null;
            $timeValue     = (int)$campaignOption->autoresponder_time_value;
            $timeUnit      = strtoupper((string)$campaignOption->autoresponder_time_unit);

            try {
                $sendAt = new CDbExpression(sprintf('DATE_ADD(NOW(), INTERVAL %d %s)', $timeValue, $timeUnit));

                // 1.4.3
                if (!empty($minTimeHour) && !empty($minTimeMinute)) {
                    $sendAt = new CDbExpression(sprintf(
                        '
	                	IF (
	                		DATE_ADD(NOW(), INTERVAL %1$d %2$s) > DATE_FORMAT(DATE_ADD(NOW(), INTERVAL %1$d %2$s), \'%%Y-%%m-%%d %3$s:%4$s:00\'),
	                		DATE_ADD(NOW(), INTERVAL %1$d %2$s),
	                		DATE_FORMAT(DATE_ADD(NOW(), INTERVAL %1$d %2$s), \'%%Y-%%m-%%d %3$s:%4$s:00\')
	                	)',
                        $timeValue,
                        $timeUnit,
                        $minTimeHour,
                        $minTimeMinute
                    ));
                }

                $campaign->queueTable->addSubscriber([
                    'subscriber_id' => $owner->subscriber_id,
                    'send_at'       => $sendAt,
                ]);
            } catch (Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
    }
}
