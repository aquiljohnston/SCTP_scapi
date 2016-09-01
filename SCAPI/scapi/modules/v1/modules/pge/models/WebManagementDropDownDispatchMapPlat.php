<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownDispatchMapPlat".
 *
 * @property string $WorkCenter
 * @property string $Map/Plat
 */
class WebManagementDropDownDispatchMapPlat extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownDispatchMapPlat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WorkCenter', 'MapPlat'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'WorkCenter' => 'Work Center',
            'MapPlat' => 'Map Plat',
        ];
    }
}
