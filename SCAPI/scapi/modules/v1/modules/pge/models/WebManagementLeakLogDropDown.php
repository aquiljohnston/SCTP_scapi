<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementLeakLogDropDown".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string $Surveyor
 * @property string $Map/Plat
 * @property string $Date
 * @property string $OrderByDate
 */
class WebManagementLeakLogDropDown extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementLeakLogDropDown';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Division', 'WorkCenter', 'Surveyor', 'Map/Plat', 'Date'], 'string'],
            [['OrderByDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
            'Surveyor' => 'Surveyor',
            'Map/Plat' => 'Map/ Plat',
            'Date' => 'Date',
            'OrderByDate' => 'Order By Date',
        ];
    }
}
