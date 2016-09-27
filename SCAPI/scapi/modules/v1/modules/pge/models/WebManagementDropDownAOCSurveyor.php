<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAOCSurveyor".
 *
 * @property string $Surveyor
 * @property string $WorkCenter
 * @property string $Division
 */
class WebManagementDropDownAOCSurveyor extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAOCSurveyor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Surveyor', 'WorkCenter', 'Division'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Surveyor' => 'Surveyor',
            'WorkCenter' => 'Work Center',
            'Division' => 'Division',
        ];
    }
}
