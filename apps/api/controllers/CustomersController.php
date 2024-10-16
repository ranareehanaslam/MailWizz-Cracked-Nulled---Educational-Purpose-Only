<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CustomersController
 *
 * Handles the CRUD actions for customers.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */

class CustomersController extends Controller
{
    /**
     * @return array
     */
    public function accessRules()
    {
        return [
            // allow all users on all actions for now
            ['allow'],
        ];
    }

    /**
     * Handles the creation of a new customer if registration is enabled.
     *
     * @return void
     * @throws CDbException
     * @throws CException
     * @throws ReflectionException
     */
    public function actionCreate()
    {
        if (!request()->getIsPostRequest()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Only POST requests allowed for this endpoint.'),
            ], 400);
            return;
        }

        /** @var OptionCustomerRegistration $registration */
        $registration = container()->get(OptionCustomerRegistration::class);

        if (!$registration->getIsEnabled()) {
            $this->renderJson([
                'status'    => 'error',
                'error'     => t('api', 'Customer creation is disabled.'),
            ], 400);
            return;
        }

        $customer     = new Customer('register');
        $company      = new CustomerCompany('register');
        $customerPost = (array)request()->getPost('customer', []);
        $companyPost  = (array)request()->getPost('company', []);

        if (isset($customerPost['password'])) {
            $customerPost['fake_password'] = $customerPost['password'];
            unset($customerPost['password']);
        }

        $customer->attributes = $customerPost;
        $customer->tc_agree   = '1';
        $customer->status     = Customer::STATUS_ACTIVE;
        $companyRequired      = $registration->getIsCompanyRequired();

        $requireApproval = $registration->getRequireApproval();
        if ($requireApproval) {
            $customer->status = Customer::STATUS_PENDING_ACTIVE;
        }

        $mustConfirmEmail = $registration->getRequireEmailConfirmation();
        if ($mustConfirmEmail) {
            $customer->status = Customer::STATUS_PENDING_CONFIRM;
        }

        if ($group = $registration->getDefaultGroup()) {
            $customer->group_id = (int)$group->group_id;
        }

        if (!$customer->save()) {
            $errors = $customer->shortErrors->getAll();

            // since 2.2.19
            if (isset($errors['fake_password'])) {
                $errors['password'] = $errors['fake_password'];
                unset($errors['fake_password']);
            }

            $this->renderJson([
                'status'    => 'error',
                'error'     => [
                    'general' => $errors,
                ],
            ], 422);
            return;
        }

        if ($companyRequired) {
            $country = null;
            if (!empty($companyPost['country'])) {
                $country = Country::model()->findByAttributes(['name' => $companyPost['country']]);
                if (empty($country)) {
                    $customer->delete();
                    $this->renderJson([
                        'status'    => 'error',
                        'error'     => [
                            'company' => [
                                'country_id' => t('api', 'Unable to find the specified country, please double check the spelling!'),
                            ],
                        ],
                    ], 422);
                    return;
                }
                $companyPost['country_id'] = (int)$country->country_id;
                unset($companyPost['country']);
            }
            if (!empty($companyPost['zone'])) {
                if (!empty($country)) {
                    $zone = Zone::model()->findByAttributes([
                        'country_id' => $country->country_id,
                        'name'       => $companyPost['zone'],
                    ]);
                    if (empty($zone)) {
                        $customer->delete();
                        $this->renderJson([
                            'status'    => 'error',
                            'error'     => [
                                'company' => [
                                    'zone_id' => t('api', 'Unable to find the specified zone, please double check the spelling!'),
                                ],
                            ],
                        ], 422);
                        return;
                    }
                    $companyPost['zone_id'] = (int)$zone->zone_id;
                }
                unset($companyPost['zone']);
            }

            $company->attributes  = $companyPost;
            $company->customer_id = (int)$customer->customer_id;

            if (!$company->save()) {
                $customer->delete();
                $this->renderJson([
                    'status'    => 'error',
                    'error'     => [
                        'company' => $company->shortErrors->getAll(),
                    ],
                ], 422);
                return;
            }
        }

        if ($mustConfirmEmail) {
            $this->_sendRegistrationConfirmationEmail($customer, $company);
        }
        $this->_sendNewCustomerNotifications($customer, $company);

        // 1.3.7
        $listSubscribeResult = $this->_subscribeToEmailList($customer);

        $this->renderJson([
            'status'                => 'success',
            'customer_uid'          => $customer->customer_uid,
            'must_confirm_email'    => $mustConfirmEmail,
            'require_approval'      => $requireApproval,
            'list_subscribe_result' => $listSubscribeResult,
        ], 201);
    }

    /**
     * Callback after success registration to send the confirmation email
     *
     * @param Customer $customer
     * @param CustomerCompany $company
     *
     * @return void
     * @throws CException
     */
    protected function _sendRegistrationConfirmationEmail(Customer $customer, CustomerCompany $company)
    {
        /** @var OptionCommon */
        $common = container()->get(OptionCommon::class);

        /** @var OptionCustomerRegistration */
        $registration = container()->get(OptionCustomerRegistration::class);

        /** @var OptionUrl */
        $url = container()->get(OptionUrl::class);

        if ($registration->getIsCompanyRequired() && $company->isNewRecord) {
            return;
        }

        $params = CommonEmailTemplate::getAsParamsArrayBySlug(
            'customer-confirm-registration',
            [
                'subject' => t('customers', 'Please confirm your account!'),
            ],
            [
                '[CONFIRMATION_URL]' => $url->getCustomerUrl('guest/confirm-registration/' . $customer->confirmation_key),
            ]
        );

        $email = new TransactionalEmail();
        $email->to_name     = $customer->getFullName();
        $email->to_email    = $customer->email;
        $email->from_name   = $common->getSiteName();
        $email->subject     = $params['subject'];
        $email->body        = $params['body'];
        $email->save();
    }

    /**
     * Callback after success registration to send the notification emails to admin users
     *
     * @param Customer $customer
     * @param CustomerCompany $company
     *
     * @return void
     * @throws CException
     */
    protected function _sendNewCustomerNotifications(Customer $customer, CustomerCompany $company)
    {
        /** @var OptionCommon */
        $common = container()->get(OptionCommon::class);

        /** @var OptionCustomerRegistration */
        $registration = container()->get(OptionCustomerRegistration::class);

        /** @var OptionUrl */
        $url = container()->get(OptionUrl::class);

        if (!($recipients = $registration->getNewCustomersRegistrationNotificationTo())) {
            return;
        }

        $customerInfo = [];
        foreach ($customer->getAttributes(['first_name', 'last_name', 'email']) as $attributeName => $attributeValue) {
            $customerInfo[] = $customer->getAttributeLabel($attributeName) . ': ' . $attributeValue;
        }
        $customerInfo = implode('<br />', $customerInfo);

        $params = CommonEmailTemplate::getAsParamsArrayBySlug(
            'new-customer-registration',
            [
                'subject' => t('customers', 'New customer registration!'),
            ],
            [
                '[CUSTOMER_URL]' => $url->getBackendUrl('customers/update/id/' . $customer->customer_id),
                '[CUSTOMER_INFO]'=> $customerInfo,
            ]
        );

        foreach ($recipients as $recipient) {
            if (!FilterVarHelper::email($recipient)) {
                continue;
            }
            $email = new TransactionalEmail();
            $email->to_name     = $recipient;
            $email->to_email    = $recipient;
            $email->from_name   = $common->getSiteName();
            $email->subject     = $params['subject'];
            $email->body        = $params['body'];
            $email->save();
        }
    }

    /**
     * @param Customer $customer
     * @return array
     * @throws ReflectionException
     */
    protected function _subscribeToEmailList(Customer $customer): array
    {
        /** @var OptionCustomerRegistration $registration */
        $registration = container()->get(OptionCustomerRegistration::class);

        $result = ['status' => 'disabled', 'message' => ''];

        $apiEnabled = $registration->getApiEnabled();
        if (empty($apiEnabled)) {
            return CMap::mergeArray($result, [
                'status' => 'disabled',
                'message' => t('api', 'Feature is not enabled.'),
            ]);
        }

        $apiUrl         = (string)$registration->api_url;
        $apiKey         = (string)$registration->api_key;
        $listUids       = (string)$registration->api_list_uid;
        $consentText    = $registration->getApiConsentText();

        if (empty($apiUrl) || empty($apiKey) || empty($listUids)) {
            return CMap::mergeArray($result, [
                'status' => 'error',
                'message' => t('api', 'Please make sure the API details are properly set.'),
            ]);
        }

        if (!empty($consentText) && (empty($customer->newsletter_consent) || $consentText != $customer->newsletter_consent)) {
            return CMap::mergeArray($result, [
                'status' => 'error',
                'message' => t('api', 'Provided consent text does not match expected context text.'),
            ]);
        }

        \EmsApi\Base::setConfig(new \EmsApi\Config([
            'apiUrl'    => $apiUrl,
            'apiKey'    => $apiKey,
        ]));

        $lists    = CommonHelper::getArrayFromString((string)$listUids, ',');
        $endpoint = new \EmsApi\Endpoint\ListSubscribers();

        foreach ($lists as $list) {
            $endpoint->create($list, [
                'EMAIL'    => $customer->email,
                'FNAME'    => $customer->first_name,
                'LNAME'    => $customer->last_name,
                'CONSENT'  => $customer->newsletter_consent,
                'details'  => [
                    'ip_address' => (string)request()->getUserHostAddress(),
                    'user_agent' => StringHelper::truncateLength((string)request()->getUserAgent(), 255),
                ],
            ]);
        }

        return CMap::mergeArray($result, [
            'status' => 'success',
            'message' => t('api', 'The information has been sent to the email list.'),
        ]);
    }
}
