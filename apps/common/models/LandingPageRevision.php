<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageRevision
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

/**
 * This is the model class for table "landing_page_revision".
 *
 * The followings are the available columns in table 'landing_page_revision':
 * @property integer $revision_id
 * @property integer $page_id
 * @property integer|null $created_from
 * @property string $page_type
 * @property string $title
 * @property string $description
 * @property string $redirect_url
 * @property string $redirect_status_code
 * @property integer $visitors_count
 * @property integer $views_count
 * @property integer $conversions_count
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 *
 * The followings are the available model relations:
 * @property LandingPage $page
 * @property LandingPageRevision $createdFrom
 * @property LandingPageUrl[] $urls
 * @property LandingPageRevisionVariant|null $activeVariant
 * @property LandingPageRevisionVariant[] $variants
 * @property LandingPageRevisionVariant[] $activeVariants
 * @property LandingPageRevisionVariant[] $inactiveVariants
 */
class LandingPageRevision extends ActiveRecord
{
    const PAGE_TYPE_STANDARD = 'standard';
    const PAGE_TYPE_AB_TEST = 'ab-test';

    /**
     * @var int|null
     */
    public $template_id;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page_revision}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['title', 'required'],
            ['title', 'length', 'max' => 200],
            ['description', 'length', 'max' => 255],
            ['page_type', 'in', 'range' => [self::PAGE_TYPE_STANDARD, self::PAGE_TYPE_AB_TEST]],
            ['template_id', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 1000000],
            // The following rule is used by search().
            ['title, description', 'safe', 'on' => 'search'],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'page'             => [self::BELONGS_TO, LandingPage::class, 'page_id'],
            'createdFrom'      => [self::BELONGS_TO, LandingPageRevision::class, 'created_from'],
            'urls'             => [self::HAS_MANY, LandingPageUrl::class, 'revision_id'],
            'variants'         => [self::HAS_MANY, LandingPageRevisionVariant::class, 'revision_id'],
            'activeVariant'    => [
                self::HAS_ONE,
                LandingPageRevisionVariant::class,
                'revision_id',
                'condition' => 'activeVariant.active = "yes"',
            ],
            'activeVariants'   => [
                self::HAS_MANY,
                LandingPageRevisionVariant::class,
                'revision_id',
                'condition' => 'activeVariants.active = "yes"',
            ],
            'inactiveVariants' => [
                self::HAS_MANY,
                LandingPageRevisionVariant::class,
                'revision_id',
                'condition' => 'inactiveVariants.active = "no"',
                'order'     => 'variant_id DESC',
            ],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'revision_id'          => $this->t('Landing page revision'),
            'page_id'              => $this->t('Landing page'),
            'title'                => $this->t('Title'),
            'description'          => $this->t('Description'),
            'page_type'            => $this->t('Page type'),
            'redirect_url'         => $this->t('Redirect url'),
            'redirect_status_code' => $this->t('Redirect status code'),
            'visitors_count'       => $this->t('Visitors count'),
            'views_count'          => $this->t('Views count'),
            'conversions_count'    => $this->t('Conversions count'),

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
     *
     * @throws CException
     */
    public function search()
    {
        $criteria = new CDbCriteria();

        $criteria->compare('title', $this->title, true);
        $criteria->compare('description', $this->description, true);

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'       => [
                'defaultOrder' => [
                    'revision_id' => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return LandingPageRevision the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var LandingPageRevision $model */
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
     * @return array
     */
    public static function getPageTypeArray(): array
    {
        return [
            ''                       => t('app', 'Choose'),
            self::PAGE_TYPE_STANDARD => t('landing_pages', 'Standard'),
            self::PAGE_TYPE_AB_TEST  => t('landing_pages', 'A/B Test'),
        ];
    }

    /**
     * @return string
     */
    public function getPageTypeText(): string
    {
        return self::getPageTypeArray()[$this->page_type] ?? $this->page_type;
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = [];
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'title' => $this->t('My landing page title'),
        ];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function getExcerpt(int $length = 200): string
    {
        return StringHelper::truncateLength($this->description, $length);
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        $attributes = $this->getAttributes(['title', 'description', 'page_type', 'redirect_url', 'redirect_status_code']);

        return sha1((string)json_encode($attributes));
    }

    /**
     * @param bool $save
     * @return LandingPageRevision
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

    /**
     * @return LandingPageRevisionVariant|null
     * @throws Exception
     */
    public function pickVariantToShow(): ?LandingPageRevisionVariant
    {
        $page = $this->page;
        if ($page->getIsStandard()) {
            return $this->activeVariant;
        }

        throw new Exception(t('landing pages', 'Not implemented'));
    }

    /**
     * @return array
     */
    public function getVariantsDropDownArray(): array
    {
        $_options = [];

        if (empty($this->revision_id)) {
            return $_options;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('t.revision_id', (int)$this->revision_id);
        $criteria->order = 't.title ASC';

        return $_options = LandingPageRevisionVariantCollection::findAll($criteria)->mapWithKeys(function (
            LandingPageRevisionVariant $variant
        ) {
            return [$variant->variant_id => $variant->title];
        })->all();
    }
}
