<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderCGEByWODetails".
 *
 * @property string $Inspector
 * @property string $Address
 * @property string $InspectionDateTime
 * @property string $Image
 * @property integer $ID
 */
class AvailableWorkOrderCGEByWODetails extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAvailableWorkOrderCGEByWODetails';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Inspector', 'Address', 'Image'], 'string'],
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
            'ID' => 'ID',
        ];
    }
}
