<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownDispatch".
 *
 * @property string $FLOC
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyType
 * @property string $ComplianceYearMonth
 * @property string $ComplianceSort
 */
class WebManagementDropDownDispatch extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownDispatch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['FLOC', 'Division', 'WorkCenter', 'SurveyType', 'ComplianceYearMonth', 'ComplianceSort'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'FLOC' => 'Floc',
            'Division' => 'Division',
            'WorkCenter' => 'Work Center',
            'SurveyType' => 'Survey Type',
            'ComplianceYearMonth' => 'Compliance Year Month',
            'ComplianceSort' => 'Compliance Sort',
        ];
    }
}
