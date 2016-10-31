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
 * @property string $SrcOpenDTLT
 * @property integer $ElectrolysisSurveyFlag
 * @property string $RiserPipeSoilMeas
 * @property integer $DIMPSurveyFlag
 * @property string $DIMPRiserType
 * @property string $ServiceHeadAdapterType
 * @property integer $ManifoldSetFlag
 * @property integer $ServiceValueFlag
 * @property integer $FilterFlag
 * @property string $FilterSize
 * @property string $FilterMfg
 * @property string $FilterModel
 * @property integer $Regulator1Flag
 * @property string $Regulator1Size
 * @property string $Regulator1Mfg
 * @property string $Regulator1Model
 * @property integer $Regulator2Flag
 * @property string $Regulator2Size
 * @property string $Regulator2Mfg
 * @property string $Regulator2Model
 * @property integer $Regulator3Flag
 * @property string $Regulator3Size
 * @property string $Regulator3Mfg
 * @property string $Regulator3Model
 * @property string $MeterType
 * @property string $MeterMfg
 * @property string $MeterModel
 * @property integer $ECFlag
 * @property integer $AMRFlag
 * @property integer $DripTankFlag
 * @property string $Photo1
 * @property string $Photo2
 * @property string $Photo3
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
            [['AssetAddressInspectionUID', 'AssetAddressUID', 'AssetInspectionUID', 'MapGridUID', 'InspectionRequestUID', 'MasterLeakLogUID', 'CreatedUserUID', 'ModifiedUserUID', 'SourceID', 'StatusType', 'GPSSource', 'GPSType', 'GPSSentence', 'GPSTime', 'ChecksumData', 'GPSStatus', 'ActivityUID', 'RiserPipeSoilMeas', 'DIMPRiserType', 'ServiceHeadAdapterType', 'FilterSize', 'FilterMfg', 'FilterModel', 'Regulator1Size', 'Regulator1Mfg', 'Regulator1Model', 'Regulator2Size', 'Regulator2Mfg', 'Regulator2Model', 'Regulator3Size', 'Regulator3Mfg', 'Regulator3Model', 'MeterType', 'MeterMfg', 'MeterModel', 'Photo1', 'Photo2', 'Photo3'], 'string'],
            [['InGridFlag', 'Revision', 'ActiveFlag', 'FixQuality', 'NumberOfSatellites', 'NumberOfGPSAttempts', 'ElectrolysisSurveyFlag', 'DIMPSurveyFlag', 'ManifoldSetFlag', 'ServiceValueFlag', 'FilterFlag', 'Regulator1Flag', 'Regulator2Flag', 'Regulator3Flag', 'ECFlag', 'AMRFlag', 'DripTankFlag'], 'integer'],
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
            'ElectrolysisSurveyFlag' => 'Electrolysis Survey Flag',
            'RiserPipeSoilMeas' => 'Riser Pipe Soil Meas',
            'DIMPSurveyFlag' => 'Dimpsurvey Flag',
            'DIMPRiserType' => 'Dimpriser Type',
            'ServiceHeadAdapterType' => 'Service Head Adapter Type',
            'ManifoldSetFlag' => 'Manifold Set Flag',
            'ServiceValueFlag' => 'Service Value Flag',
            'FilterFlag' => 'Filter Flag',
            'FilterSize' => 'Filter Size',
            'FilterMfg' => 'Filter Mfg',
            'FilterModel' => 'Filter Model',
            'Regulator1Flag' => 'Regulator1 Flag',
            'Regulator1Size' => 'Regulator1 Size',
            'Regulator1Mfg' => 'Regulator1 Mfg',
            'Regulator1Model' => 'Regulator1 Model',
            'Regulator2Flag' => 'Regulator2 Flag',
            'Regulator2Size' => 'Regulator2 Size',
            'Regulator2Mfg' => 'Regulator2 Mfg',
            'Regulator2Model' => 'Regulator2 Model',
            'Regulator3Flag' => 'Regulator3 Flag',
            'Regulator3Size' => 'Regulator3 Size',
            'Regulator3Mfg' => 'Regulator3 Mfg',
            'Regulator3Model' => 'Regulator3 Model',
            'MeterType' => 'Meter Type',
            'MeterMfg' => 'Meter Mfg',
            'MeterModel' => 'Meter Model',
            'ECFlag' => 'Ecflag',
            'AMRFlag' => 'Amrflag',
            'DripTankFlag' => 'Drip Tank Flag',
            'Photo1' => 'Photo1',
            'Photo2' => 'Photo2',
            'Photo3' => 'Photo3',
        ];
    }
}
