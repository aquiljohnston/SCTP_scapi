<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderByMapGrid".
 *
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $SectionFlag
 * @property integer $AvailableWorkOrderCount
 * @property string $Frequency
 * @property string $Division
 * @property string $BillingCode
 */
class AvailableWorkOrderByMapGrid extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAvailableWorkOrderByMapGrid';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid', 'Frequency', 'Division', 'BillingCode'], 'string'],
            [['ComplianceStart', 'ComplianceEnd'], 'safe'],
            [['SectionFlag'], 'required'],
            [['SectionFlag', 'AvailableWorkOrderCount'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGrid' => 'Map Grid',
            'ComplianceStart' => 'Compliance Start',
            'ComplianceEnd' => 'Compliance End',
            'SectionFlag' => 'Section Flag',
            'AvailableWorkOrderCount' => 'Available Work Order Count',
            'Frequency' => 'Frequency',
            'Division' => 'Division',
            'BillingCode' => 'Billing Code',
            'InspectionType' => 'Inspection Type',
            'OfficeName' => 'Office Name',
        ];
    }
}
