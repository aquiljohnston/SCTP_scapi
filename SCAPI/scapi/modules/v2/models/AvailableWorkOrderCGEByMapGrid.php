<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderCGEByMapGrid".
 *
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $SectionFlag
 * @property integer $AvailableWorkOrderCount
 * @property integer $ScheduleRequired
 */
class AvailableWorkOrderCGEByMapGrid extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAvailableWorkOrderCGEByMapGrid';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid'], 'string'],
            [['ComplianceStart', 'ComplianceEnd'], 'safe'],
            [['SectionFlag'], 'required'],
            [['SectionFlag', 'AvailableWorkOrderCount', 'ScheduleRequired'], 'integer'],
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
            'ScheduleRequired' => 'Schedule Required',
            'InspectionType' => 'Inspection Type',
            'BillingCode' => 'Billing Code',
            'OfficeName' => 'Office Name',
        ];
    }
}
