<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDivisionWorkCenterFLOCWithIR".
 *
 * @property string $FLOC
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyFreq
 */
class WebManagementFlocsDropDown extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDivisionWorkCenterFLOCWithIR';
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
            "FLOC" => "FLOC",
            "Division" => "Division",
            "WorkCenter" => "WorkCenter",
            "SurveyFreq" => "SurveyFreq"
        ];
    }
}
