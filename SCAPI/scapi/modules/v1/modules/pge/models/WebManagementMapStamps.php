<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementMapStamps".
 *
 */
class WebManagementMapStamps extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        if (YII_ENV_DEV) {
            return 'MapStampFake';
        }
        return 'vWebManagementMapStamps';
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
            "WorkCenter" => "Work Center",
            "Division" => "Division",
            "FLOC" => "FLOC",
            "Type" => "Type",
            "NotificationID" => "Notification ID",
            "Survey Freq" => "Survey Freq",
            "ComplianceDate" => "Compliance Date",
            "TotalNbOfDays" => "Total # of Days",
            "TotalNbOfLeaks" => "Total # of Leaks",
            "TotalFeetOfMain" => "Total Feet of Main",
            "TotalNbOfServices" => "Total # of Services",
        ];
    }
}
