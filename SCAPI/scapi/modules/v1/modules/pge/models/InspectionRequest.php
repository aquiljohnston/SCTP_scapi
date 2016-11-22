<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tInspectionRequest".
 *
 * @property integer $tInspectionRequestID
 * @property string $InspectionRequestUID
 * @property string $MapGridUID
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $CreateDTLT
 * @property string $ModifiedDTLT
 * @property string $Comments
 * @property string $RevisionComments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $StatusType
 * @property string $PipelineType
 * @property string $SurveyType
 * @property string $LsNtfNo
 * @property string $OrderNo
 * @property string $MapID
 * @property string $Wall
 * @property string $Plat
 * @property string $MWC
 * @property string $FLOC
 * @property string $InspectionFrequencyType
 * @property string $ComplianceDueDate
 * @property string $ScheduledStartDate
 * @property string $ScheduledCompleteDate
 * @property string $ReleaseDate
 * @property string $PrevServ
 * @property string $PrevFtOfMain
 * @property integer $ReturnFlag
 * @property string $ReturnComments
 * @property integer $FileCount
 * @property integer $ApprovedFlag
 * @property string $ApprovedByUserUID
 * @property string $ApprovedDTLT
 * @property integer $SubmittedFlag
 * @property string $SubmittedStatusType
 * @property string $SubmittedUserUID
 * @property string $SubmittedDTLT
 * @property integer $ReturnedFlag
 * @property string $ReturnedFromPGEStatusType
 * @property string $RetrunedFromPGEDTLT
 * @property integer $CompletedFlag
 * @property string $CompletedDTLT
 * @property string $InspectionType
 * @property string $ActualStartDate
 * @property integer $AdhocFlag
 */
class InspectionRequest extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tInspectionRequest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['InspectionRequestUID', 'CreatedUserUID', 'ModifiedUserUID'], 'required'],
            [['InspectionRequestUID', 'MapGridUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'RevisionComments', 'StatusType', 'PipelineType', 'SurveyType', 'LsNtfNo', 'OrderNo', 'MapID', 'Wall', 'Plat', 'MWC', 'FLOC', 'InspectionFrequencyType', 'ReturnComments', 'ApprovedByUserUID', 'SubmittedStatusType', 'SubmittedUserUID', 'ReturnedFromPGEStatusType', 'InspectionType'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'PrevServ', 'PrevFtOfMain', 'ReturnFlag', 'FileCount', 'ApprovedFlag', 'SubmittedFlag', 'ReturnedFlag', 'CompletedFlag', 'AdhocFlag'], 'integer'],
            [['CreateDTLT', 'ModifiedDTLT', 'ComplianceDueDate', 'ScheduledStartDate', 'ScheduledCompleteDate', 'ReleaseDate', 'ApprovedDTLT', 'SubmittedDTLT', 'RetrunedFromPGEDTLT', 'CompletedDTLT', 'ActualStartDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tInspectionRequestID' => 'T Inspection Request ID',
            'InspectionRequestUID' => 'Inspection Request Uid',
            'MapGridUID' => 'Map Grid Uid',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'CreateDTLT' => 'Create Dtlt',
            'ModifiedDTLT' => 'Modified Dtlt',
            'Comments' => 'Comments',
            'RevisionComments' => 'Revision Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'StatusType' => 'Status Type',
            'PipelineType' => 'Pipeline Type',
            'SurveyType' => 'Survey Type',
            'LsNtfNo' => 'Ls Ntf No',
            'OrderNo' => 'Order No',
            'MapID' => 'Map ID',
            'Wall' => 'Wall',
            'Plat' => 'Plat',
            'MWC' => 'Mwc',
            'FLOC' => 'Floc',
            'InspectionFrequencyType' => 'Inspection Frequency Type',
            'ComplianceDueDate' => 'Compliance Due Date',
            'ScheduledStartDate' => 'Scheduled Start Date',
            'ScheduledCompleteDate' => 'Scheduled Complete Date',
            'ReleaseDate' => 'Release Date',
            'PrevServ' => 'Prev Serv',
            'PrevFtOfMain' => 'Prev Ft Of Main',
            'ReturnFlag' => 'Return Flag',
            'ReturnComments' => 'Return Comments',
            'FileCount' => 'File Count',
            'ApprovedFlag' => 'Approved Flag',
            'ApprovedByUserUID' => 'Approved By User Uid',
            'ApprovedDTLT' => 'Approved Dtlt',
            'SubmittedFlag' => 'Submitted Flag',
            'SubmittedStatusType' => 'Submitted Status Type',
            'SubmittedUserUID' => 'Submitted User Uid',
            'SubmittedDTLT' => 'Submitted Dtlt',
            'ReturnedFlag' => 'Returned Flag',
            'ReturnedFromPGEStatusType' => 'Returned From Pgestatus Type',
            'RetrunedFromPGEDTLT' => 'Retruned From Pgedtlt',
            'CompletedFlag' => 'Completed Flag',
            'CompletedDTLT' => 'Completed Dtlt',
            'InspectionType' => 'Inspection Type',
            'ActualStartDate' => 'Actual Start Date',
            'AdhocFlag' => 'Adhoc Flag',
        ];
    }
}
