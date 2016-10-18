<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownUserWorkCenter".
 *
 * @property string $WorkCenter
 * @property string $WorkCenterUID
 */
class WebManagementMasterLeakLog extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
//        if (YII_ENV_DEV) {
//            return 'llmgmt';
//        }
        return 'vWebManagementMasterLeakLog';
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
            "Leaks" => "Leaks",
            "Approved" => "Approved",
            "HCA" => "HCA",
            "Date" => "Date",
            "Surveyor" => "Surveyor",
            "WorkCenter" => "Work Center",
            "FLOC" => "FLOC",
            "SurveyFreq" => "Survey Freq",
            "FeetOfMain" => "Feet of Main",
            "NumofServices" => "# of Services",
            "Hours" => "Hours",
        ];
    }
}
