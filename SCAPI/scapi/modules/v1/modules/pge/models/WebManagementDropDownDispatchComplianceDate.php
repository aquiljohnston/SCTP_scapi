<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementDropDownDispatchComplianceDate".
 *
 * @property string $ComplianceYearMonth
 * @property string $ComplianceSort
 */
class WebManagementDropDownDispatchComplianceDate extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementDropDownDispatchComplianceDate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ComplianceYearMonth', 'ComplianceSort'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ComplianceYearMonth' => 'Compliance Year Month',
            'ComplianceSort' => 'Compliance Sort',
        ];
    }
}
