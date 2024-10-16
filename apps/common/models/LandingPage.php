<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPage
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.1.20
 */

/**
 * This is the model class for table "landing_page".
 *
 * The followings are the available columns in table 'landing_page':
 * @property integer $page_id
 * @property integer|null $revision_id
 * @property integer $customer_id
 * @property integer|null $domain_id
 * @property string $slug
 * @property integer $visitors_count
 * @property integer $views_count
 * @property integer $conversions_count
 * @property string $has_unpublished_changes
 * @property string $status
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 *
 * The followings are the available model relations:
 * @property LandingPageRevision[] $revisions
 * @property LandingPageRevision|null $publishedRevision
 * @property LandingPageRevision|null $lastRevision
 * @property LandingPageDomain|null $domain
 */
class LandingPage extends ActiveRecord
{
    use ModelHashIdsTrait;

    /**
     * Flags for various statuses
     */
    const STATUS_PUBLISHED = 'published';
    const STATUS_UNPUBLISHED = 'unpublished';

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $pageType;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['status, has_unpublished_changes', 'required'],
            ['status', 'in', 'range' => [self::STATUS_PUBLISHED, self::STATUS_UNPUBLISHED]],
            ['has_unpublished_changes', 'in', 'range' => array_keys($this->getYesNoOptions())],
            ['slug', 'length', 'max' => 255],

            ['domain_id', 'length', 'max' => 11],
            ['domain_id', 'numerical', 'integerOnly' => true, 'min' => 0],
            ['domain_id', 'exist', 'className' => LandingPageDomain::class, 'attributeName' => 'domain_id'],

