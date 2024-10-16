<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageUrl
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

/**
 * This is the model class for table "landing_page_url".
 *
 * The followings are the available columns in table 'landing_page_url':
 * @property null|int $url_id
 * @property integer $page_id
 * @property integer $revision_id
 * @property integer $variant_id
 * @property string $hash
 * @property string $destination
 * @property string|CDbExpression $date_added
 *
 * The followings are the available model relations:
 * @property LandingPage $page
 * @property LandingPageRevision $revision
 * @property LandingPageRevisionVariant $variant
 */
class LandingPageUrl extends ActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page_url}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     */
    public function relations()
    {
        $relations = [
            'page'     => [self::BELONGS_TO, LandingPage::class, 'page_id'],
            'revision' => [self::BELONGS_TO, LandingPageRevision::class, 'revision_id'],
            'variant'  => [self::BELONGS_TO, LandingPageRevisionVariant::class, 'variant_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'url_id'        => t('landing_pages', 'Url'),
            'page_id'       => t('landing_pages', 'Page'),
            'revision_id'   => t('landing_pages', 'Revision'),
            'variant_id'    => t('landing_pages', 'Variant'),
            'hash'          => t('landing_pages', 'Hash'),
            'destination'   => t('landing_pages', 'Destination'),
            'clicked_times' => t('landing_pages', 'Clicked times'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return LandingPageUrl the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var LandingPageUrl $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @param int $textLength
     *
     * @return string
     */
    public function getDisplayGridDestination(int $textLength = 0): string
    {
        $destination = (string)str_replace('&amp;', '&', (string)$this->destination);
        $text        = $destination;
        if ($textLength > 0) {
            $text = StringHelper::truncateLength($text, $textLength);
        }
        if (FilterVarHelper::url($destination)) {
            return CHtml::link($text, $destination, ['target' => '_blank', 'title' => $destination]);
        }
        return $text;
    }
}
