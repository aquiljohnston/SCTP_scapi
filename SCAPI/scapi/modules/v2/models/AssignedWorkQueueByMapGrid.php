<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vAssignedWorkQueueByMapGrid".
 *
 * @property string $MapGrid
 * @property string $ComplianceStart
 * @property string $ComplianceEnd
 * @property integer $InspectionAttemptcounter
 * @property integer $SectionFlag
 * @property integer $AssignedWorkOrderCount
 * @property string $AssignedCount
 * @property string $UIDList
 * @property string $SearchString
 * @property string $Percent Completed
 * @property integer $Total
 * @property integer $Remaining
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
            [['InspectionAttemptcounter', 'SectionFlag', 'AssignedWorkOrderCount', 'Total', 'Remaining'], 'integer'],
            [['SectionFlag', 'AssignedCount'], 'required'],
            [['Percent Completed'], 'number'],
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
            'InspectionAttemptcounter' => 'Inspection Attemptcounter',
            'SectionFlag' => 'Section Flag',
            'AssignedWorkOrderCount' => 'Assigned Work Order Count',
            'AssignedCount' => 'Assigned Count',
            'UIDList' => 'Uidlist',
            'SearchString' => 'Search String',
            'Percent Completed' => 'Percent  Completed',
            'Total' => 'Total',
            'Remaining' => 'Remaining',
        ];
    }
}
