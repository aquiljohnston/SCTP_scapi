<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAssetInspection".
 *
 * @property integer $AssetInspectionID
 * @property string $AssetInspectionUID
 * @property string $AssetUID
 * @property string $MasterLeakLogsUID
 * @property string $MapGridUID
 * @property string $InspectionRequestUID
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property string $SrcOpenDTLT
 * @property string $SrcClosedDTLT
 * @property string $GPSType
 * @property string $GPSSentence
 * @property double $Latitude
 * @property double $Longitude
 * @property string $SHAPE
 * @property string $Comments
 * @property string $RevisionComments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $StatusType
 * @property integer $InspectionFlag
 * @property string $Photo1
 * @property string $Photo2
 * @property string $Photo3
 * @property string $OptionalData1
 * @property string $OptionalData2
 * @property string $OptionalData3
 * @property string $OptionalData4
 * @property string $OptionalData5
 * @property integer $ApprovedFlag
 * @property string $ApprovedByUserUID
 * @property string $ApprovedDTLT
 * @property integer $SubmittedFlag
 * @property string $SubmittedStatusType
 * @property integer $SubmittedUserID
 * @property string $SubmittedDTLT
 * @property string $ResponseStatusType
 * @property string $ResponseComments
 * @property string $ResponceErrorComments
 * @property string $ResponseDTLT
 * @property integer $CompletedFlag
 * @property string $CompletedDTLT
 */
class AssetInspection extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAssetInspection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetInspectionUID', 'AssetUID', 'MasterLeakLogsUID', 'MapGridUID', 'InspectionRequestUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'GPSType', 'GPSSentence', 'SHAPE', 'Comments', 'RevisionComments', 'StatusType', 'Photo1', 'Photo2', 'Photo3', 'OptionalData1', 'OptionalData2', 'OptionalData3', 'OptionalData4', 'OptionalData5', 'ApprovedByUserUID', 'SubmittedStatusType', 'ResponseStatusType', 'ResponseComments', 'ResponceErrorComments'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'InspectionFlag', 'ApprovedFlag', 'SubmittedFlag', 'SubmittedUserID', 'CompletedFlag'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset', 'SrcOpenDTLT', 'SrcClosedDTLT', 'ApprovedDTLT', 'SubmittedDTLT', 'ResponseDTLT', 'CompletedDTLT'], 'safe'],
            [['Latitude', 'Longitude'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AssetInspectionID' => 'Asset Inspection ID',
            'AssetInspectionUID' => 'Asset Inspection Uid',
            'AssetUID' => 'Asset Uid',
            'MasterLeakLogsUID' => 'Master Leak Logs Uid',
            'MapGridUID' => 'Map Grid Uid',
            'InspectionRequestUID' => 'Inspection Request Uid',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'SrcOpenDTLT' => 'Src Open Dtlt',
            'SrcClosedDTLT' => 'Src Closed Dtlt',
            'GPSType' => 'Gpstype',
            'GPSSentence' => 'Gpssentence',
            'Latitude' => 'Latitude',
            'Longitude' => 'Longitude',
            'SHAPE' => 'Shape',
            'Comments' => 'Comments',
            'RevisionComments' => 'Revision Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'StatusType' => 'Status Type',
            'InspectionFlag' => 'Inspection Flag',
            'Photo1' => 'Photo1',
            'Photo2' => 'Photo2',
            'Photo3' => 'Photo3',
            'OptionalData1' => 'Optional Data1',
            'OptionalData2' => 'Optional Data2',
            'OptionalData3' => 'Optional Data3',
            'OptionalData4' => 'Optional Data4',
            'OptionalData5' => 'Optional Data5',
            'ApprovedFlag' => 'Approved Flag',
            'ApprovedByUserUID' => 'Approved By User Uid',
            'ApprovedDTLT' => 'Approved Dtlt',
            'SubmittedFlag' => 'Submitted Flag',
            'SubmittedStatusType' => 'Submitted Status Type',
            'SubmittedUserID' => 'Submitted User ID',
            'SubmittedDTLT' => 'Submitted Dtlt',
            'ResponseStatusType' => 'Response Status Type',
            'ResponseComments' => 'Response Comments',
            'ResponceErrorComments' => 'Responce Error Comments',
            'ResponseDTLT' => 'Response Dtlt',
            'CompletedFlag' => 'Completed Flag',
            'CompletedDTLT' => 'Completed Dtlt',
        ];
    }
}
