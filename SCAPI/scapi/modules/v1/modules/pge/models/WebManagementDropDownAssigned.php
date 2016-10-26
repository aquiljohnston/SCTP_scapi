<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAssigned".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyFreq
 * @property string $FLOC
 * @property string $StatusType
 * @property string $DispatchMethod
 * @property string $ComplianceYearMonth
 * @property string $ComplianceSort
 */
class WebManagementDropDownAssigned extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAssigned';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Division', 'WorkCenter', 'SurveyFreq', 'FLOC', 'StatusType', 'DispatchMethod', 'ComplianceYearMonth', 'ComplianceSort'], 'string'],
            [['StatusType'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
            'SurveyFreq' => 'Survey Freq',
            'FLOC' => 'Floc',
            'StatusType' => 'Status Type',
            'DispatchMethod' => 'Dispatch Method',
            'ComplianceYearMonth' => 'Compliance Year Month',
            'ComplianceSort' => 'Compliance Sort',
        ];
    }
}
