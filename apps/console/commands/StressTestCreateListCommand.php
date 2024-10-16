<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * StressTestCreateListCommand
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.2
 */

class StressTestCreateListCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        // customer
        $customers = CustomerCollection::findAll()->all();

        echo 'Please select a customer:' . PHP_EOL;
        echo collect($customers)->map(function (Customer $customer, int $index) {
            return sprintf('[%d] - %s(%s)', $index, $customer->getFullName(), $customer->email);
        })->join(PHP_EOL) . PHP_EOL;

        while (true) {
            $choice = $this->prompt('Please enter your selection: ', '0');
            if (isset($customers[(int)$choice])) {
                break;
            }
        }

        /** @var Customer $customer */
        $customer = collect($customers)->get((int)$choice);

        $fieldTypesMapping = $this->getFieldsTypeMapping();

        $fieldTypeEmail = collect($fieldTypesMapping)->filter(function (array $item): bool {
            return $item['displayType'] === 'email';
        })->first();

        $fieldsToAdd = [
            ['label' => 'Email', 'tag' => 'EMAIL', 'type' => $fieldTypeEmail, 'field_id' => null],
        ];

        while (true) {
            $choice = $this->confirm('Beside the EMAIL field, should this list contain other custom fields?');
            if (!$choice) {
                break;
            }

            while (true) {
                echo 'Adding field: ' . PHP_EOL;

                $label = '';
                while (!$label) {
                    $label = $this->prompt('Enter the field label', '');
                }

                $fieldType = '';
                while (trim((string)$fieldType) === '') {
                    echo 'Available field types: ' . PHP_EOL;
                    echo collect($fieldTypesMapping)->map(function (array $item, int $index): string {
                        return sprintf('[%d] - %s', $index, $item['displayType']);
                    })->join(PHP_EOL) . PHP_EOL;
                    $fieldType = (string) $this->prompt('Please enter your selection: ', '');
                }

                $type = $fieldTypesMapping[(int)$fieldType] ?? null;
                if (!$type) {
                    echo 'You have provided an invalid field type.' . PHP_EOL;
                    continue;
                }

                $fieldsToAdd[] = [
                    'label'     => $label,
                    'tag'       => StringHelper::getTagFromString((string)$label),
                    'type'      => $type,
                    'field_id'  => null,
                ];

                if (!$this->confirm('Add a new field?')) {
                    break;
                }
            }

            break;
        }

        echo sprintf(
            "You are going to create a new email list for %s having following fields:\n%s",
            sprintf('%s (id: %d / email: %s)', $customer->getFullName(), $customer->customer_id, $customer->email),
            collect($fieldsToAdd)->map(function (array $item): string {
                return sprintf('Label: %s, Type: %s, Tag: %s', $item['label'], $item['type']['displayType'], $item['tag']);
            })->join(PHP_EOL) . PHP_EOL
        );
        if (!$this->confirm('Continue?')) {
            return 0;
        }

        $subscribersCount = 0;
        while ($subscribersCount <= 0 || $subscribersCount > 1000000) {
            $subscribersCount = (int) $this->prompt('How many subscribers should we add in this list?', '1000');
        }
        if (!$this->confirm(sprintf('We are going to insert %s subscribers in this list, continue?', formatter()->formatNumber($subscribersCount)))) {
            return 0;
        }

        $faker = Faker\Factory::create();

        $transaction = db()->beginTransaction();
        try {
            $list = new Lists();
            $list->customer_id = $customer->customer_id;
            $list->name = $faker->sentence(3);
            $list->display_name = $faker->sentence(3);
            $list->description = $faker->sentence();
            $list->visibility = Lists::VISIBILITY_PUBLIC;
            $list->opt_in = Lists::OPT_IN_DOUBLE;
            $list->opt_out = Lists::OPT_OUT_SINGLE;
            $list->welcome_email = Lists::TEXT_NO;
            $list->removable = Lists::TEXT_YES;
            $list->subscriber_require_approval = Lists::TEXT_NO;
            $list->status = Lists::STATUS_ACTIVE;

            if (!$list->save()) {
                throw new \Exception(sprintf("Creating the list failed with:\n%s", $list->shortErrors->getAllAsString()));
            }

            $listDefault = new ListDefault();
            $listDefault->list_id = (int)$list->list_id;
            $listDefault->from_name = $faker->name();
            $listDefault->from_email = $faker->email();
            $listDefault->reply_to = $faker->email();

            if (!$listDefault->save()) {
                throw new \Exception(sprintf("Creating the list default failed with:\n%s", $listDefault->shortErrors->getAllAsString()));
            }

            $listCompany = new ListCompany();
            $listCompany->list_id = (int)$list->list_id;
            $listCompany->country_id = 223;
            $listCompany->name = $faker->company();
            $listCompany->address_1 = $faker->address();
            $listCompany->city = $faker->city();
            $listCompany->zip_code = $faker->postcode();
            $listCompany->address_format = $listCompany->defaultAddressFormat;

            if (!$listCompany->save()) {
                throw new \Exception(sprintf("Creating the list company failed with:\n%s", $listCompany->shortErrors->getAllAsString()));
            }

            $listCustomerNotification = new ListCustomerNotification();
            $listCustomerNotification->list_id = (int)$list->list_id;
            $listCustomerNotification->daily = ListCustomerNotification::TEXT_NO;
            $listCustomerNotification->subscribe = ListCustomerNotification::TEXT_NO;
            $listCustomerNotification->unsubscribe = ListCustomerNotification::TEXT_NO;

            if (!$listCustomerNotification->save()) {
                throw new \Exception(sprintf("Creating the list company failed with:\n%s", $listCustomerNotification->shortErrors->getAllAsString()));
            }

            foreach ($fieldsToAdd as $index => $fieldToAdd) {
                /** @var ListFieldType|null $fieldType */
                $fieldType = ListFieldType::model()->findByAttributes([
                    'identifier' => $fieldToAdd['type']['internalType'],
                ]);
                if (empty($fieldType)) {
                    throw new Exception(sprintf('The filed type "%s" was not found!', $fieldToAdd['type']['internalType']));
                }

                $field = new ListField();
                $field->type_id = (int)$fieldType->type_id;
                $field->list_id = (int)$list->list_id;
                $field->label = (string)$fieldToAdd['label'];
                $field->tag = (string)$fieldToAdd['tag'];
                $field->required = ListField::TEXT_YES;
                $field->visibility = ListField::VISIBILITY_VISIBLE;
                $field->sort_order = $index;

                if (!$field->save()) {
                    throw new \Exception(sprintf("Creating the list fields failed with:\n%s", $field->shortErrors->getAllAsString()));
                }

                $fieldsToAdd[$index]['field_id'] = $field->field_id;
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();

            echo $e->getMessage() . PHP_EOL;
            return 1;
        }

        echo sprintf('The list(%s) has been created, we\'re populating it with subscribers...', $list->list_uid) . PHP_EOL;
        $count = 0;
        $percentDisplayed = [];
        while ($count < $subscribersCount) {
            $subscriber = new ListSubscriber();
            $subscriber->list_id = $list->list_id;
            $subscriber->status = ListSubscriber::STATUS_CONFIRMED;
            $subscriber->email = 'temp@example.com';
            $subscriber->ip_address = $faker->ipv4();
            $subscriber->source = ListSubscriber::SOURCE_WEB;
            $subscriber->save(false);

            foreach ($fieldsToAdd as $fieldToAdd) {
                $value = (string) call_user_func($fieldToAdd['type']['valueGenerator']);

                $fieldValue = $subscriber->createListFieldValueInstance();
                $fieldValue->field_id = (int)$fieldToAdd['field_id'];
                $fieldValue->subscriber_id = (int)$subscriber->subscriber_id;
                $fieldValue->value = $value;
                $fieldValue->save(false);

                if ($fieldToAdd['tag'] === 'EMAIL') {
                    $subscriber->email = $value;
                    $subscriber->save(false);
                }
            }

            $count++;

            $percent = intval(round($count / $subscribersCount * 100));
            if (($percent === 0 || $percent % 5 === 0) && !isset($percentDisplayed[$percent])) {
                $percentDisplayed[$percent] = true;
                echo sprintf('Current progress: %d%%', $percent) . PHP_EOL;
            }
        }

        echo 'Done!' . PHP_EOL;

        return 0;
    }

    private function getFieldsTypeMapping(): array
    {
        $faker = Faker\Factory::create();

        return [
            [
                'displayType'       => 'email',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->email();
                },
            ],
            [
                'displayType'       => 'firstName',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->firstName();
                },
            ],
            [
                'displayType'       => 'lastName',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->lastName();
                },
            ],
            [
                'displayType'       => 'fullName',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->name();
                },
            ],
            [
                'displayType'       => 'age',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): int {
                    return $faker->numberBetween(18, 90);
                },
            ],
            [
                'displayType'       => 'country',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->country();
                },
            ],
            [
                'displayType'       => 'booleanInteger',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): int {
                    return $faker->numberBetween(0, 1);
                },
            ],
            [
                'displayType'       => 'website',
                'internalType'      => 'url',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->url();
                },
            ],
            [
                'displayType'       => 'text',
                'internalType'      => 'text',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->sentence(3);
                },
            ],
            [
                'displayType'       => 'textarea',
                'internalType'      => 'textarea',
                'valueGenerator'    => function () use ($faker): string {
                    return $faker->sentence(10);
                },
            ],
        ];
    }
}
