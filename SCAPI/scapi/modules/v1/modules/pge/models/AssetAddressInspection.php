<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAssetAddressInspection".
 *
 * @property integer $AssetAddressInspectionID
 * @property string $AssetAddressInspectionUID
 * @property string $AssetAddressUID
 * @property string $AssetInspectionUID
 * @property string $MapGridUID
 * @property string $InspectionRequestUID
 * @property string $MasterLeakLogUID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $SourceID
 * @property integer $InGridFlag
 * @property string $srvDTLT
 * @property string $srvDTLTOffset
 * @property string $srcDTLT
 * @property string $SrcOpenDTLT
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $StatusType
 * @property double $Latitude
 * @property double $Longitude
 * @property string $GPSSource
 * @property string $GPSType
 * @property string $GPSSentence
 * @property string $GPSTime
 * @property integer $FixQuality
 * @property integer $NumberOfSatellites
 * @property double $HDOP
 * @property double $AltitudemetersAboveMeanSeaLevel
 * @property double $HeightofGeoid
 * @property double $TimeSecondsSinceLastDGPS
 * @property string $ChecksumData
 * @property double $Bearing
 * @property double $Speed
 * @property string $GPSStatus
 * @property integer $NumberOfGPSAttempts
 * @property string $ActivityUID
 */
class AssetAddressInspection extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAssetAddressInspection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetAddressInspectionUID', 'AssetAddressUID', 'AssetInspectionUID', 'MapGridUID', 'InspectionRequestUID', 'MasterLeakLogUID', 'CreatedUserUID', 'ModifiedUserUID', 'SourceID', 'StatusType', 'GPSSource', 'GPSType', 'GPSSentence', 'GPSTime', 'ChecksumData', 'GPSStatus', 'ActivityUID'], 'string'],
            [['InGridFlag', 'Revision', 'ActiveFlag', 'FixQuality', 'NumberOfSatellites', 'NumberOfGPSAttempts'], 'integer'],
            [['srvDTLT', 'srvDTLTOffset', 'srcDTLT', 'SrcOpenDTLT'], 'safe'],
            [['Latitude', 'Longitude', 'HDOP', 'AltitudemetersAboveMeanSeaLevel', 'HeightofGeoid', 'TimeSecondsSinceLastDGPS', 'Bearing', 'Speed'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AssetAddressInspectionID' => 'Asset Address Inspection ID',
            'AssetAddressInspectionUID' => 'Asset Address Inspection Uid',
            'AssetAddressUID' => 'Asset Address Uid',
            'AssetInspectionUID' => 'Asset Inspection Uid',
            'MapGridUID' => 'Map Grid Uid',
            'InspectionRequestUID' => 'Inspection Request Uid',
            'MasterLeakLogUID' => 'Master Leak Log Uid',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'SourceID' => 'Source ID',
            'InGridFlag' => 'In Grid Flag',
            'srvDTLT' => 'Srv Dtlt',
            'srvDTLTOffset' => 'Srv Dtltoffset',
            'srcDTLT' => 'Src Dtlt',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'StatusType' => 'Status Type',
            'Latitude' => 'Latitude',
            'Longitude' => 'Longitude',
            'GPSSource' => 'Gpssource',
            'GPSType' => 'Gpstype',
            'GPSSentence' => 'Gpssentence',
            'GPSTime' => 'Gpstime',
            'FixQuality' => 'Fix Quality',
            'NumberOfSatellites' => 'Number Of Satellites',
            'HDOP' => 'Hdop',
            'AltitudemetersAboveMeanSeaLevel' => 'Altitudemeters Above Mean Sea Level',
            'HeightofGeoid' => 'Heightof Geoid',
            'TimeSecondsSinceLastDGPS' => 'Time Seconds Since Last Dgps',
            'ChecksumData' => 'Checksum Data',
            'Bearing' => 'Bearing',
            'Speed' => 'Speed',
            'GPSStatus' => 'Gpsstatus',
            'NumberOfGPSAttempts' => 'Number Of Gpsattempts',
            'ActivityUID' => 'Activity Uid',
			'SrcOpenDTLT' => 'Src Open Dtlt',
        ];
    }
}
