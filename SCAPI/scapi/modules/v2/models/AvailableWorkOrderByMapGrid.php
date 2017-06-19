<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAvailableWorkOrderByMapGrid".
 *
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $InspectionAttemptCounter
 * @property integer $AvailableWorkOrderCount
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
            [['MapGrid'], 'string'],
            [['ComplianceStart', 'ComplianceEnd'], 'safe'],
            [['InspectionAttemptCounter', 'AvailableWorkOrderCount'], 'integer'],
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
            'InspectionAttemptCounter' => 'Inspection Attempt Counter',
            'AvailableWorkOrderCount' => 'Available Work Order Count',
        ];
    }
}
