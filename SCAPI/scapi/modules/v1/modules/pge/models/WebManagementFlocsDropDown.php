<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownDispatchFLOC".
 *
 * @property string $FLOC
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyType
 */
class WebManagementFlocsDropDown extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDivisionWorkCenterFLOC';
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
            "SurveyType" => "SurveyType"
        ];
    }
}
