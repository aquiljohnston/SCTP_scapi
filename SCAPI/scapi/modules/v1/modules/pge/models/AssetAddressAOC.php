<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAssetAddressAOC".
 *
 * @property integer $AssetAddressAOCID
 * @property string $AssetAddressAOCUID
 * @property string $AssetAddressUID
 * @property string $AssetInspectionUID
 * @property string $InspectionRequestUID
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
 * @property string $AOCType
 * @property string $AOCReasonType
 * @property string $AOCOther
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
 * @property string $SubmittedUserUID
 * @property string $SubmittedDTLT
 * @property string $ResponseStatusType
 * @property string $Responsecomments
 * @property string $ResponceErrorComments
 * @property string $ResponseDTLT
 * @property integer $CompletedFlag
 * @property string $CompletedDTLT
 * @property string $DateFound
 * @property string $MeterNumber
 * @property string $GPSSource
 * @property string $GPSTime
 * @property integer $FixQuality
 * @property integer $NumberOfSatellites
 * @property double $HDOP
 * @property double $AltitudemetersAboveMeanSeaLevel
 * @property double $HeightOfGeoid
 * @property double $TimeSecondsSinceLastDGPS
 * @property string $ChecksumData
 * @property double $Bearing
 * @property double $Speed
 * @property string $GPSStatus
 * @property integer $NumberOfGPSAttempts
 * @property string $MasterLeakLogUID
 * @property string $ActivityUID
 */
class AssetAddressAOC extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAssetAddressAOC';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetAddressAOCUID', 'AssetAddressUID', 'AssetInspectionUID', 'InspectionRequestUID', 'MapGridUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'GPSType', 'GPSSentence', 'SHAPE', 'Comments', 'RevisionComments', 'StatusType', 'AOCType', 'AOCReasonType', 'AOCOther', 'Photo1', 'Photo2', 'Photo3', 'OptionalData1', 'OptionalData2', 'OptionalData3', 'OptionalData4', 'OptionalData5', 'ApprovedByUserUID', 'SubmittedStatusType', 'SubmittedUserUID', 'ResponseStatusType', 'Responsecomments', 'ResponceErrorComments', 'MeterNumber', 'GPSSource', 'GPSTime', 'ChecksumData', 'GPSStatus', 'MasterLeakLogUID', 'ActivityUID'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'ApprovedFlag', 'SubmittedFlag', 'CompletedFlag', 'FixQuality', 'NumberOfSatellites', 'NumberOfGPSAttempts'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset', 'SrcOpenDTLT', 'SrcClosedDTLT', 'ApprovedDTLT', 'SubmittedDTLT', 'ResponseDTLT', 'CompletedDTLT', 'DateFound'], 'safe'],
            [['Latitude', 'Longitude', 'HDOP', 'AltitudemetersAboveMeanSeaLevel', 'HeightOfGeoid', 'TimeSecondsSinceLastDGPS', 'Bearing', 'Speed'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AssetAddressAOCID' => 'Asset Address Aocid',
            'AssetAddressAOCUID' => 'Asset Address Aocuid',
            'AssetAddressUID' => 'Asset Address Uid',
            'AssetInspectionUID' => 'Asset Inspection Uid',
            'InspectionRequestUID' => 'Inspection Request Uid',
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
            'AOCType' => 'Aoctype',
            'AOCReasonType' => 'Aocreason Type',
            'AOCOther' => 'Aocother',
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
            'SubmittedUserUID' => 'Submitted User Uid',
            'SubmittedDTLT' => 'Submitted Dtlt',
            'ResponseStatusType' => 'Response Status Type',
            'Responsecomments' => 'Responsecomments',
            'ResponceErrorComments' => 'Responce Error Comments',
            'ResponseDTLT' => 'Response Dtlt',
            'CompletedFlag' => 'Completed Flag',
            'CompletedDTLT' => 'Completed Dtlt',
            'DateFound' => 'Date Found',
            'MeterNumber' => 'Meter Number',
            'GPSSource' => 'Gpssource',
            'GPSTime' => 'Gpstime',
            'FixQuality' => 'Fix Quality',
            'NumberOfSatellites' => 'Number Of Satellites',
            'HDOP' => 'Hdop',
            'AltitudemetersAboveMeanSeaLevel' => 'Altitudemeters Above Mean Sea Level',
            'HeightOfGeoid' => 'Height Of Geoid',
            'TimeSecondsSinceLastDGPS' => 'Time Seconds Since Last Dgps',
            'ChecksumData' => 'Checksum Data',
            'Bearing' => 'Bearing',
            'Speed' => 'Speed',
            'GPSStatus' => 'Gpsstatus',
            'NumberOfGPSAttempts' => 'Number Of Gpsattempts',
            'MasterLeakLogUID' => 'Master Leak Log Uid',
            'ActivityUID' => 'Activity Uid',
        ];
    }
}
