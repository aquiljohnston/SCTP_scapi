<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vWebManagementInspectionsEvents".
 *
 * @property string $StatusDescription
 * @property string $Photo
 * @property string $AOCReason
 * @property string $CGEReason
 * @property string $LeakNumber
 * @property string $LeakPipelineSystemInvestigated
 * @property string $LeakDetectType
 * @property string $LeakGrade
 * @property string $LeakAboveOrBelow
 * @property integer $LeakMeterInspected
 * @property string $LeakMeterNumber
 * @property string $LeakMeterLeakLocation
 * @property integer $RiserOnly
 * @property integer $MultiMeter
 * @property integer $InspectionID
 */
class WebManagementInspectionsEvents extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementInspectionsEvents';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['StatusDescription', 'Photo', 'AOCReason', 'CGEReason', 'LeakNumber', 'LeakPipelineSystemInvestigated', 'LeakDetectType', 'LeakGrade', 'LeakAboveOrBelow', 'LeakMeterNumber', 'LeakMeterLeakLocation'], 'string'],
            [['LeakMeterInspected', 'RiserOnly', 'MultiMeter', 'InspectionID'], 'integer'],
            [['InspectionID'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'StatusDescription' => 'Status Description',
            'Photo' => 'Photo',
            'AOCReason' => 'Aocreason',
            'CGEReason' => 'Cgereason',
            'LeakNumber' => 'Leak Number',
            'LeakPipelineSystemInvestigated' => 'Leak Pipeline System Investigated',
            'LeakDetectType' => 'Leak Detect Type',
            'LeakGrade' => 'Leak Grade',
            'LeakAboveOrBelow' => 'Leak Above Or Below',
            'LeakMeterInspected' => 'Leak Meter Inspected',
            'LeakMeterNumber' => 'Leak Meter Number',
            'LeakMeterLeakLocation' => 'Leak Meter Leak Location',
            'RiserOnly' => 'Riser Only',
            'MultiMeter' => 'Multi Meter',
            'InspectionID' => 'Inspection ID',
        ];
    }
}
