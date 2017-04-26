<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tMasterLeakLog".
 *
 * @property integer $tMasterLeakLogsID
 * @property string $MasterLeakLogUID
 * @property string $InspectionRequestLogUID
 * @property string $MapGridUID
 * @property string $ServiceDate
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $SrcDTLT
 * @property string $SrvDTLTOffset
 * @property string $SrvDTLT
 * @property string $Comments
 * @property string $RevisionComments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $StatusType
 * @property integer $ApprovedFlag
 * @property string $ApprovedByUserUID
 * @property string $ApprovedDTLT
 * @property integer $SubmittedFlag
 * @property string $SubmittedStatusType
 * @property string $SubmittedUserUID
 * @property string $SubmittedDTLT
 * @property string $ResponseStatusType
 * @property string $Response
 * @property string $ResponceErrorDescription
 * @property string $ResponseDTLT
 * @property integer $CompletedFlag
 * @property string $CompletedDTLT
 */
class MasterLeakLog extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tMasterLeakLog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MasterLeakLogUID', 'InspectionRequestLogUID', 'MapGridUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'RevisionComments', 'StatusType', 'ApprovedByUserUID', 'SubmittedStatusType', 'SubmittedUserUID', 'ResponseStatusType', 'Response', 'ResponceErrorDescription'], 'string'],
            [['ServiceDate', 'SrcDTLT', 'SrvDTLTOffset', 'SrvDTLT', 'ApprovedDTLT', 'SubmittedDTLT', 'ResponseDTLT', 'CompletedDTLT'], 'safe'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'ApprovedFlag', 'SubmittedFlag', 'CompletedFlag'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tMasterLeakLogsID' => 'T Master Leak Logs ID',
            'MasterLeakLogUID' => 'Master Leak Log Uid',
            'InspectionRequestLogUID' => 'Inspection Request Log Uid',
            'MapGridUID' => 'Map Grid Uid',
            'ServiceDate' => 'Service Date',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'SrvDTLT' => 'Srv Dtlt',
            'Comments' => 'Comments',
            'RevisionComments' => 'Revision Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'StatusType' => 'Status Type',
            'ApprovedFlag' => 'Approved Flag',
            'ApprovedByUserUID' => 'Approved By User Uid',
            'ApprovedDTLT' => 'Approved Dtlt',
            'SubmittedFlag' => 'Submitted Flag',
            'SubmittedStatusType' => 'Submitted Status Type',
            'SubmittedUserUID' => 'Submitted User Uid',
            'SubmittedDTLT' => 'Submitted Dtlt',
            'ResponseStatusType' => 'Response Status Type',
            'Response' => 'Response',
            'ResponceErrorDescription' => 'Responce Error Description',
            'ResponseDTLT' => 'Response Dtlt',
            'CompletedFlag' => 'Completed Flag',
            'CompletedDTLT' => 'Completed Dtlt',
        ];
    }
}
