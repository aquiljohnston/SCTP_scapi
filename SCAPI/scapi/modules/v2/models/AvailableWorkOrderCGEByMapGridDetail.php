<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderCGEByMapGridDetail".
 *
 * @property string $Inspector
 * @property string $Address
 * @property string $InspectionDateTime
 * @property string $Image
 * @property string $MapGrid
 * @property integer $ID
 */
class AvailableWorkOrderCGEByMapGridDetail extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAvailableWorkOrderCGEByMapGridDetail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Inspector', 'Address', 'Image', 'MapGrid'], 'string'],
            [['InspectionDateTime'], 'safe'],
            [['ID'], 'required'],
            [['ID'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Inspector' => 'Inspector',
            'Address' => 'Address',
            'InspectionDateTime' => 'Inspection Date Time',
            'Image' => 'Image',
            'MapGrid' => 'Map Grid',
            'ID' => 'ID',
        ];
    }
}
