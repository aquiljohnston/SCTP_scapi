<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAssetAddress".
 *
 * @property integer $AssetAddressID
 * @property string $AssetAddressUID
 * @property string $AssetUID
 * @property string $AssetInspectionUID
 * @property string $MapGridUID
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
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
 * @property integer $AssetAddessCorrectionFlag
 * @property integer $AssetIDNumberCorrectionFlag
 * @property integer $AssetConfirmFlag
 * @property string $RouteNo
 * @property integer $RouteSeq
 * @property integer $SortOrder
 * @property string $AssetAccountNo
 * @property string $AssetAccountName
 * @property string $AssetName
 * @property string $AssetLocationID
 * @property string $AssetLocationCode
 * @property string $AssetIDStatus
 * @property string $AssetIDNo
 * @property string $AssetIDNoCorection
 * @property string $ReverseGeoHouseNo
 * @property string $ReverseGeoStreet1
 * @property string $ReverseGeoCity
 * @property string $ReverseGeoState
 * @property string $ReverseGeoZip
 * @property double $ReverseGeoLat
 * @property double $ReverseGeoLong
 * @property string $ReverseGeoQuality
 * @property integer $HouseNoNAFlag
 * @property string $HouseNo
 * @property string $Street1
 * @property string $Street2
 * @property string $AptSuite
 * @property string $AptDesc
 * @property string $Apt
 * @property string $City
 * @property string $State
 * @property string $ZIP
 * @property string $County
 * @property string $CountyCode
 * @property string $Photo1
 * @property string $Photo2
 * @property string $Photo3
 * @property integer $ApprovedFlag
 * @property string $ApprovedByUserUID
 * @property string $ApprovedDTLT
 * @property integer $SubmittedFlag
 * @property string $SubmittedStatusType
 * @property integer $SubmittedUserUID
 * @property string $SubmittedDTLT
 * @property string $ResponseStatusType
 * @property string $Response
 * @property string $ResponceErrorDescription
 * @property string $ResponseDTLT
 * @property integer $CompletedFlag
 * @property string $CompletedDTLT
 */
class AssetAddress extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAssetAddress';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetAddressUID', 'AssetUID', 'MapGridUID', 'ProjectID', 'CreatedUserUID', 'ModifiedUserUID', 'SrcDTLT', 'Revision', 'ActiveFlag', 'CompletedFlag'], 'required'],
            [['AssetAddressUID', 'AssetUID', 'AssetInspectionUID', 'MapGridUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'GPSType', 'GPSSentence', 'SHAPE', 'Comments', 'RevisionComments', 'StatusType', 'RouteNo', 'AssetAccountNo', 'AssetAccountName', 'AssetName', 'AssetLocationID', 'AssetLocationCode', 'AssetIDStatus', 'AssetIDNo', 'AssetIDNoCorection', 'ReverseGeoHouseNo', 'ReverseGeoStreet1', 'ReverseGeoCity', 'ReverseGeoState', 'ReverseGeoZip', 'ReverseGeoQuality', 'HouseNo', 'Street1', 'Street2', 'AptSuite', 'AptDesc', 'Apt', 'City', 'State', 'ZIP', 'County', 'CountyCode', 'Photo1', 'Photo2', 'Photo3', 'ApprovedByUserUID', 'SubmittedStatusType', 'ResponseStatusType', 'Response', 'ResponceErrorDescription'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'NewAssetFlag', 'NonAssetLocationFlag', 'AssetAddessCorrectionFlag', 'AssetIDNumberCorrectionFlag', 'AssetConfirmFlag', 'RouteSeq', 'SortOrder', 'HouseNoNAFlag', 'ApprovedFlag', 'SubmittedFlag', 'SubmittedUserUID', 'CompletedFlag'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset', 'ApprovedDTLT', 'SubmittedDTLT', 'ResponseDTLT', 'CompletedDTLT'], 'safe'],
            [['Latitude', 'Longitude', 'ReverseGeoLat', 'ReverseGeoLong'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AssetAddressID' => 'Asset Address ID',
            'AssetAddressUID' => 'Asset Address Uid',
            'AssetUID' => 'Asset Uid',
            'AssetInspectionUID' => 'Asset Inspection Uid',
            'MapGridUID' => 'Map Grid Uid',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
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
            'AssetAddessCorrectionFlag' => 'Asset Addess Correction Flag',
            'AssetIDNumberCorrectionFlag' => 'Asset Idnumber Correction Flag',
            'AssetConfirmFlag' => 'Asset Confirm Flag',
            'RouteNo' => 'Route No',
            'RouteSeq' => 'Route Seq',
            'SortOrder' => 'Sort Order',
            'AssetAccountNo' => 'Asset Account No',
            'AssetAccountName' => 'Asset Account Name',
            'AssetName' => 'Asset Name',
            'AssetLocationID' => 'Asset Location ID',
            'AssetLocationCode' => 'Asset Location Code',
            'AssetIDStatus' => 'Asset Idstatus',
            'AssetIDNo' => 'Asset Idno',
            'AssetIDNoCorection' => 'Asset Idno Corection',
            'ReverseGeoHouseNo' => 'Reverse Geo House No',
            'ReverseGeoStreet1' => 'Reverse Geo Street1',
            'ReverseGeoCity' => 'Reverse Geo City',
            'ReverseGeoState' => 'Reverse Geo State',
            'ReverseGeoZip' => 'Reverse Geo Zip',
            'ReverseGeoLat' => 'Reverse Geo Lat',
            'ReverseGeoLong' => 'Reverse Geo Long',
            'ReverseGeoQuality' => 'Reverse Geo Quality',
            'HouseNoNAFlag' => 'House No Naflag',
            'HouseNo' => 'House No',
            'Street1' => 'Street1',
            'Street2' => 'Street2',
            'AptSuite' => 'Apt Suite',
            'AptDesc' => 'Apt Desc',
            'Apt' => 'Apt',
            'City' => 'City',
            'State' => 'State',
            'ZIP' => 'Zip',
            'County' => 'County',
            'CountyCode' => 'County Code',
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
