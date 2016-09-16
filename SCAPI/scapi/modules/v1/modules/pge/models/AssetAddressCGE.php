<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAssetAddressCGE".
 *
 * @property integer $AssetAddressCGEID
 * @property string $AssetAddressCGEUID
 * @property string $AssetAddressUID
 * @property string $AssetAddressInspectionUID
 * @property string $MasterLeakLogUID
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
 * @property string $CGENIFType
 * @property string $CGEReasonType
 * @property string $NIFReasonType
 * @property integer $CGECardFlag
 * @property string $CGECardNo
 * @property string $Photo1
 * @property string $Photo2
 * @property string $Photo3
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
class AssetAddressCGE extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAssetAddressCGE';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetAddressCGEUID', 'AssetAddressUID', 'MasterLeakLogUID', 'MapGridUID', 'ProjectID', 'CreatedUserUID', 'ModifiedUserUID', 'SrcDTLT', 'Revision', 'ActiveFlag', 'CompletedFlag'], 'required'],
            [['AssetAddressCGEUID', 'AssetAddressUID', 'AssetAddressInspectionUID', 'MasterLeakLogUID', 'MapGridUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'GPSType', 'GPSSentence', 'SHAPE', 'Comments', 'RevisionComments', 'StatusType', 'CGENIFType', 'CGEReasonType', 'NIFReasonType', 'CGECardNo', 'Photo1', 'Photo2', 'Photo3', 'ApprovedByUserUID', 'SubmittedStatusType', 'SubmittedUserUID', 'ResponseStatusType', 'Response', 'ResponceErrorDescription'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'CGECardFlag', 'ApprovedFlag', 'SubmittedFlag', 'CompletedFlag'], 'integer'],
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
            'AssetAddressCGEID' => 'Asset Address Cgeid',
            'AssetAddressCGEUID' => 'Asset Address Cgeuid',
            'AssetAddressUID' => 'Asset Address Uid',
            'AssetAddressInspectionUID' => 'Asset Address Inspection Uid',
            'MasterLeakLogUID' => 'Master Leak Log Uid',
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
            'CGENIFType' => 'Cgeniftype',
            'CGEReasonType' => 'Cgereason Type',
            'NIFReasonType' => 'Nifreason Type',
            'CGECardFlag' => 'Cgecard Flag',
            'CGECardNo' => 'Cgecard No',
            'Photo1' => 'Photo1',
            'Photo2' => 'Photo2',
            'Photo3' => 'Photo3',
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
