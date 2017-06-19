<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAssignedWorkQueueByMapGrid".
 *
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $InspectionAttemptCounter
 * @property integer $SectionFlag
 * @property integer $AssignedWorkOrderCount
 * @property string $AssignedCount
 * @property string $UIDList
 * @property string $SearchString
 */
class AssignedWorkQueueByMapGrid extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vAssignedWorkQueueByMapGrid';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid', 'AssignedCount', 'UIDList', 'SearchString'], 'string'],
            [['ComplianceStart', 'ComplianceEnd'], 'safe'],
            [['InspectionAttemptCounter', 'SectionFlag', 'AssignedWorkOrderCount'], 'integer'],
            [['SectionFlag', 'AssignedCount'], 'required'],
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
            'SectionFlag' => 'Section Flag',
            'AssignedWorkOrderCount' => 'Assigned Work Order Count',
            'AssignedCount' => 'Assigned Count',
            'UIDList' => 'Uidlist',
            'SearchString' => 'Search String',
        ];
    }
}
