<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tEvent".
 *
 * @property integer $ID
 * @property string $EventTabletID
 * @property integer $EventType
 * @property string $InspectionTabletID
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property integer $CreatedByUserID
 * @property string $LocationID
 * @property string $LocationAddress
 * @property string $City
 * @property string $State
 * @property string $MapGrid
 * @property string $Photo1Path
 * @property string $Photo2Path
 * @property string $Photo3Path
 * @property string $AOCReason
 * @property string $CGEReason
 * @property string $LeakNumber
 * @property string $LeakReportedTo
 * @property string $LeakPipelineSystemInvestigated
 * @property string $LeakDetectType
 * @property string $LeakGrade
 * @property string $LeakAboveOrBelow
 * @property integer $LeakMeterInspected
 * @property string $LeakMeterNumber
 * @property string $LeakMeterLeakLocation
 * @property integer $RiserOnly
 * @property integer $MultiMeter
 * @property string $PartOfSystem
 * @property string $CustomerType
 * @property string $SurfaceCondition
 * @property string $DetectedByEquipment
 * @property string $EquipmentSerialNumber
 * @property string $Collecting
 * @property string $ProbableCause
 * @property string $Soil
 * @property string $LELPercent
 * @property string $GASPercent
 * @property string $PPM
 * @property string $Negative
 * @property string $Pressure
 * @property string $Surface
 * @property string $PipeSize
 * @property string $PipeType
 * @property string $PipeCondition
 * @property string $Comments
 * @property double $Latitude
 * @property double $Longitude
 * @property string $GPSType
 * @property string $GPSSentence
 * @property string $GPSTime
 * @property integer $FixQuality
 * @property integer $NumberOfSatellites
 * @property double $HDOP
 * @property double $AltitudeMetersAboveMeanSeaLevel
 * @property double $HeightOfGeoid
 * @property double $TimeSecondsSinceLastDGPS
 * @property string $ChecksumData
 * @property double $Bearing
 * @property double $Speed
 * @property integer $NumberOfGPSAttempts
 * @property integer $InspectionID
 * @property integer $DeletedFlag
 * @property integer $SplashGuardNeeded
 * @property integer $SplashGuardInstalled
 * @property integer $TracerWireMissing
 * @property integer $TamperSealNotPresent
 * @property integer $EnergyDiversionPresent
 * @property integer $ACGrade
 * @property integer $RiserPostBad
 * @property integer $RecommendToRetireInActiveService
 * @property integer $FacilitiesNeedToBeProtected
 * @property integer $Other
 * @property integer $AOCFlag
 * @property string $AccessIssues
 * @property string $CGE
 * @property integer $LeakRepaired
 * @property string $LeakFoundMainOrService
 *
 * @property UserTb $createdByUser
 * @property RStatusLookup $eventType
 */
