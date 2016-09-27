<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAssignedSurveyFreq".
 *
 * @property string $SurveyType
 * @property string $Division
 * @property string $WorkCenter
 */
class WebManagementDropDownAssignedSurveyFreq extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAssignedSurveyFreq';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['SurveyType', 'Division', 'WorkCenter'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'SurveyType' => 'Survey Type',
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
        ];
    }
}
