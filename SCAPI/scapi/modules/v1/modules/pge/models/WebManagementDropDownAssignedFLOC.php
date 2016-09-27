<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownAssignedFLOC".
 *
 * @property string $FLOC
 * @property string $Division
 * @property string $WorkCenter
 * @property string $SurveyType
 */
class WebManagementDropDownAssignedFLOC extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownAssignedFLOC';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['FLOC', 'Division', 'WorkCenter', 'SurveyType'], 'string']
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
        ];
    }
}
