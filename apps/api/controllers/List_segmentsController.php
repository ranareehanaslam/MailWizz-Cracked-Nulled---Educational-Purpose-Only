<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * List_segmentsController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class List_segmentsController extends Controller
{
    /**
     * @return array
     */
    public function accessRules()
    {
        return [
            // allow all authenticated users on all actions
            ['allow', 'users' => ['@']],
            // deny all rule.
            ['deny'],
        ];
    }

    /**
     * Handles the listing of the email list segments.
     * The listing is based on page number and number of list segments per page.
     *
     * @param string $list_uid
     *
     * @return void
     * @throws CException
     */
    public function actionIndex($list_uid)
    {
        $list = Lists::model()->findByAttributes([
            'list_uid'      => $list_uid,
            'customer_id'   => (int)user()->getId(),
        ]);

        if (empty($list)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The subscribers list does not exist.'),
            ], 404);
            return;
        }

        $perPage = (int)request()->getQuery('per_page', 10);
        $page    = (int)request()->getQuery('page', 1);

        $maxPerPage = (int)hooks()->applyFilters('api_list_segments_collection_max_records_per_page', 1000);
        $minPerPage = (int)hooks()->applyFilters('api_list_segments_collection_min_records_per_page', 10);

        if ($perPage < $minPerPage) {
            $perPage = $minPerPage;
        }

        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        if ($page < 1) {
            $page = 1;
        }

        $data = [
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => [],
        ];

        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', (int)$list->list_id);
        $criteria->addNotInCondition('status', [ListSegment::STATUS_PENDING_DELETE]);

        /** @var CDbCriteria $criteria */
        $criteria = hooks()->applyFilters('api_list_segments_collection_count_criteria', $criteria);

        $count = ListSegment::model()->count($criteria);

        if ($count == 0) {
            $this->renderJson([
                'status'    => 'success',
                'data'      => $data,
            ]);
            return;
        }

        $totalPages = ceil($count / $perPage);

        $data['count']          = $count;
        $data['current_page']   = $page;
        $data['next_page']      = $page < $totalPages ? $page + 1 : null;
        $data['prev_page']      = $page > 1 ? $page - 1 : null;
        $data['total_pages']    = $totalPages;

        $criteria->order    = 't.segment_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;

        /** @var CDbCriteria $criteria */
        $criteria = hooks()->applyFilters('api_list_segments_collection_find_criteria', $criteria);

        $segments = ListSegment::model()->findAll($criteria);

        foreach ($segments as $segment) {
            $record = $segment->getAttributes(['segment_uid', 'name']);
            $record['subscribers_count'] = $segment->countSubscribers();
            $data['records'][] = hooks()->applyFilters('api_list_segments_collection_record', $record, $segment);
        }

        $this->renderJson([
            'status'    => 'success',
            'data'      => hooks()->applyFilters('api_list_segments_collection_data', $data),
        ]);
    }

    /**
     * Handles the listing of a single list segment.
     *
     * @param string $list_uid
     * @param string $segment_uid
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function actionView($list_uid, $segment_uid)
    {
        $list = Lists::model()->findByAttributes([
            'list_uid'      => $list_uid,
            'customer_id'   => (int)user()->getId(),
        ]);

        if (empty($list)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The subscribers list does not exist.'),
            ], 404);
            return;
        }

        $segment = ListSegment::model()->findByAttributes([
            'segment_uid'   => $segment_uid,
            'list_id'       => $list->list_id,
        ]);

        if (empty($segment)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The segment list does not exist.'),
            ], 404);
            return;
        }

        /** @var array $record */
        $record = $segment->getAttributes(['segment_uid', 'segment_id', 'name', 'operator_match']);

        $record['date_added']        = $segment->dateAdded;
        $record['subscribers_count'] = $segment->countSubscribers();

        $conditions = [];
        foreach ($segment->segmentConditions as $condition) {
            $conditions[] = $condition->getAttributes(['field_id', 'operator_id', 'value']);
        }
        $record['conditions'] = $conditions;

        $campaignConditionsArray = [];
        $campaignConditions = ListSegmentCampaignCondition::model()->findAllByAttributes([
            'segment_id' => $segment->segment_id,
        ]);

        foreach ($campaignConditions as $condition) {
            $campaignConditionsArray[] = $condition->getAttributes(['action', 'campaign_id', 'time_comparison_operator', 'time_value', 'time_unit']);
        }
        $record['campaign_conditions'] = $campaignConditionsArray;

        $data = [
            'record' => $record,
        ];

        $this->renderJson([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    /**
     * Handles the listing of a single list segment subscribers.
     *
     * @param string $list_uid
     * @param string $segment_uid
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function actionSubscribers($list_uid, $segment_uid)
    {
        $list = Lists::model()->findByAttributes([
            'list_uid'      => $list_uid,
            'customer_id'   => (int)user()->getId(),
        ]);

        if (empty($list)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The subscribers list does not exist.'),
            ], 404);
            return;
        }

        $segment = ListSegment::model()->findByAttributes([
            'segment_uid'   => $segment_uid,
            'list_id'       => $list->list_id,
        ]);

        if (empty($segment)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The list segment does not exist.'),
            ], 404);
            return;
        }


        $perPage    = (int)request()->getQuery('per_page', 10);
        $page       = (int)request()->getQuery('page', 1);

        $maxPerPage = (int)hooks()->applyFilters('api_list_segments_subscribers_collection_max_records_per_page', 1000);
        $minPerPage = (int)hooks()->applyFilters('api_list_segments_subscribers_collection_min_records_per_page', 10);

        if ($perPage < $minPerPage) {
            $perPage = $minPerPage;
        }

        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        if ($page < 1) {
            $page = 1;
        }

        $data = [
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => [],
        ];

        $count = $segment->countSubscribers();

        if ($count == 0) {
            $this->renderJson([
                'status'    => 'success',
                'data'      => $data,
            ]);
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$list->list_id);
        $criteria->order = 'sort_order ASC';
        $fields = ListField::model()->findAll($criteria);

        if (empty($fields)) {
            $this->renderJson([
                'status' => 'error',
                'error'  => t('api', 'The subscribers list does not have any custom field defined.'),
            ], 404);
            return;
        }

        $totalPages = ceil($count / $perPage);

        $data['count']          = $count;
        $data['current_page']   = $page;
        $data['next_page']      = $page < $totalPages ? $page + 1 : null;
        $data['prev_page']      = $page > 1 ? $page - 1 : null;
        $data['total_pages']    = $totalPages;

        /** @var ListSubscriber $subscribers */
        $subscribers = (array)$segment->findSubscribers(($page - 1) * $perPage, $perPage);

        foreach ($subscribers as $subscriber) {
            $record = ['subscriber_uid' => null]; // keep this first!
            foreach ($fields as $field) {
                if ($field->tag == 'EMAIL') {
                    $record[$field->tag] = $subscriber->getDisplayEmail();
                    continue;
                }

                $value = '';
                $criteria = new CDbCriteria();
                $criteria->select = 'value';
                $criteria->compare('field_id', (int)$field->field_id);
                $criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
                $valueModels = $subscriber->getListFieldValueModel()->findAll($criteria);
                if (!empty($valueModels)) {
                    $value = [];
                    foreach ($valueModels as $valueModel) {
                        $value[] = $valueModel->value;
                    }
                    $value = implode($field->getMultiValuesSeparator(), $value);
                }
                $record[$field->tag] = $value;
            }

            $record['subscriber_uid']   = (string)$subscriber->subscriber_uid;
            $record['status']           = $subscriber->status;
            $record['source']           = $subscriber->source;
            $record['ip_address']       = $subscriber->ip_address;
            $record['date_added']       = $subscriber->date_added;

            $data['records'][] = hooks()->applyFilters('api_list_segments_subscribers_collection_record', $record, $subscriber);
        }

        $this->renderJson([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    /**
     * Handles the creation of segments for a certain email list.
     *
     * @param string $list_uid
     * @return void
     * @throws CException
     */
    public function actionCreate($list_uid)
    {
        if (!request()->getIsPostRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only POST requests allowed for this endpoint.'),
            ], 400);
            return;
        }

        $list = Lists::model()->findByAttributes([
            'list_uid'      => $list_uid,
            'customer_id'   => (int)user()->getId(),
        ]);

        if (empty($list)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The subscribers list does not exist.'),
            ], 404);
            return;
        }

        $segment                 = new ListSegment();
        $segment->list_id        = (int)$list->list_id;
        $segment->name           = (string)request()->getPost('name', '');
        $segment->operator_match = (string)request()->getPost('operator_match', ListSegment::OPERATOR_MATCH_ALL);

        if (!$segment->validate()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => $segment->shortErrors->getAll(),
            ], 422);
            return;
        }

        $conditions         = [];
        $campaignConditions = [];

        $postConditions         = (array)request()->getPost('conditions', []);
        $postCampaignConditions = (array)request()->getPost('campaign_conditions', []);
        $conditionsCount        = count($postConditions) + count($postCampaignConditions);

        /** @var Customer $customer */
        $customer = user()->getModel();

        $maxAllowedConditions = (int)$customer->getGroupOption('lists.max_segment_conditions', 3);
        if ($conditionsCount > $maxAllowedConditions) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('list_segments', 'You are only allowed to add {num} segment conditions.', ['{num}' => $maxAllowedConditions]),
            ], 422);
            return;
        }

        if (!empty($postConditions)) {
            $hashedConditions = [];
            /** @var array $conditionAttributes */
            foreach ($postConditions as $conditionAttributes) {
                $cond = new ListSegmentCondition();
                $cond->attributes = $conditionAttributes;

                $hashKey = sha1($cond->field_id . $cond->operator_id . $cond->value);
                if (isset($hashedConditions[$hashKey])) {
                    continue;
                }
                $hashedConditions[$hashKey] = true;

                $conditions[] = $cond;
            }
        }

        if (!empty($postCampaignConditions)) {
            $hashedConditions = [];
            /** @var array $conditionAttributes */
            foreach ($postCampaignConditions as $conditionAttributes) {
                $cond = new ListSegmentCampaignCondition();
                $cond->attributes = $conditionAttributes;

                $hashKey = sha1($cond->action . $cond->campaign_id . $cond->time_comparison_operator . $cond->time_value . $cond->time_unit);
                if (isset($hashedConditions[$hashKey])) {
                    continue;
                }
                $hashedConditions[$hashKey] = true;

                $campaignConditions[] = $cond;
            }
        }

        $success = false;
        $errors = [];
        $subscribersCount = 0;
        $transaction      = db()->beginTransaction();

        try {
            if (!$segment->save()) {
                $errors['segment'] = $segment->shortErrors->getAll();
                throw new Exception((string)json_encode($errors));
            }

            $conditionsErrors = [];
            foreach ($conditions as $cond) {
                $cond->segment_id = (int)$segment->segment_id;
                if (!$cond->save()) {
                    $conditionsErrors[] = $cond->shortErrors->getAll();
                }
            }
            if (!empty($conditionsErrors)) {
                $errors['conditions'] = $conditionsErrors;
                throw new Exception((string)json_encode($errors));
            }

            $conditionsErrors = [];
            foreach ($campaignConditions as $cond) {
                $cond->segment_id = (int)$segment->segment_id;
                if (!$cond->save()) {
                    $conditionsErrors[] = $cond->shortErrors->getAll();
                }
            }
            if (!empty($conditionsErrors)) {
                $errors['campaign_conditions'] = $conditionsErrors;
                throw new Exception((string)json_encode($conditions));
            }

            $timeNow = time();
            try {
                $subscribersCount = $segment->countSubscribers();
            } catch (Exception $e) {
            }

            if ((time() - $timeNow) > (int)$customer->getGroupOption('lists.max_segment_wait_timeout', 5)) {
                $errors = t('list_segments', 'Current segmentation is too deep and loads too slow, please revise your segment conditions!');
                throw new Exception((string)json_encode([$errors]));
            }

            $transaction->commit();

            /** @var CustomerActionLogBehavior $logAction */
            $logAction = $customer->getLogAction();
            $logAction->segmentCreated($segment);

            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
        }

        if (!$success) {
            $this->renderJson([
                'status' => 'error',
                'error'  => $errors,
            ], 422);
            return;
        }

        $segment->refresh();

        /** @var array $record */
        $record = $segment->getAttributes(['segment_uid', 'segment_id', 'name', 'operator_match']);

        $record['date_added']        = $segment->dateTimeFormatter->getDateAdded();
        $record['subscribers_count'] = $subscribersCount;

        $conditions = [];
        foreach ($segment->segmentConditions as $condition) {
            $conditions[] = $condition->getAttributes(['field_id', 'operator_id', 'value']);
        }
        $record['conditions'] = $conditions;

        $campaignConditionsArray = [];
        $campaignConditions = ListSegmentCampaignCondition::model()->findAllByAttributes([
            'segment_id' => $segment->segment_id,
        ]);

        foreach ($campaignConditions as $condition) {
            $campaignConditionsArray[] = $condition->getAttributes(['action', 'campaign_id', 'time_comparison_operator', 'time_value', 'time_unit']);
        }
        $record['campaign_conditions'] = $campaignConditionsArray;

        $this->renderJson([
            'status' => 'success',
            'data' => [
                'record' => $record,
            ],
        ], 201);
    }

    /**
     * Handles the updating of an existing email list segment.
     *
     * @param string $list_uid
     * @param string $segment_uid
     * @return void
     * @throws CException
     */
    public function actionUpdate($list_uid, $segment_uid)
    {
        if (!request()->getIsPutRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only PUT requests allowed for this endpoint.'),
            ], 400);
            return;
        }

        $list = Lists::model()->findByAttributes([
            'list_uid'    => $list_uid,
            'customer_id' => (int)user()->getId(),
        ]);

        if (empty($list)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The subscribers list does not exist.'),
            ], 404);
            return;
        }

        $segment = ListSegment::model()->findByAttributes([
            'segment_uid'   => $segment_uid,
            'list_id'       => $list->list_id,
        ]);

        if (empty($segment)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The segment list does not exist.'),
            ], 404);
            return;
        }

        $segment->name           = (string)request()->getPut('name', $segment->name);
        $segment->operator_match = (string)request()->getPut('operator_match', $segment->operator_match);

        if (!$segment->validate()) {
            $this->renderJson([
                'status' => 'error',
                'error'  => $segment->shortErrors->getAll(),
            ], 422);
            return;
        }

        $conditions         = [];
        $campaignConditions = [];

        $postConditions         = (array)request()->getPut('conditions', []);
        $postCampaignConditions = (array)request()->getPut('campaign_conditions', []);
        $conditionsCount        = count($postConditions) + count($postCampaignConditions);

        /** @var Customer $customer */
        $customer = user()->getModel();

        $maxAllowedConditions = (int)$customer->getGroupOption('lists.max_segment_conditions', 3);
        if ($conditionsCount > $maxAllowedConditions) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('list_segments', 'You are only allowed to add {num} segment conditions.', ['{num}' => $maxAllowedConditions]),
            ], 422);
            return;
        }

        if (!empty($postConditions)) {
            $hashedConditions = [];
            /** @var array $conditionAttributes */
            foreach ($postConditions as $conditionAttributes) {
                $cond = new ListSegmentCondition();
                $cond->attributes = $conditionAttributes;

                $hashKey = sha1($cond->field_id . $cond->operator_id . $cond->value);
                if (isset($hashedConditions[$hashKey])) {
                    continue;
                }
                $hashedConditions[$hashKey] = true;

                $conditions[] = $cond;
            }
        }

        if (!empty($postCampaignConditions)) {
            $hashedConditions   = [];
            /** @var array $conditionAttributes */
            foreach ($postCampaignConditions as $conditionAttributes) {
                $cond = new ListSegmentCampaignCondition();
                $cond->attributes = $conditionAttributes;

                $hashKey = sha1($cond->action . $cond->campaign_id . $cond->time_comparison_operator . $cond->time_value . $cond->time_unit);
                if (isset($hashedConditions[$hashKey])) {
                    continue;
                }
                $hashedConditions[$hashKey] = true;

                $campaignConditions[] = $cond;
            }
        }

        $success = false;
        $errors = [];
        $subscribersCount = 0;
        $transaction = db()->beginTransaction();

        try {
            if (!$segment->save()) {
                $errors['segment'] = $segment->shortErrors->getAll();
                throw new Exception((string)json_encode($errors));
            }

            ListSegmentCondition::model()->deleteAllByAttributes([
                'segment_id' => $segment->segment_id,
            ]);

            $conditionsErrors = [];
            foreach ($conditions as $cond) {
                $cond->segment_id = (int)$segment->segment_id;
                if (!$cond->save()) {
                    $conditionsErrors[] = $cond->shortErrors->getAll();
                }
            }
            if (!empty($conditionsErrors)) {
                $errors['conditions'] = $conditionsErrors;
                throw new Exception((string)json_encode($errors));
            }

            ListSegmentCampaignCondition::model()->deleteAllByAttributes([
                'segment_id' => $segment->segment_id,
            ]);

            $conditionsErrors = [];
            foreach ($campaignConditions as $cond) {
                $cond->segment_id = (int)$segment->segment_id;
                if (!$cond->save()) {
                    $conditionsErrors[] = $cond->shortErrors->getAll();
                }
            }
            if (!empty($conditionsErrors)) {
                $errors['campaign_conditions'] = $conditionsErrors;
                throw new Exception((string)json_encode($errors));
            }

            $timeNow = time();
            try {
                $subscribersCount = $segment->countSubscribers();
            } catch (Exception $e) {
            }

            if ((time() - $timeNow) > (int)$customer->getGroupOption('lists.max_segment_wait_timeout', 5)) {
                $errors = t('list_segments', 'Current segmentation is too deep and loads too slow, please revise your segment conditions!');
                throw new Exception((string)json_encode([$errors]));
            }

            $transaction->commit();

            /** @var CustomerActionLogBehavior $logAction */
            $logAction = $customer->getLogAction();
            $logAction->segmentUpdated($segment);

            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
        }

        if (!$success) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => $errors,
            ], 422);
            return;
        }

        /** @var array $record */
        $record = $segment->getAttributes(['segment_uid', 'segment_id', 'name', 'operator_match']);

        $record['date_added']        = $segment->dateAdded;
        $record['subscribers_count'] = $subscribersCount;

        $conditions = [];
        foreach ($segment->segmentConditions as $condition) {
            $conditions[] = $condition->getAttributes(['field_id', 'operator_id', 'value']);
        }
        $record['conditions'] = $conditions;

        $campaignConditionsArray = [];
        $campaignConditions = ListSegmentCampaignCondition::model()->findAllByAttributes([
            'segment_id' => $segment->segment_id,
        ]);

        foreach ($campaignConditions as $condition) {
            $campaignConditionsArray[] = $condition->getAttributes(['action', 'campaign_id', 'time_comparison_operator', 'time_value', 'time_unit']);
        }
        $record['campaign_conditions'] = $campaignConditionsArray;

        $this->renderJson([
            'status' => 'success',
            'data' => [
                'record' => $record,
            ],
        ]);
    }

    /**
     * Handles deleting of an existing email list segment.
     *
     * @param string $list_uid
     * @param string $segment_uid
     *
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function actionDelete($list_uid, $segment_uid)
    {
        if (!request()->getIsDeleteRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only DELETE requests allowed for this endpoint.'),
            ], 400);
        }

        $list = Lists::model()->findByAttributes([
            'list_uid'      => $list_uid,
            'customer_id'   => (int)user()->getId(),
        ]);

        if (empty($list)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The subscribers list does not exist.'),
            ], 404);
            return;
        }

        $segment = ListSegment::model()->findByAttributes([
            'segment_uid'   => $segment_uid,
            'list_id'       => $list->list_id,
        ]);

        if (empty($segment)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The segment list does not exist.'),
            ], 404);
            return;
        }

        $segment->delete();

        /** @var Customer $customer */
        $customer = user()->getModel();

        /** @var CustomerActionLogBehavior $logAction */
        $logAction = $customer->getLogAction();
        $logAction->segmentDeleted($segment);

        $this->renderJson([
            'status' => 'success',
        ]);
    }

    /**
     * Handles the listing of the list segments operators.
     *
     * @return void
     * @throws CException
     */
    public function actionCondition_operators()
    {
        $data = [
            'count'   => 0,
            'records' => [],
        ];

        $count = ListSegmentOperator::model()->count();

        if ($count == 0) {
            $this->renderJson([
                'status' => 'success',
                'data'   => $data,
            ]);
            return;
        }

        $data['count'] = $count;

        $operators = ListSegmentOperator::model()->findAll();

        foreach ($operators as $operator) {
            $data['records'][] = $operator->getAttributes(['operator_id', 'name', 'slug']);
        }

        $this->renderJson([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * It will generate the timestamp that will be used to generate the ETAG for GET requests.
     *
     * @return int
     * @throws CException
     */
    public function generateLastModified()
    {
        static $lastModified;

        if ($lastModified !== null) {
            return $lastModified;
        }

        $row = [];

        if ($this->getAction()->getId() == 'index') {
            $listUid    = request()->getQuery('list_uid');
            $perPage    = (int)request()->getQuery('per_page', 10);
            $page       = (int)request()->getQuery('page', 1);

            $maxPerPage = (int)hooks()->applyFilters('api_list_segments_collection_max_records_per_page', 1000);
            $minPerPage = (int)hooks()->applyFilters('api_list_segments_collection_min_records_per_page', 10);

            if ($perPage < $minPerPage) {
                $perPage = $minPerPage;
            }

            if ($perPage > $maxPerPage) {
                $perPage = $maxPerPage;
            }

            if ($page < 1) {
                $page = 1;
            }

            $list = Lists::model()->findByAttributes([
                'list_uid'      => $listUid,
                'customer_id'   => (int)user()->getId(),
            ]);

            if (empty($list)) {
                return $lastModified = parent::generateLastModified();
            }

            $limit  = $perPage;
            $offset = ($page - 1) * $perPage;

            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                     SELECT `a`.`list_id`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                     FROM `{{list_segment}}` `a` 
                     WHERE `a`.`list_id` = :lid 
                     ORDER BY a.`segment_id` DESC 
                     LIMIT :l OFFSET :o
                ) AS t 
                WHERE `t`.`list_id` = :lid
            ';

            $command = db()->createCommand($sql);
            $command->bindValue(':lid', (int)$list->list_id, PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);

            $row = $command->queryRow();
        }

        if (isset($row['timestamp'])) {
            $timestamp = round((float)$row['timestamp']);
            // avoid for when subscribers imported having same timestamp
            if (preg_match('/\.(\d+)/', (string)$row['timestamp'], $matches)) {
                $timestamp += (int)$matches[1];
            }
            return $lastModified = (int)$timestamp;
        }

        return $lastModified = parent::generateLastModified();
    }
}
