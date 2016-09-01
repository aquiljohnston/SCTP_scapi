<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownDispatchSurveyType".
 *
 * @property string $SurveyType
 */
class WebManagementDropDownDispatchSurveyType extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownDispatchSurveyType';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['SurveyType'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'SurveyType' => 'Survey Type',
        ];
    }
}
