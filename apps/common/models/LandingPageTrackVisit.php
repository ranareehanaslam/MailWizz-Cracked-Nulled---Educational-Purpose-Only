<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageTrackVisit
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

/**
 * This is the model class for table "landing_page_track_visit".
 *
 * The followings are the available columns in table 'landing_page_track_visit':
 * @property integer $id
 * @property integer $page_id
 * @property integer $revision_id
 * @property integer $variant_id
 * @property integer $location_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string|CDbExpression $date_added
 *
 * The followings are the available model relations:
 * @property LandingPage $page
 * @property LandingPageRevision $revision
 * @property LandingPageRevisionVariant $variant
 * @property IpLocation $ipLocation
 */
class LandingPageTrackVisit extends ActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page_track_visit}}';
    }

    /**
     * @return array
     * @throws CException
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
            'page'       => [self::BELONGS_TO, LandingPage::class, 'page_id'],
            'revision'   => [self::BELONGS_TO, LandingPageRevision::class, 'revision_id'],
            'variant'    => [self::BELONGS_TO, LandingPageRevisionVariant::class, 'variant_id'],
            'ipLocation' => [self::BELONGS_TO, IpLocation::class, 'location_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'id'          => t('landing_pages', 'ID'),
            'page_id'     => t('landing_pages', 'Page'),
            'revision_id' => t('landing_pages', 'Revision'),
            'variant_id'  => t('landing_pages', 'Variant'),
            'location_id' => t('landing_pages', 'Location'),
            'ip_address'  => t('landing_pages', 'Ip address'),
            'user_agent'  => t('landing_pages', 'User agent'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return LandingPageTrackVisit the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var LandingPageTrackVisit $model */
        $model = parent::model($className);

        return $model;
    }

    /**
     * @return string
     */
    public function getIpWithLocationForGrid(): string
    {
        if (empty($this->ipLocation)) {
            return (string)$this->ip_address;
        }

        return $this->ip_address . ' <br />(' . $this->ipLocation->getLocation() . ')';
    }
}
