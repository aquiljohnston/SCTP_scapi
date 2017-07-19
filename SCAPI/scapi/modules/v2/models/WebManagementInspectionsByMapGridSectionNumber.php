<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vWebManagementInspectionsByMapGridSectionNumber".
 *
 * @property string $MapGrid
 * @property string $SectionNumber
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $TotalInspections
 * @property string $PercentageComplete
 */
class WebManagementInspectionsByMapGridSectionNumber extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementInspectionsByMapGridSectionNumber';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid', 'SectionNumber'], 'string'],
            [['SectionNumber'], 'required'],
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
            'SectionNumber' => 'Section Number',
            'ComplianceStart' => 'Compliance Start',
            'ComplianceEnd' => 'Compliance End',
            'TotalInspections' => 'Total Inspections',
            'PercentageComplete' => 'Percentage Complete',
        ];
    }
}
