<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageDomain
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.3
 */

/**
 * This is the model class for table "{{landing_page_domain}}".
 *
 * The followings are the available columns in table '{{landing_page_domain}}':
 * @property integer $domain_id
 * @property integer|string $customer_id
 * @property string $name
 * @property string $scheme
 * @property string $verified
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 *
 * The followings are the available model relations:
 * @property LandingPage[] $landingPages
 * @property Customer $customer
 */
class LandingPageDomain extends ActiveRecord
{
    /**
     * Flag for http scheme
     */
    const SCHEME_HTTP = 'http';

    /**
     * Flag for https scheme
     */
    const SCHEME_HTTPS = 'https';

    /**
     * @var int - whether we should skip dns verification.
     */
    public $skipVerification = 0;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page_domain}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['name, scheme', 'required'],
            ['name', 'length', 'max'=> 255],
            ['name', 'unique'],
            ['name', '_validateDomainCname'],
            ['scheme', 'in', 'range' => array_keys($this->getSchemesList())],

            // The following rule is used by search().
            ['name, verified', 'safe', 'on'=>'search'],

            ['scheme, skipVerification', 'safe'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'landingPages' => [self::HAS_MANY, LandingPage::class, 'domain_id'],
            'customer'     => [self::BELONGS_TO, Customer::class, 'customer_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'domain_id'         => t('landing_page_domains', 'Domain ID'),
            'customer_id'       => t('landing_page_domains', 'Customer'),
            'name'              => t('landing_page_domains', 'Name'),
            'scheme'            => t('landing_page_domains', 'Scheme'),
            'verified'          => t('landing_page_domains', 'Verified'),
            'skipVerification'  => t('landing_page_domains', 'Skip verification'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'name' => t('landing_page_domains', 'subdomain.your-domain.com'),
        ];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'skipVerification'  => t('landing_page_domains', 'Please DO NOT SKIP verification unless you are 100% sure you know what you are doing.'),
            'scheme'            => t('landing_page_domains', 'Choose HTTPS only if your tracking domain can also provide a valid SSL certificate, otherwise stick to regular HTTP.'),
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
        $criteria->with = [];

        if (!empty($this->customer_id)) {
            $customerId = (string)$this->customer_id;
            if (is_numeric($customerId)) {
                $criteria->compare('t.customer_id', $customerId);
            } else {
                $criteria->with = [
                    'customer' => [
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'CONCAT(customer.first_name, " ", customer.last_name) LIKE :name',
                        'params'    => [
                            ':name'    => '%' . $customerId . '%',
                        ],
                    ],
                ];
            }
        }

        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.scheme', $this->scheme);
        $criteria->compare('t.verified', $this->verified);

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'=>[
                'defaultOrder' => [
                    't.domain_id'  => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return LandingPageDomain the static model class
     */
    public static function model($className=__CLASS__)
    {
        /** @var LandingPageDomain $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return array
     */
    public function getSchemesList(): array
    {
        return [
            self::SCHEME_HTTP  => 'HTTP',
            self::SCHEME_HTTPS => 'HTTPS',
        ];
    }

    /**
     * @return bool
     */
    public function getIsVerified(): bool
    {
        return !empty($this->verified) && (string)$this->verified === self::TEXT_YES;
    }

    /**
     * @return string
     */
    public function getDomainNameWithSchema(): string
    {
        return strpos($this->name, 'http') !== 0 ? sprintf('%s://%s', $this->scheme ?: self::SCHEME_HTTP, $this->name) : $this->name;
    }

    /**
     * @return string
     */
    public function getAppCurrentDomainName(): string
    {
        /** @var OptionUrl $optionUrlModel */
        $optionUrlModel = container()->get(OptionUrl::class);

        $currentDomainName = parse_url($optionUrlModel->getFrontendUrl(), PHP_URL_HOST);
        if (empty($currentDomainName)) {
            return '';
        }

        return (string)$currentDomainName;
    }

    /**
     * @return bool
     * @throws Net_DNS2_Exception
     */
    public function hasValidDNSRecords(): bool
    {
        // make sure we properly extract the tracking domain name
        $domainName = strpos($this->name, 'http') !== 0 ? 'https://' . $this->name : $this->name;
        $domainName = parse_url($domainName, PHP_URL_HOST);
        if (empty($domainName)) {
            return false;
        }

        // get the application domain name, this is where the CNAME/A must point
        $currentDomainName = $this->getAppCurrentDomainName();

        $resolver = new Net_DNS2_Resolver([
            'nameservers' => DnsHelper::getDnsResolverNameservers(),
        ]);

        // first, get the cname record
        $result = $resolver->query($domainName, 'CNAME');

        // if the cname is valid, there is nothing else to do, we found it, and we stop
        $count = count(array_filter($result->answer, function ($record) use ($currentDomainName): bool {
            if (!($record instanceof Net_DNS2_RR_CNAME)) {
                return false;
            }
            return (string)$record->cname === (string)$currentDomainName;
        }));

        if ($count > 0) {
            return true;
        }

        // we need to query the list of IP addresses the current domain has
        $result = $resolver->query($currentDomainName, 'A');

        $ipAddresses = array_filter(array_unique(array_map(function ($record): string {
            if (!($record instanceof Net_DNS2_RR_A)) {
                return '';
            }
            return (string)$record->address;
        }, $result->answer)));

        // now we can query the tracking domain, if it is not a CNAME maybe it is an "A" record
        $result = $resolver->query($domainName, 'A');

        // if any of its IP addresses matches the ones pointing to this domain, we're okay, so we can stop
        $count = count(array_filter($result->answer, function ($record) use ($ipAddresses): bool {
            if (!($record instanceof Net_DNS2_RR_A)) {
                return false;
            }
            return in_array((string)$record->address, $ipAddresses);
        }));

        if ($count > 0) {
            return true;
        }

        // at this point, we were not able to find the dns records
        return false;
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function _validateDomainCname(string $attribute, array $params = []): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $this->verified = self::TEXT_YES;

        if ($this->skipVerification) {
            return;
        }

        $domainName = strpos($this->$attribute, 'http') !== 0 ? 'https://' . $this->$attribute : $this->$attribute;
        $domainName = parse_url($domainName, PHP_URL_HOST);
        if (empty($domainName)) {
            $this->verified = self::TEXT_NO;
            $this->addError($attribute, t('landing_page_domains', 'Your specified domain name does not seem to be valid!'));
            return;
        }

        try {
            $valid = $this->hasValidDNSRecords();
        } catch (Net_DNS2_Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            $valid = false;
        }

        if (!$valid) {
            $this->verified = self::TEXT_NO;
            $this->addError($attribute, t('landing_page_domains', 'Cannot find a valid CNAME record for {domainName}! Remember, the CNAME of {domainName} must point to {currentDomain}!', [
                '{domainName}'    => $domainName,
                '{currentDomain}' => $this->getAppCurrentDomainName(),
            ]));
        }
    }
}