            // The following rule is used by search().
            ['status, has_unpublished_changes, slug, title, description, pageType', 'safe', 'on' => 'search'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'revisions'         => [self::HAS_MANY, LandingPageRevision::class, 'page_id'],
            'publishedRevision' => [self::BELONGS_TO, LandingPageRevision::class, 'revision_id'],
            'lastRevision'      => [
                self::HAS_ONE,
                LandingPageRevision::class,
                'page_id',
                'order' => 'lastRevision.revision_id DESC',
            ],
            'domain'            => [self::BELONGS_TO, LandingPageDomain::class, 'domain_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'page_id'                 => $this->t('Landing page'),
            'revision_id'             => $this->t('Revision'),
            'customer_id'             => $this->t('Customer'),
            'domain_id'               => $this->t('Domain'),
            'slug'                    => $this->t('Slug'),
            'has_unpublished_changes' => $this->t('Has unpublished changes'),
            'visitors_count'          => $this->t('Visitors count'),
            'views_count'             => $this->t('Views count'),
            'conversions_count'       => $this->t('Conversions count'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'domain_id' => $this->t('The domain that will be used for the landing page url, must be a DNS CNAME of the master domain.'),
        ];
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'slug' => $this->t('my-landing-page-title'),
        ];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
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
     *
     * @throws CException
     */
    public function search()
    {
        $criteria       = new CDbCriteria();
        $criteria->with = [];

        $criteria->compare('customer_id', $this->customer_id);
        $criteria->compare('status', $this->status);
        $criteria->compare('has_unpublished_changes', $this->has_unpublished_changes);
        $criteria->compare('slug', $this->slug, true);

        if (!empty($this->title)) {
            $criteria->with['lastRevision'] = [
                'condition' => 'lastRevision.title LIKE :title',
                'params'    => [':title' => '%' . $this->title . '%'],
            ];
        }

        if (!empty($this->description)) {
            $criteria->with['lastRevision'] = [
                'condition' => 'lastRevision.description LIKE :desc',
                'params'    => [':desc' => '%' . $this->description . '%'],
            ];
        }

        if (!empty($this->pageType)) {
            $criteria->with['lastRevision'] = [
                'condition' => 'lastRevision.page_type = :type',
                'params'    => [':type' => $this->pageType],
            ];
        }

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'       => [
                'defaultOrder' => [
                    'page_id' => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return LandingPage the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var LandingPage $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return string
     */
    public function getTranslationCategory(): string
    {
        return 'landing_pages';
    }

    /**
     * @param string $title
     * @return string
     */
    public function generateSlug(string $title): string
    {
        $string  = !empty($this->slug) ? $this->slug : $title;
        $slug    = URLify::filter((string)$string);
        $page_id = (int)$this->page_id;

        $criteria = new CDbCriteria();
        $criteria->addCondition('page_id != :id AND slug = :slug');
        $criteria->params = [':id' => $page_id, ':slug' => $slug];
        $exists           = self::model()->find($criteria);

        $i = 0;
        while (!empty($exists)) {
            ++$i;
            $slug     = preg_replace('/^(.*)(\d+)$/six', '$1', $slug);
            $slug     = URLify::filter($slug . ' ' . $i);
            $criteria = new CDbCriteria();
            $criteria->addCondition('page_id != :id AND slug = :slug');
            $criteria->params = [':id' => $page_id, ':slug' => $slug];
            $exists           = self::model()->find($criteria);
        }

        return $slug;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title = !empty($this->lastRevision) ? $this->lastRevision->title : '';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description = !empty($this->lastRevision) ? $this->lastRevision->description : '';
    }

    /**
     * @param int $length
     * @return string
     */
    public function getShortDescription(int $length = 200): string
    {
        return $this->getExcerpt($length);
    }

    /**
     * @return array
     */
    public function getDomainsArray(): array
    {
        /** @var array<string, string> $_options */
        static $_options = [];
        if (!empty($_options)) {
            return $_options;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('verified', LandingPageDomain::TEXT_YES);
        $criteria->compare('customer_id', (int)$this->customer_id);

        $criteria->order = 'domain_id DESC';
        $models = LandingPageDomain::model()->findAll($criteria);

        $_options[''] = $this->getAppDomainNameWithScheme() . '/' . $this->getUrlSuffix();
        foreach ($models as $model) {
            $_options[(string)$model->domain_id] = $model->getDomainNameWithSchema() . '/' . $this->getUrlSuffix();
        }

        return $_options;
    }

    /**
     *
     * @return string
     */
    public function getPermalink(): string
    {
        return sprintf('%s/%s', $this->getCurrentDomainNameWithScheme(), $this->getUrlSuffix());
    }

    /**
     * @return string
     */
    public function getCurrentDomainNameWithScheme(): string
    {
        $currentDomainNameWithScheme = $this->getAppDomainNameWithScheme();

        if (empty($this->domain_id) || $this->getIsUnpublished()) {
            return $currentDomainNameWithScheme;
        }

        $domain = $this->domain;
        if (empty($domain)) {
            return $currentDomainNameWithScheme;
        }

        $domainName = $domain->getDomainNameWithSchema();
        $domainName = parse_url($domainName, PHP_URL_HOST);

        if (empty($domainName)) {
            return $currentDomainNameWithScheme;
        }

        return sprintf('%s://%s', $domain->scheme, $domainName);
    }

    /**
     * @return string
     */
    public function getUrlSuffix(): string
    {
        return ltrim(apps()->getAppUrl('frontend', sprintf('lp/%s-%s', $this->getHashId(), $this->slug)), '/');
    }

    /**
     * @return string
     */
    public function getAppDomainNameWithScheme(): string
    {
        /** @var OptionUrl $optionUrl */
        $optionUrl        = container()->get(OptionUrl::class);
        $appDomainName    = parse_url($optionUrl->getFrontendUrl(), PHP_URL_HOST);
        $appDomainScheme  = parse_url($optionUrl->getFrontendUrl(), PHP_URL_SCHEME);

        return sprintf('%s://%s', $appDomainScheme, $appDomainName);
    }

    /**
     * @return string
     */
    public function getPageType(): string
    {
        return $this->pageType = !empty($this->lastRevision) ? $this->lastRevision->page_type : '';
    }

    /**
     * @return string
     */
    public function getPageTypeText(): string
    {
        return LandingPageRevision::getPageTypeArray()[$this->getPageType()] ?? $this->getPageType();
    }

    /**
     * @return string
     */
    public function getPublishedTitle(): string
    {
        return !empty($this->revision_id) ? (!empty($this->publishedRevision) ? $this->publishedRevision->title : '') : '';
    }

    /**
     * @return string
     */
    public function getPublishedDescription(): string
    {
        return !empty($this->revision_id) ? (!empty($this->publishedRevision) ? $this->publishedRevision->description : '') : '';
    }

    /**
     *
     * @return string
     */
    public function getPublishedPageType(): string
    {
        return !empty($this->revision_id) ? (!empty($this->publishedRevision) ? $this->publishedRevision->page_type : '') : '';
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function getExcerpt(int $length = 200): string
    {
        return StringHelper::truncateLength($this->getDescription(), $length);
    }

    /**
     * @return array
     */
    public function getStatusesArray(): array
    {
        return [
            ''                       => t('app', 'Choose'),
            self::STATUS_PUBLISHED   => $this->t('Published'),
            self::STATUS_UNPUBLISHED => $this->t('Unpublished'),
        ];
    }

    /**
     * @return string
     */
    public function getStatusText(): string
    {
        return $this->getStatusesArray()[$this->status] ?? $this->status;
    }

    /**
     * @return bool
     */
    public function getIsUnpublished(): bool
    {
        return $this->status === self::STATUS_UNPUBLISHED;
    }

    /**
     * @return bool
     */
    public function getIsPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * @return bool
     */
    public function getHasUnpublishedChanges(): bool
    {
        return $this->has_unpublished_changes === self::TEXT_YES;
    }

    /**
     * @return bool
     */
    public function getIsStandard(): bool
    {
        return $this->getPageType() === LandingPageRevision::PAGE_TYPE_STANDARD;
    }

    /**
     * @return string
     */
    public function getPublishedAt(): string
    {
        $publishedRevision = $this->publishedRevision;

        if (empty($publishedRevision)) {
            return (string)$this->date_added;
        }

        return (string)$publishedRevision->date_added;
    }

    /**
     * @return LandingPageRevisionVariant|null
     */
    public function pickPublishedVariant(): ?LandingPageRevisionVariant
    {
        if ($this->getIsStandard()) {
            // We have only 1 active variant for a standard page that will be used as the default one
            // When we will add the AB Test type, all the active variants will be part of the test.
            // Something like the published status, but we will pick them up based on the weight
            // TODO - See how the current_champion flag will play its part
            $revision = $this->publishedRevision;

            if (empty($revision)) {
                return null;
            }

            return $revision->activeVariant;
        }

        return null;
    }

    /**
     * @return LandingPageRevisionVariant|null
     */
    public function pickLastRevisionVariant(): ?LandingPageRevisionVariant
    {
        if ($this->getIsStandard()) {
            // We have only 1 active variant for a standard page that will be used as the default one
            // When we will add the AB Test type, all the active variants will be part of the test.
            // Something like the published status, but we will pick them up based on the weight
            // TODO - See how the current_champion flag will play its part
            $revision = $this->lastRevision;

            if (empty($revision)) {
                return null;
            }

            return $revision->activeVariant;
        }

        return null;
    }

    /**
     * This is meant to be run in a transaction
     * It returns either the last revision if the page is unpublished, either a new one having all the variants except
     * the ones in the exclude list
     *
     * @param array $excludedVariantIds
     * @return LandingPageRevision|null
     * @throws CDbException
     */
    public function getRevisionFromLastRevision(array $excludedVariantIds = []): ?LandingPageRevision
    {
        $lastRevision = $this->lastRevision;

        if (empty($lastRevision)) {
            return null;
        }

        // If unpublished we edit only the lastRevision
        if ($this->getIsUnpublished()) {
            return $lastRevision;
        }

        return $this->copyRevision($lastRevision, $excludedVariantIds);
    }

    /**
     * @param LandingPageRevision $sourceRevision
     * @param array $excludedVariantIds
     * @return LandingPageRevision|null
     * @throws CDbException
     */
    public function copyRevision(LandingPageRevision $sourceRevision, array $excludedVariantIds = []): ?LandingPageRevision
    {
        // We create a new revision having the source revision variants except the ones in the excludedVariantIds
        $revision               = new LandingPageRevision();
        $revision->attributes   = $sourceRevision->attributes;
        $revision->page_id      = $this->page_id;
        $revision->created_from = $sourceRevision->revision_id;
        $revision->resetCounters();

        try {
            if (!$revision->save()) {
                return null;
            }

            $sourceRevisionVariants = $sourceRevision->variants;
            foreach ($sourceRevisionVariants as $variantModel) {

                // We skip the one that we've just received
                if (!empty($excludedVariantIds) && in_array((int)$variantModel->variant_id, $excludedVariantIds)) {
                    continue;
                }
                $newVariantModel = $variantModel->copy();
                $newVariantModel->revision_id  = $revision->revision_id;

                if (!$newVariantModel->save()) {
                    return null;
                }

                if (!$newVariantModel->copyTrackUrlsFromVariant($variantModel)) {
                    return null;
                }
            }
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $revision;
    }

    /**
     * @param bool $save
     * @return LandingPage
     * @throws CDbException
     */
    public function resetCounters(bool $save = false): self
    {
        if (!$save) {
            $this->views_count       = 0;
            $this->visitors_count    = 0;
            $this->conversions_count = 0;
            return $this;
        }

        $this->saveAttributes([
            'views_count'       => 0,
            'visitors_count'    => 0,
            'conversions_count' => 0,
        ]);

        return $this;
    }
}
