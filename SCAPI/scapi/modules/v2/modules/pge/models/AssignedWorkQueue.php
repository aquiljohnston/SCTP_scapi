<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tAssignedWorkQueue".
 *
 * @property integer $AssignedWorkQueueID
 * @property string $AssignedWorkQueueUID
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $SrvCreatedDTLT
 * @property string $SrvCreatedDTLTOffset
 * @property string $SrvModifiedDTLT
 * @property string $SrvModifiedDTLTOffset
 * @property string $Comments
 * @property string $RevisionComments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $AssignedInspectionRequestUID
 * @property string $AssignedUserUID
 * @property string $AssignedDate
 * @property string $AcceptedDate
 * @property integer $AcceptedFlag
 * @property integer $LockedFlag
 * @property integer $PendingDeleteFlag
 * @property string $DispatchMethod
 */
class AssignedWorkQueue extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tAssignedWorkQueue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssignedWorkQueueUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'RevisionComments', 'AssignedInspectionRequestUID', 'AssignedUserUID', 'DispatchMethod'], 'string'],
            [['CreatedUserUID', 'ModifiedUserUID'], 'required'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'AcceptedFlag', 'LockedFlag', 'PendingDeleteFlag'], 'integer'],
            [['SrvCreatedDTLT', 'SrvCreatedDTLTOffset', 'SrvModifiedDTLT', 'SrvModifiedDTLTOffset', 'AssignedDate', 'AcceptedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AssignedWorkQueueID' => 'Assigned Work Queue ID',
            'AssignedWorkQueueUID' => 'Assigned Work Queue Uid',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'SrvCreatedDTLT' => 'Srv Created Dtlt',
            'SrvCreatedDTLTOffset' => 'Srv Created Dtltoffset',
            'SrvModifiedDTLT' => 'Srv Modified Dtlt',
            'SrvModifiedDTLTOffset' => 'Srv Modified Dtltoffset',
            'Comments' => 'Comments',
            'RevisionComments' => 'Revision Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'AssignedInspectionRequestUID' => 'Assigned Inspection Request Uid',
            'AssignedUserUID' => 'Assigned User Uid',
            'AssignedDate' => 'Assigned Date',
            'AcceptedDate' => 'Accepted Date',
            'AcceptedFlag' => 'Accepted Flag',
            'LockedFlag' => 'Locked Flag',
            'PendingDeleteFlag' => 'Pending Delete Flag',
            'DispatchMethod' => 'Dispatch Method',
        ];
    }
}