<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageRevisionVariant
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

/**
 * This is the model class for table "landing_page_revision_variant".
 *
 * The followings are the available columns in table 'landing_page_revision_variant':
 * @property integer|null $variant_id
 * @property integer $revision_id
 * @property integer|null $created_from
 * @property string $title
 * @property string $content
 * @property integer $weight
 * @property string $current_champion
 * @property string $active
 * @property integer $visitors_count
 * @property integer $views_count
 * @property integer $conversions_count
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 *
 * The followings are the available model relations:
 * @property LandingPageRevision $revision
 * @property LandingPageRevisionVariant $createdFrom
 * @property LandingPageUrl[] $urls
 */
class LandingPageRevisionVariant extends ActiveRecord
{
    use ModelHashIdsTrait;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page_revision_variant}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['title', 'required'],
            ['title', 'length', 'max' => 50],
            ['weight', 'numerical', 'integerOnly' => true],
            ['weight', 'length', 'min' => 0, 'max' => 100],

            ['active, current_champion', 'in', 'range' => [self::TEXT_YES, self::TEXT_NO]],
            // The following rule is used by search().
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'revision'    => [self::BELONGS_TO, LandingPageRevision::class, 'revision_id'],
            'createdFrom' => [self::BELONGS_TO, LandingPageRevisionVariant::class, 'created_from'],
            'urls'        => [self::HAS_MANY, LandingPageUrl::class, 'variant_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'variant_id'        => $this->t('Landing page variant'),
            'revision_id'       => $this->t('Revision'),
            'title'             => $this->t('Title'),
            'content'           => $this->t('Content'),
            'current_champion'  => $this->t('Current champion'),
            'active'            => $this->t('Active'),
            'visitors_count'    => $this->t('Visitors count'),
            'views_count'       => $this->t('Views count'),
            'conversions_count' => $this->t('Conversions count'),
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
        $criteria       = new CDbCriteria();
        $criteria->with = [];

        $criteria->compare('active', $this->active);
        $criteria->compare('current_champion', $this->current_champion);

        return new CActiveDataProvider(get_class($this), [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ],
            'sort'       => [
                'defaultOrder' => [
                    'variant_id' => CSort::SORT_DESC,
                ],
            ],
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return LandingPageRevisionVariant the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var LandingPageRevisionVariant $model */
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
        $placeholders = [];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @param bool $absolute
     *
     * @return string
     */
    public function getPermalink(bool $absolute = true): string
    {
        return apps()->getAppUrl(
            'frontend',
            sprintf('landing-page-variants/%s', $this->getHashId()),
            $absolute
        );
    }

    /**
     * Can be made inactive if it is active and there is one active variant other than this
     *
     * @return bool
     */
    public function getCanBeMadeInactive(): bool
    {
        if ($this->getIsInactive()) {
            return false;
        }
        $criteria = new CDbCriteria();
        $criteria->addCondition('variant_id != :variantId');
        $criteria->compare('active', self::TEXT_YES);
        $criteria->compare('revision_id', $this->revision_id);
        $criteria->params[':variantId'] = (int)$this->variant_id;

        return (int)self::model()->count($criteria) > 0;
    }

    /**
     * Can be deleted if there is at least one active variant except this
     *
     * @return bool
     */
    public function getCanBeDeleted(): bool
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('variant_id != :variantId');
        $criteria->compare('active', self::TEXT_YES);
        $criteria->compare('revision_id', $this->revision_id);
        $criteria->params[':variantId'] = (int)$this->variant_id;

        return (int)self::model()->count($criteria) > 0;
    }

    /**
     * @return bool
     */
    public function toggleActive(): bool
    {
        $revision = LandingPageRevision::model()->findByPk($this->revision_id);
        if (empty($revision)) {
            return false;
        }

        $page = LandingPage::model()->findByPk($revision->page_id);
        if (empty($page)) {
            return false;
        }

        if ($this->getIsInactive()) {
            $this->active = LandingPageRevisionVariant::TEXT_YES;
            if ($page->getIsStandard()) {
                self::model()->updateAll(
                    ['active' => self::TEXT_NO],
                    'variant_id != :variantId AND revision_id = :revisionId AND active = :active',
                    [
                        ':variantId'  => (int)$this->variant_id,
                        ':revisionId' => (int)$this->revision_id,
                        ':active'     => self::TEXT_YES,
                    ]
                );
            }

            if (!$this->save(false)) {
                return false;
            }
        } elseif ($this->getIsActive()) {
            $this->active = LandingPageRevisionVariant::TEXT_NO;
            if (!$this->getCanBeMadeInactive() || !$this->save(false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getIsPublished(): bool
    {
        if ($this->getIsNewRecord() || $this->getIsInactive()) {
            return false;
        }

        $revision = $this->revision;
        $page     = $revision->page;

        if ($page->getIsUnpublished()) {
            return false;
        }

        $publishedVariant = $page->pickPublishedVariant();
        if (empty($publishedVariant)) {
            return true;
        }

        // If this variant is the same as the published variant
        if ((int)$this->variant_id === (int)$publishedVariant->variant_id) {
            return true;
        }
        // We decided to take into account the content also, even if this is not the published variant
        // That is to accommodate the situation when we are doing an action that does not affect the published variant
        // content, but we are still issuing a new revision. Like editing an inactive variant, creating a new one, etc.
        return $this->getSignature() === $publishedVariant->getSignature();
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->active === self::TEXT_YES;
    }

    /**
     * @return bool
     */
    public function getIsInactive(): bool
    {
        return $this->active === self::TEXT_NO;
    }

    /**
     * @return string
     */
    public function getRelativeLastUpdated(): string
    {
        return (string)(new Carbon\Carbon((string)$this->last_updated))->diffForHumans();
    }

    /**
     * @return string
     */
    public function getRelativeDateAdded(): string
    {
        return (string)(new Carbon\Carbon((string)$this->date_added))->diffForHumans();
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        $attributes = $this->getAttributes(['content', 'active', 'weight', 'current_champion']);

        return sha1((string)json_encode($attributes));
    }

    /**
     * @return array
     */
    public function getContentUrls(): array
    {
        return LandingPageHelper::extractTemplateUrls($this->content);
    }

    /**
     * @return string
     */
    public function getParsedContent(): string
    {
        $content = LandingPageHelper::transformLinksForTracking($this);
        $content = LandingPageHelper::applyDomainAliasForTrackingLinks($content, $this);

        return $content;
    }

    /**
     * @param string $content
     * @return bool
     * @throws CException
     */
    public function saveContent(string $content): bool
    {
        $this->content = $content;

        $success = false;

        $transaction = db()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new Exception(t('landing_pages', 'Cannot save the landing page variant content'));
            }

            // Remove the links set for tracking but that do not exist anymore in the template
            $this->removeTrackedUrlsNotFoundInTheContent();

            $transaction->commit();
            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
        }

        return $success;
    }

    /**
     * @return void
     */
    public function removeTrackedUrlsNotFoundInTheContent()
    {
        $contentUrls       = $this->getContentUrls();
        $hashedContentUrls = array_map('sha1', $contentUrls);

        $criteria = new CDbCriteria();
        $criteria->compare('variant_id', $this->variant_id);
        $criteria->addNotInCondition('hash', $hashedContentUrls);
        LandingPageUrl::model()->deleteAll($criteria);
    }

    /**
     * @param LandingPageRevisionVariant $sourceVariant
     * @return bool
     */
    public function copyTrackUrlsFromVariant(LandingPageRevisionVariant $sourceVariant): bool
    {
        foreach ($sourceVariant->urls as $urlModel) {
            /** @var LandingPageUrl $newVariantUrlModel */
            $newVariantUrlModel = $urlModel->createNewInstanceFromLoadedAttributes();
            $newVariantUrlModel->page_id     = $this->revision->page_id;
            $newVariantUrlModel->revision_id = $this->revision_id;
            $newVariantUrlModel->variant_id  = (int)$this->variant_id;
            $newVariantUrlModel->hash        = sha1($newVariantUrlModel->destination);
            if (!$newVariantUrlModel->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool $save
     * @return LandingPageRevisionVariant
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
     * @return void
     */
    public function loadDefaults(): void
    {
        $this->title   = t('landing_pages', 'Variant');
        $this->content = '<div></div>';
    }

    /**
     * @param string $active
     * @param bool $save
     * @param bool $resetCounters
     * @param bool $incrementTitleCount
     * @param bool $keepLastUpdated
     * @return self
     * @throws CDbException
     */
    public function copy(
        string $active = '',
        bool $save = false,
        bool $resetCounters = true,
        bool $incrementTitleCount = false,
        bool $keepLastUpdated = true
    ): self {
        /** @var LandingPageRevisionVariant $newVariant */
        $newVariant = $this->createNewInstanceFromLoadedAttributes();
        $newVariant->revision_id  = $this->revision_id;
        $newVariant->created_from = $this->variant_id;

        if (!$keepLastUpdated) {
            $newVariant->date_added   = MW_DATETIME_NOW;
            $newVariant->last_updated = MW_DATETIME_NOW;
        }

        if ($resetCounters) {
            $newVariant->resetCounters();
        }

        if ($active && in_array($active, array_keys($this->getYesNoOptions()))) {
            $newVariant->active = $active;
        }

        if ($incrementTitleCount) {
            if (preg_match('/#(\d+)$/', $newVariant->title, $matches)) {
                $counter = (int)$matches[1];
                $counter++;
                $newVariant->title = (string)preg_replace('/#(\d+)$/', '#' . $counter, $newVariant->title);
            } else {
                $newVariant->title .= ' #1';
            }
        }

        if ($save) {
            $newVariant->save();
        }

        return $newVariant;
    }
}
