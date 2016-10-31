<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownUserWorkCenter".
 *
 * @property string $WorkCenter
 * @property string $WorkCenterUID
 */
class WebManagementEquipmentServices extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementEquipmentServices';
    }
    //
    //    /**
    //     * @inheritdoc
    //     */
    //    public function rules()
    //    {
    //        return [
    //            [['WorkCenter', 'WorkCenterUID'], 'string']
    //        ];
    //    }
    //
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            "Division" => "Division",
            "WorkCenter" => "WorkCenter",
            "Surveyor" => "Surveyor",
            "Map/Plat" => "Map/Plat",
            "Date" => "Date",
            "EquipmentType" => "EquipmentType",
            "SerialNumber" => "SerialNumber",
            "SurveyMode" => "SurveyMode",
            "WindSpeedStart" => "WindSpeedStart",
            "WindSpeedEnd" => "WindSpeedEnd",
            "FeetOfMain" => "FeetOfMain",
            "NumOfServices" => "NumOfServices",
            "InspectionServicesUID" => "InspectionServicesUID",
            "MasterLeakLogUID" => "MasterLeakLogUID",
            "OperatorId" => "OperatorId",
            "LockedFlag" => "LockedFlag",
            "UserLANID" => "LAN ID",
            "SurveyType" => "Survey Type"
        ];
    }
}
