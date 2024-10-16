<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageTemplate
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

/**
 * This is the model class for table "landing_page_template".
 *
 * The followings are the available columns in table 'landing_page_template':
 * @property integer|null $template_id
 * @property string|null $builder_id
 * @property string $is_blank
 * @property string $title
 * @property string $content
 * @property string $screenshot
 * @property string|CDbExpression $date_added
 * @property string|CDbExpression $last_updated
 *
 */
class LandingPageTemplate extends ActiveRecord
{
    use ModelHashIdsTrait;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page_template}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            ['title, content', 'required'],
            ['title', 'length', 'max' => 200],
            ['is_blank', 'in', 'range' => array_keys($this->getYesNoOptions())],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'template_id' => $this->t('Landing page variant'),
            'builder_id'  => $this->t('Builder'),
            'title'       => $this->t('Title'),
            'content'     => $this->t('Content'),
            'screenshot'  => $this->t('Screenshot'),
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

        $criteria->compare('title', $this->title);

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
     * @return LandingPageTemplate the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var LandingPageTemplate $model */
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
     * @return string
     */
    public function getSignature(): string
    {
        $attributes = $this->getAttributes(['builder_id', 'title', 'content', 'screenshot']);

        return sha1((string)json_encode($attributes));
    }

    /**
     * @return string
     */
    public function getScreenshotSrc(): string
    {
        if (!empty($this->screenshot)) {
            if (FilterVarHelper::url($this->screenshot)) {
                return $this->screenshot;
            }
            try {
                if ($image = @ImageHelper::resize((string)$this->screenshot)) {
                    return $image;
                }
            } catch (Exception $e) {
            }
        }
        return ImageHelper::resize('/frontend/assets/files/no-template-image-320x320.jpg');
    }

    /**
     * @return bool
     */
    public function getIsBlank(): bool
    {
        return $this->is_blank === self::TEXT_YES;
    }

    /**
     * @return static|null
     */
    public static function getBlankTemplate(): ?self
    {
        return self::model()->findByAttributes(['is_blank' => self::TEXT_YES]);
    }

    /**
     * @return bool
     */
    protected function beforeValidate()
    {
        if ($this->is_blank != self::TEXT_YES) {
            $blankTemplate = self::getBlankTemplate();
            if (empty($blankTemplate)) {
                $this->is_blank = self::TEXT_YES;
            }
        }

        return parent::beforeValidate();
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        if ($this->is_blank == self::TEXT_YES) {
            self::model()->updateAll(['is_blank' => self::TEXT_NO], 'template_id != :tid AND is_blank = :blank', [':tid' => (int)$this->template_id, ':blank' => self::TEXT_YES]);
        }

        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    protected function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        return $this->is_blank != self::TEXT_YES;
    }
}
