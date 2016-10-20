<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vTabletMeter".
 *
 * @property string $MeterType
 * @property string $MeterMfgType
 * @property string $MeterModelType
 */
class TabletMeter extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTabletMeter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MeterType', 'MeterMfgType', 'MeterModelType'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MeterType' => 'Meter Type',
            'MeterMfgType' => 'Meter Mfg Type',
            'MeterModelType' => 'Meter Model Type',
        ];
    }
}
