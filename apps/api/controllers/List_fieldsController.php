<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}
/**
 * List_fieldsController
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * @property ListFieldsControllerCallbacksBehavior $callbacks
 */
class List_fieldsController extends Controller
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
     * List of behaviors attached to this controller
     * The behaviors are merged with the one from parent implementation
     *
     * @return array
     * @throws CException
     */
    public function behaviors()
    {
        return CMap::mergeArray([
            'callbacks' => [
                'class' => 'customer.components.behaviors.ListFieldsControllerCallbacksBehavior',
            ],
        ], parent::behaviors());
    }

    /**
     * Handles the listing of the email list custom fields.
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

        $fields = ListField::model()->findAllByAttributes([
            'list_id'    => (int)$list->list_id,
        ]);

        if (empty($fields)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The subscribers list does not have any custom field defined.'),
            ], 404);
            return;
        }

        $data = [
            'records' => [],
        ];

        foreach ($fields as $field) {
            $attributes = $field->getAttributes([
                'field_id', 'label', 'tag', 'help_text', 'description', 'default_value', 'required', 'visibility', 'sort_order',
            ]);
            $attributes['type'] = $field->type->getAttributes(['name', 'identifier', 'description']);

            // since 1.3.6.2
            if (!empty($field->options)) {
                $attributes['options'] = [];
                foreach ($field->options as $option) {
                    $attributes['options'][$option->value] = $option->name;
                }
            }

            $data['records'][]  = $attributes;
        }

        $this->renderJson([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    /**
     * Handles the listing of a single list field.
     *
     * @param string $list_uid
     * @param int $field_id
     * @return void
     * @throws CException
     */
    public function actionView($list_uid, $field_id)
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

        /** @var ListField|null $field */
        $field = ListField::model()->findByAttributes([
            'list_id'  => $list->list_id,
            'field_id' => (int)$field_id,
        ]);

        if (empty($field)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The list field does not exist.'),
            ], 404);
            return;
        }

        $record = $field->getAttributes([
            'field_id', 'label', 'tag', 'help_text', 'description', 'default_value', 'required', 'visibility', 'sort_order',
        ]);
        $record['type'] = $field->type->getAttributes(['name', 'identifier', 'description']);

        if (!empty($field->options)) {
            $record['options'] = [];
            foreach ($field->options as $option) {
                $record['options'][$option->value] = $option->name;
            }
        }

        $this->renderJson([
            'status'    => 'success',
            'data'      => [
                'record' => $record,
            ],
        ]);
    }

    /**
     * @param string $list_uid
     * @return void
     * @throws CException
     * @throws Exception
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

        $attributes = (array)request()->getPost('', []);

        $fieldType = $attributes['type'] ?? null;
        if (empty($fieldType)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The field type should be set.'),
            ], 422);
            return;
        }

        $fieldTypeModel = ListFieldType::model()->findByAttributes([
            'identifier' => $fieldType,
        ]);
        if (empty($fieldTypeModel)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The field type identifier is wrong.'),
            ], 422);
            return;
        }

        // since 2.3.9
        /** @var Customer $customer */
        $customer = $list->customer;
        $maxListFields = (int)$customer->getGroupOption('lists.max_custom_fields_per_list', -1);
        $listFieldsCount = (int)ListField::model()->countByAttributes(['list_id' => $list->list_id]);
        if ($maxListFields > -1 && $listFieldsCount >= $maxListFields) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('list_fields', 'You have reached the maximum number of allowed custom fields. Your list has {current} custom fields and you are allowed only {max} custom fields.', [
                    '{current}' => $listFieldsCount,
                    '{max}'     => $maxListFields,
                ]),
            ], 422);
            return;
        }

        $this->buildPostDataFromRequest($list);
        $result = $this->processListFieldType($list, $fieldTypeModel);

        if ($result['status'] === 'error') {
            $this->renderJson([
                'status' => 'error',
                'error'  => $result['errors'],
            ], 422);
            return;
        }

        /** @var  OptionCronProcessSubscribers $optionCronProcessSubscribers */
        $optionCronProcessSubscribers = container()->get(OptionCronProcessSubscribers::class);

        if ($optionCronProcessSubscribers->getSyncCustomFieldsValues()) {
            // since 2.2.2
            $list->sendQueueRequestSyncCustomFields();
        }

        $record = [];

        if (!empty($result['fields'])) {
            /** @var ListField $field */
            $field = current($result['fields']);

            $attributes = $field->getAttributes([
                'field_id', 'label', 'tag', 'help_text', 'description', 'default_value', 'required', 'visibility', 'sort_order',
            ]);
            $attributes['type'] = $field->type->getAttributes(['name', 'identifier', 'description']);
            $attributes['list'] = $field->list->getAttributes(['list_uid', 'display_name']);

            // since 1.3.6.2
            if (!empty($field->options)) {
                $attributes['options'] = [];
                foreach ($field->options as $option) {
                    $attributes['options'][$option->value] = $option->name;
                }
            }

            $record = CMap::mergeArray($record, $attributes);
        }

        $this->renderJson([
            'status' => 'success',
            'data'   => ['record' => $record],
        ], 201);
    }

    /**
     * @param string $list_uid
     * @param string $field_id
     * @return void
     * @throws CException
     * @throws Exception
     */
    public function actionUpdate($list_uid, $field_id)
    {
        if (!request()->getIsPutRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only PUT requests allowed for this endpoint.'),
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

        /** @var ListField|null $listField */
        $listField = ListField::model()->findByAttributes([
            'list_id'  => $list->list_id,
            'field_id' => (int)$field_id,
        ]);

        if (empty($listField)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The list field does not exist.'),
            ], 404);
            return;
        }

        $attributes = (array)request()->getPut('', []);
        $fieldType  = $attributes['type'] ?? null;
        if (!empty($fieldType) && $listField->type->identifier !== $fieldType) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'You are not allowed to change the field type. Please delete the field and re-create it.'),
            ], 422);
            return;
        }

        $this->buildPostDataFromRequest($list, $listField);
        $result = $this->processListFieldType($list, $listField->type);

        if ($result['status'] === 'error') {
            $this->renderJson([
                'status' => 'error',
                'error'  => $result['errors'],
            ], 422);
            return;
        }

        /** @var  OptionCronProcessSubscribers $optionCronProcessSubscribers */
        $optionCronProcessSubscribers = container()->get(OptionCronProcessSubscribers::class);

        if ($optionCronProcessSubscribers->getSyncCustomFieldsValues()) {
            // since 2.2.2
            $list->sendQueueRequestSyncCustomFields();
        }

        $record = [];

        if (!empty($result['fields'])) {
            /** @var ListField $field */
            $field = current($result['fields']);

            $attributes = $field->getAttributes([
                'field_id', 'label', 'tag', 'help_text', 'description', 'default_value', 'required', 'visibility', 'sort_order',
            ]);
            $attributes['type'] = $field->type->getAttributes(['name', 'identifier', 'description']);
            $attributes['list'] = $field->list->getAttributes(['list_uid', 'display_name']);

            // since 1.3.6.2
            if (!empty($field->options)) {
                $attributes['options'] = [];
                foreach ($field->options as $option) {
                    $attributes['options'][$option->value] = $option->name;
                }
            }

            $record = CMap::mergeArray($record, $attributes);
        }

        $this->renderJson([
            'status' => 'success',
            'data'   => ['record' => $record],
        ]);
    }

    /**
     * Handles deleting an existing field.
     *
     * @param string $list_uid
     * @param int $field_id
     *
     * @return void
     * @throws CDbException
     * @throws CException
     */
    public function actionDelete($list_uid, $field_id)
    {
        if (!request()->getIsDeleteRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only DELETE requests allowed for this endpoint.'),
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

        $field = ListField::model()->findByAttributes([
            'field_id' => (int)$field_id,
            'list_id'  => (int)$list->list_id,
        ]);

        if (empty($field)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The field does not exist.'),
            ], 404);
            return;
        }

        if ($field->tag === 'EMAIL') {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The EMAIL field cannot be deleted.'),
            ], 422);
            return;
        }

        $field->delete();

        $this->renderJson([
            'status' => 'success',
        ]);
    }

    /**
     * @return void
     * @throws CException
     */
    public function actionList_field_types()
    {
        $data = [
            'count'  => 0,
            'records'=> [],
        ];

        $count = ListFieldType::model()->count();

        if ($count == 0) {
            $this->renderJson([
                'status' => 'success',
                'data'   => $data,
            ]);
            return;
        }

        $data['count'] = $count;

        /** @var ListFieldType[] $types */
        $types = ListFieldType::model()->findAll();

        foreach ($types as $type) {
            $data['records'][] = $type->getAttributes(['name', 'identifier', 'description']);
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
            $listUid = request()->getQuery('list_uid');

            $sql = '
                SELECT l.list_id, AVG(UNIX_TIMESTAMP(f.last_updated)) as `timestamp` 
                    FROM {{list}} l
                INNER JOIN {{list_field}} f ON f.list_id = l.list_id 
                WHERE l.list_uid = :uid AND l.customer_id = :cid
                GROUP BY l.list_id 
            ';
            $command = db()->createCommand($sql);
            $command->bindValue(':uid', $listUid, PDO::PARAM_STR);
            $command->bindValue(':cid', (int)user()->getId(), PDO::PARAM_INT);

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

    /**
     * The ListFieldBuilder component is using POST data to handle all the fields, thus we need to make the
     * POST data available for it in a format that it can understand
     *
     * @param Lists $list
     * @param ListField|null $listField
     * @return void
     * @throws CException
     */
    protected function buildPostDataFromRequest(Lists $list, ?ListField $listField = null): void
    {
        if (request()->getIsPostRequest()) {
            $attributes = (array)request()->getPost('', []);
        } elseif (request()->getIsPutRequest()) {
            $attributes = (array)request()->getPut('', []);
        } else {
            return;
        }

        $fieldType = $attributes['type'] ?? null;
        if (!empty($listField)) {
            $fieldType = $listField->type->identifier;
        }

        if (empty($fieldType)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The field type should be set by now. Something went wrong. Please re-check your payload.'),
            ], 422);
            return;
        }

        $typesMapping             = $this->getFieldIdentifierToClassMapping();
        $fieldIdentifierClassName = $typesMapping[$fieldType];

        if (empty($fieldIdentifierClassName)) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'The field type class identifier could not be found.'),
            ], 404);
            return;
        }

        $requestData = [];
        if (!empty($attributes['options'])) {
            $requestData[ListFieldOption::class][$fieldType][] = $attributes['options'];
        }
        unset($attributes['type'], $attributes['options']);

        // We don't want to update these attributes for the EMAIL
        if (!empty($listField) && $listField->tag === 'EMAIL') {
            unset($attributes['tag'], $attributes['required'], $attributes['visibility']);
        }

        if (!empty($listField)) {
            $attributes['field_id'] = $listField->field_id;
        }

        $requestData[$fieldIdentifierClassName][$fieldType][] = $attributes;

        $existingFields = $list->fields;
        foreach ($existingFields as $field) {
            // We should not add the field that we update since we will use the PUT data for it
            if (!empty($listField) && $field->field_id === $listField->field_id) {
                continue;
            }

            $fieldType                                            = $field->type->identifier;
            $fieldIdentifierClassName                             = $typesMapping[$fieldType];
            $requestData[$fieldIdentifierClassName][$fieldType][] = $field->attributes;

            $optionsModels = $field->options;
            $options       = [];
            if (!empty($optionsModels)) {
                foreach ($optionsModels as $option) {
                    $options[] = $option->attributes;
                }
                $requestData[ListFieldOption::class][$fieldType][] = $options;
            }
        }

        $_POST = array_merge($_POST, $requestData);
    }

    /**
     * @param Lists $list
     * @param ListFieldType $fieldTypeModel
     * @return array
     * @throws CException
     * @throws Exception
     */
    protected function processListFieldType(Lists $list, ListFieldType $fieldTypeModel): array
    {
        $status = 'success';

        /** @var ListFieldBuilderType[] $instances */
        $instances = [];
        $types     = [$fieldTypeModel];

        /** @var CWebApplication $app */
        $app = app();

        foreach ($types as $type) {
            if (empty($type->identifier) || !is_file((string)Yii::getPathOfAlias($type->class_alias) . '.php')) {
                continue;
            }

            $component = $app->getWidgetFactory()->createWidget($this, $type->class_alias, [
                'fieldType' => $type,
                'list'      => $list,
            ]);

            if (!($component instanceof ListFieldBuilderType)) {
                continue;
            }

            // run the component to hook into next events
            $component->run();

            $instances[] = $component;
        }

        $fields      = [];
        $transaction = db()->beginTransaction();

        try {
            // raise event
            $this->callbacks->onListFieldsSave(new CEvent($this->callbacks, [
                'fields' => &$fields,
            ]));

            // if no error thrown but still there are errors in any of the instances, stop.
            foreach ($instances as $instance) {
                if (!empty($instance->errors)) {
                    throw new Exception(t('app', 'Your form has a few errors. Please fix them and try again!'));
                }
            }

            // raise event. at this point everything seems to be fine.
            $this->callbacks->onListFieldsSaveSuccess(new CEvent($this->callbacks, [
                'instances' => $instances,
            ]));

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            $status = 'error';
        }

        $errors = [];
        if ($status === 'error') {
            foreach ($instances as $instance) {
                foreach ($instance->errors as $instanceError) {
                    $errors[] = $instanceError['errors'] ?? [];
                }
            }
        }

        $fields = array_filter(array_map(function (array $item): ?ListField {
            /** @var ListField|null $field */
            $field = $item['field'] ?? null; // @phpstan-ignore-line
            return $field;
        // @phpstan-ignore-next-line
        }, isset($fields[$fieldTypeModel->identifier]) && is_array($fields[$fieldTypeModel->identifier]) ? $fields[$fieldTypeModel->identifier] : []));

        return [
            'status' => $status,
            'errors' => $errors,
            'fields' => $fields,
        ];
    }

    /**
     * @return array
     */
    protected function getFieldIdentifierToClassMapping(): array
    {
        $fieldTypes = ListFieldType::model()->findAll();

        $specialIdentifiers = ['text', 'checkbox', 'consentcheckbox', 'phonenumber', 'rating', 'textarea', 'url'];
        $mapping            = [];
        foreach ($fieldTypes as $type) {
            $listFieldClass = ListField::class;
            if (in_array($type->identifier, $specialIdentifiers)) {
                $listFieldClass = sprintf('%s%s', ListField::class, StringHelper::simpleCamelCase($type->name));
            }

            if (!is_file(Yii::getPathOfAlias(sprintf('common.models.%s', $listFieldClass)) . '.php')) {
                continue;
            }

            $mapping[$type->identifier] = $listFieldClass;
        }

        return $mapping;
    }
}
