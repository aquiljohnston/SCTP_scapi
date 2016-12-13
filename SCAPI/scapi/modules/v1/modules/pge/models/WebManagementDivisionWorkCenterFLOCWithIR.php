<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDivisionWorkCenterFLOCWithIR".
 *
 * @property string $Division
 * @property string $WorkCenter
 * @property string $FLOC
 * @property string $SurveyFreq
 */
class WebManagementDivisionWorkCenterFLOCWithIR extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDivisionWorkCenterFLOCWithIR';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Division', 'WorkCenter', 'FLOC', 'SurveyFreq'], 'string']
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
            'FLOC' => 'Floc',
            'SurveyFreq' => 'Survey Freq',
        ];
    }
}
