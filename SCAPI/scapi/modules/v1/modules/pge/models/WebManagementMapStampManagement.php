<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementMapStampManagement".
 *
 */
class WebManagementMapStampManagement extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementMapStampManagement';
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
            "MapStampStatus" => "Status",
            "Division" => "Division",
            "WorkCenter" => "Work Center",
            "FLOC" => "FLOC",
            "InspectionType" => "Type",
            "NotificationID" => "Notification ID",
            "SurveyType" => "Survey Freq",
            "ComplianceDate" => "Compliance Date",
            "Total # Of Days" => "Total # of Days",
            "TotalNoOfLeaks" => "Total # of Leaks",
            "TotalFeetOfMain" => "Total Feet of Main",
            "TotalServices" => "Total # of Services",
            "InspectionRequestUID" => "Inspection Request UID"
        ];
    }
}
