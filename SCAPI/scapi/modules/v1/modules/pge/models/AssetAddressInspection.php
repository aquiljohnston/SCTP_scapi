<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAssetAddressInspection".
 *
 * @property integer $AssetAddressInspectionID
 * @property string $AssetAddressInspectionUID
 * @property string $AssetAddressUID
 */
class AssetAddressInspection extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAssetAddressInspection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetAddressInspectionUID', 'AssetAddressUID'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AssetAddressInspectionID' => 'Asset Address Inspection ID',
            'AssetAddressInspectionUID' => 'Asset Address Inspection Uid',
            'AssetAddressUID' => 'Asset Address Uid',
        ];
    }
}
