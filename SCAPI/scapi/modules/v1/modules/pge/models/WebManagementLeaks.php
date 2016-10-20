<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownUserWorkCenter".
 *
 * @property string $WorkCenter
 * @property string $WorkCenterUID
 */
class WebManagementLeaks extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementLeaks';
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
            "Status" => "Status",
            "Approved" => "Approved",
            "HCA" => "HCA",
            "Map/Plat" => "Map/Plat",
            "SAPLeakNumber" => "SAPLeakNumber",
            "AboveBelowGround" => "AboveBelowGround",
            "FoundDateTime" => "FoundDateTime",
            "Address" => "Address",
            "City" => "City",
            "SORL" => "SORL",
            "ReadingInPct" => "ReadingInPct",
            "InstTypeFoundBy" => "InstTypeFoundBy",
            "InstTypeGradeBy" => "InstTypeGradeBy",
            "Grade" => "Grade",
            "LocationRemarks" => "LocationRemarks",
            "UID" => "Leak #",
            "MasterLeakLogUID" => "MasterLeakLogUID",
            "Division" => "Division",
            "MapPlatNumber" => "MapPlatNumber",
            "LockFlag" => "LockFlag",
            "WorkCenter" => "WorkCenter",
            "Date" => "Date",
            "Surveyor" => "Surveyor",
            "Time" => "Time",
            "LeakNumber" => "LeakNumber"
        ];
    }
}