class ScanaEvent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tEvent';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('scanaDevDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EventTabletID', 'InspectionTabletID', 'LocationID', 'LocationAddress', 'City', 'State', 'MapGrid', 'Photo1Path', 'Photo2Path', 'Photo3Path', 'AOCReason', 'CGEReason', 'LeakNumber', 'LeakReportedTo', 'LeakPipelineSystemInvestigated', 'LeakDetectType', 'LeakGrade', 'LeakAboveOrBelow', 'LeakMeterNumber', 'LeakMeterLeakLocation', 'PartOfSystem', 'CustomerType', 'SurfaceCondition', 'DetectedByEquipment', 'EquipmentSerialNumber', 'Collecting', 'ProbableCause', 'Soil', 'LELPercent', 'GASPercent', 'PPM', 'Negative', 'Pressure', 'Surface', 'PipeSize', 'PipeType', 'PipeCondition', 'Comments', 'GPSType', 'GPSSentence', 'GPSTime', 'ChecksumData', 'AccessIssues', 'CGE', 'LeakFoundMainOrService'], 'string'],
            [['EventType', 'CreatedByUserID', 'LeakMeterInspected', 'RiserOnly', 'MultiMeter', 'FixQuality', 'NumberOfSatellites', 'NumberOfGPSAttempts', 'InspectionID', 'DeletedFlag', 'SplashGuardNeeded', 'SplashGuardInstalled', 'TracerWireMissing', 'TamperSealNotPresent', 'EnergyDiversionPresent', 'ACGrade', 'RiserPostBad', 'RecommendToRetireInActiveService', 'FacilitiesNeedToBeProtected', 'Other', 'AOCFlag', 'LeakRepaired'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset'], 'safe'],
            [['Latitude', 'Longitude', 'HDOP', 'AltitudeMetersAboveMeanSeaLevel', 'HeightOfGeoid', 'TimeSecondsSinceLastDGPS', 'Bearing', 'Speed'], 'number'],
            [['InspectionID'], 'required'],
            [['CreatedByUserID'], 'exist', 'skipOnError' => true, 'targetClass' => UserTb::className(), 'targetAttribute' => ['CreatedByUserID' => 'UserID']],
            [['EventType'], 'exist', 'skipOnError' => true, 'targetClass' => RStatusLookup::className(), 'targetAttribute' => ['EventType' => 'StatusCode']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'EventTabletID' => 'Event Tablet ID',
            'EventType' => 'Event Type',
            'InspectionTabletID' => 'Inspection Tablet ID',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'CreatedByUserID' => 'Created By User ID',
            'LocationID' => 'Location ID',
            'LocationAddress' => 'Location Address',
            'City' => 'City',
            'State' => 'State',
            'MapGrid' => 'Map Grid',
            'Photo1Path' => 'Photo1 Path',
            'Photo2Path' => 'Photo2 Path',
            'Photo3Path' => 'Photo3 Path',
            'AOCReason' => 'Aocreason',
            'CGEReason' => 'Cgereason',
            'LeakNumber' => 'Leak Number',
            'LeakReportedTo' => 'Leak Reported To',
            'LeakPipelineSystemInvestigated' => 'Leak Pipeline System Investigated',
            'LeakDetectType' => 'Leak Detect Type',
            'LeakGrade' => 'Leak Grade',
            'LeakAboveOrBelow' => 'Leak Above Or Below',
            'LeakMeterInspected' => 'Leak Meter Inspected',
            'LeakMeterNumber' => 'Leak Meter Number',
            'LeakMeterLeakLocation' => 'Leak Meter Leak Location',
            'RiserOnly' => 'Riser Only',
            'MultiMeter' => 'Multi Meter',
            'PartOfSystem' => 'Part Of System',
            'CustomerType' => 'Customer Type',
            'SurfaceCondition' => 'Surface Condition',
            'DetectedByEquipment' => 'Detected By Equipment',
            'EquipmentSerialNumber' => 'Equipment Serial Number',
            'Collecting' => 'Collecting',
            'ProbableCause' => 'Probable Cause',
            'Soil' => 'Soil',
            'LELPercent' => 'Lelpercent',
            'GASPercent' => 'Gaspercent',
            'PPM' => 'Ppm',
            'Negative' => 'Negative',
            'Pressure' => 'Pressure',
            'Surface' => 'Surface',
            'PipeSize' => 'Pipe Size',
            'PipeType' => 'Pipe Type',
            'PipeCondition' => 'Pipe Condition',
            'Comments' => 'Comments',
            'Latitude' => 'Latitude',
            'Longitude' => 'Longitude',
            'GPSType' => 'Gpstype',
            'GPSSentence' => 'Gpssentence',
            'GPSTime' => 'Gpstime',
            'FixQuality' => 'Fix Quality',
            'NumberOfSatellites' => 'Number Of Satellites',
            'HDOP' => 'Hdop',
            'AltitudeMetersAboveMeanSeaLevel' => 'Altitude Meters Above Mean Sea Level',
            'HeightOfGeoid' => 'Height Of Geoid',
            'TimeSecondsSinceLastDGPS' => 'Time Seconds Since Last Dgps',
            'ChecksumData' => 'Checksum Data',
            'Bearing' => 'Bearing',
            'Speed' => 'Speed',
            'NumberOfGPSAttempts' => 'Number Of Gpsattempts',
            'InspectionID' => 'Inspection ID',
            'DeletedFlag' => 'Deleted Flag',
            'SplashGuardNeeded' => 'Splash Guard Needed',
            'SplashGuardInstalled' => 'Splash Guard Installed',
            'TracerWireMissing' => 'Tracer Wire Missing',
            'TamperSealNotPresent' => 'Tamper Seal Not Present',
            'EnergyDiversionPresent' => 'Energy Diversion Present',
            'ACGrade' => 'Acgrade',
            'RiserPostBad' => 'Riser Post Bad',
            'RecommendToRetireInActiveService' => 'Recommend To Retire In Active Service',
            'FacilitiesNeedToBeProtected' => 'Facilities Need To Be Protected',
            'Other' => 'Other',
            'AOCFlag' => 'Aocflag',
            'AccessIssues' => 'Access Issues',
            'CGE' => 'Cge',
            'LeakRepaired' => 'Leak Repaired',
            'LeakFoundMainOrService' => 'Leak Found Main Or Service',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(UserTb::className(), ['UserID' => 'CreatedByUserID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEventType()
    {
        return $this->hasOne(RStatusLookup::className(), ['StatusCode' => 'EventType']);
    }
}
