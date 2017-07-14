<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vWebManagementInspectionsByMapGrid".
 *
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $TotalInspections
 * @property string $PercentageComplete
 */
class WebManagementInspectionsByMapGrid extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementInspectionsByMapGrid';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid'], 'string'],
            [['ComplianceStart', 'ComplianceEnd'], 'safe'],
            [['TotalInspections'], 'integer'],
            [['PercentageComplete'], 'number'],
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
            'TotalInspections' => 'Total Inspections',
            'PercentageComplete' => 'Percentage Complete',
        ];
    }
}
