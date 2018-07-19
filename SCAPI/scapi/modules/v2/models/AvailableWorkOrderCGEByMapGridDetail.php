<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderCGEByMapGridDetail".
 *
 * @property string $CustomerInfo
 * @property string $InspectionType
 * @property string $Inspector
 * @property string $Address
 * @property string $InspectionDateTime
 * @property string $Image
 * @property string $MapGrid
 * @property integer $WorkOrderID
 * @property string $SectionNumber
 * @property integer $ScheduleRequired
 * @property string $BillingCode
 * @property string $OfficeName
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
            [['CustomerInfo', 'InspectionType', 'Inspector', 'Address', 'InspectionDateTime', 'Image', 'MapGrid', 'SectionNumber', 'BillingCode', 'OfficeName'], 'string'],
            [['WorkOrderID'], 'required'],
            [['WorkOrderID', 'ScheduleRequired'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'CustomerInfo' => 'Customer Info',
            'InspectionType' => 'Inspection Type',
            'Inspector' => 'Inspector',
            'Address' => 'Address',
            'InspectionDateTime' => 'Inspection Date Time',
            'Image' => 'Image',
            'MapGrid' => 'Map Grid',
            'WorkOrderID' => 'Work Order ID',
            'SectionNumber' => 'Section Number',
            'ScheduleRequired' => 'Schedule Required',
            'BillingCode' => 'Billing Code',
            'OfficeName' => 'Office Name',
        ];
    }
}
