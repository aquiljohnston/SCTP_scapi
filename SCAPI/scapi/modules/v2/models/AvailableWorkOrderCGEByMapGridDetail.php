<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderCGEByMapGridDetail".
 *
 * @property string $CustomerInfo
 * @property string $SurveyType
 * @property string $Inspector
 * @property string $Address
 * @property string $InspectionDateTime
 * @property string $Image
 * @property string $MapGrid
 * @property integer $ID
 * @property string $SectionNumber
 * @property integer $ScheduleRequired
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
            [['CustomerInfo', 'SurveyType', 'Inspector', 'Address', 'InspectionDateTime', 'Image', 'MapGrid', 'SectionNumber'], 'string'],
            [['ID'], 'required'],
            [['ID', 'ScheduleRequired'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'CustomerInfo' => 'Customer Info',
            'SurveyType' => 'Survey Type',
            'Inspector' => 'Inspector',
            'Address' => 'Address',
            'InspectionDateTime' => 'Inspection Date Time',
            'Image' => 'Image',
            'MapGrid' => 'Map Grid',
            'ID' => 'ID',
            'SectionNumber' => 'Section Number',
            'ScheduleRequired' => 'Schedule Required',
        ];
    }
}
