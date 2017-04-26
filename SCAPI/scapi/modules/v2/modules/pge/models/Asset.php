<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAsset".
 *
 * @property integer $AssetID
 * @property string $AssetUID
 * @property string $MapGridUID
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
 * @property integer $NewAssetFlag
 * @property integer $NonAssetLocationFlag
 * @property integer $CGEFlag
 * @property integer $AOCFlag
 * @property integer $InspectFlag
 * @property integer $LeakIndicationFlag
 * @property integer $OtherIndicationFlag
 * @property string $Photo1
 * @property string $Photo2
 * @property string $Photo3
 * @property string $OptionalData1
 * @property string $OptionalData2
 * @property string $OptionalData3
 * @property string $OptionalData4
 * @property string $OptionalData5
 * @property string $OptionalData6
 * @property string $OptionalData7
 * @property string $OptionalData8
 * @property string $OptionalData9
 * @property string $OptionalData10
 * @property string $OptionalData11
 * @property string $OptionalData12
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
class Asset extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAsset';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetUID', 'MapGridUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'GPSType', 'GPSSentence', 'SHAPE', 'Comments', 'RevisionComments', 'StatusType', 'Photo1', 'Photo2', 'Photo3', 'OptionalData1', 'OptionalData2', 'OptionalData3', 'OptionalData4', 'OptionalData5', 'OptionalData6', 'OptionalData7', 'OptionalData8', 'OptionalData9', 'OptionalData10', 'OptionalData11', 'OptionalData12', 'ApprovedByUserUID', 'SubmittedStatusType', 'SubmittedUserUID', 'ResponseStatusType', 'Response', 'ResponceErrorDescription'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'NewAssetFlag', 'NonAssetLocationFlag', 'CGEFlag', 'AOCFlag', 'InspectFlag', 'LeakIndicationFlag', 'OtherIndicationFlag', 'ApprovedFlag', 'SubmittedFlag', 'CompletedFlag'], 'integer'],
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
            'AssetID' => 'Asset ID',
            'AssetUID' => 'Asset Uid',
            'MapGridUID' => 'Map Grid Uid',
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
            'NewAssetFlag' => 'New Asset Flag',
            'NonAssetLocationFlag' => 'Non Asset Location Flag',
            'CGEFlag' => 'Cgeflag',
            'AOCFlag' => 'Aocflag',
            'InspectFlag' => 'Inspect Flag',
            'LeakIndicationFlag' => 'Leak Indication Flag',
            'OtherIndicationFlag' => 'Other Indication Flag',
            'Photo1' => 'Photo1',
            'Photo2' => 'Photo2',
            'Photo3' => 'Photo3',
            'OptionalData1' => 'Optional Data1',
            'OptionalData2' => 'Optional Data2',
            'OptionalData3' => 'Optional Data3',
            'OptionalData4' => 'Optional Data4',
            'OptionalData5' => 'Optional Data5',
            'OptionalData6' => 'Optional Data6',
            'OptionalData7' => 'Optional Data7',
            'OptionalData8' => 'Optional Data8',
            'OptionalData9' => 'Optional Data9',
            'OptionalData10' => 'Optional Data10',
            'OptionalData11' => 'Optional Data11',
            'OptionalData12' => 'Optional Data12',
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
