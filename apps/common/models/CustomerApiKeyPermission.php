<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * This is the model class for table "{{customer_api_key_permission}}".
 *
 * The followings are the available columns in table '{{customer_api_key_permission}}':
 * @property integer $permission_id
 * @property string $name
 * @property string $route
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CustomerApiKey[] $keys
 */
class CustomerApiKeyPermission extends ActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{customer_api_key_permission}}';
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
            'keys' => [self::MANY_MANY, CustomerApiKey::class, '{{customer_api_key_to_permission}}(permission_id, key_id)'],
        ];

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = [
            'permission_id' => t('api_keys', 'Permission'),
            'name'          => t('api_keys', 'Name'),
            'route'         => t('api_keys', 'Route'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerApiKeyPermission the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
