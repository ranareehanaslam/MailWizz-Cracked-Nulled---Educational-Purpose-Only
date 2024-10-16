<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageTrackUrl
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */

/**
 * This is the model class for table "landing_page_track_url".
 *
 * The followings are the available columns in table 'landing_page_track_url':
 * @property integer $id
 * @property integer $url_id
 * @property integer $location_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string|CDbExpression $date_added
 *
 * The followings are the available model relations:
 * @property IpLocation $ipLocation
 * @property LandingPageUrl $url
 */
class LandingPageTrackUrl extends ActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{landing_page_track_url}}';
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
            'ipLocation' => [self::BELONGS_TO, IpLocation::class, 'location_id'],
            'url'        => [self::BELONGS_TO, LandingPageUrl::class, 'url_id'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'id'            => t('landing_pages', 'ID'),
            'url_id'        => t('landing_pages', 'Url'),
            'location_id'   => t('landing_pages', 'Location'),
            'ip_address'    => t('landing_pages', 'Ip Address'),
            'user_agent'    => t('landing_pages', 'User Agent'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return LandingPageTrackUrl the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var LandingPageTrackUrl $model */
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
