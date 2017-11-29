<?php

namespace app\modules\v2\models;

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
 * @property string $LeakGrade
 * @property string $LeakAboveOrBelow
 * @property string $DetectedByEquipment
 * @property string $EquipmentSerialNumber
 * @property string $Comments
 * @property double $Latitude
 * @property double $Longitude
 * @property string $GPSType
 * @property string $GPSSentence
 * @property string $GPSTime
 * @property double $FixQuality
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
 * @property string $ACGrade
 * @property integer $RiserPostBad
 * @property integer $RecommendToRetireInActiveService
 * @property integer $FacilitiesNeedToBeProtected
 * @property integer $Other
 * @property integer $AOCFlag
 * @property string $AccessIssues
 * @property string $CGE
 * @property integer $LeakRepaired
 * @property string $LeakFoundMainOrService
 * @property integer $BadDogPresent
 * @property string $NIFReason
 *
 * @property UserTb $createdByUser
 */
class ScanaEvent extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tEvent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EventTabletID', 'InspectionTabletID', 'LocationID', 'LocationAddress', 'City', 'State', 'MapGrid', 'Photo1Path', 'Photo2Path', 'Photo3Path', 'AOCReason', 'CGEReason', 'LeakNumber', 'LeakGrade', 'LeakAboveOrBelow', 'DetectedByEquipment', 'EquipmentSerialNumber', 'Comments', 'GPSType', 'GPSSentence', 'GPSTime', 'ChecksumData', 'ACGrade', 'AccessIssues', 'CGE', 'LeakFoundMainOrService', 'NIFReason'], 'string'],
            [['EventType', 'CreatedByUserID', 'NumberOfSatellites', 'NumberOfGPSAttempts', 'InspectionID', 'DeletedFlag', 'SplashGuardNeeded', 'SplashGuardInstalled', 'TracerWireMissing', 'TamperSealNotPresent', 'EnergyDiversionPresent', 'RiserPostBad', 'RecommendToRetireInActiveService', 'FacilitiesNeedToBeProtected', 'Other', 'AOCFlag', 'LeakRepaired', 'BadDogPresent'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset'], 'safe'],
            [['Latitude', 'Longitude', 'HDOP', 'AltitudeMetersAboveMeanSeaLevel', 'HeightOfGeoid', 'TimeSecondsSinceLastDGPS', 'Bearing', 'Speed', 'FixQuality'], 'number'],
            [['InspectionID'], 'required'],
            [['CreatedByUserID'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedByUserID' => 'UserID']],
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
            'LeakGrade' => 'Leak Grade',
            'LeakAboveOrBelow' => 'Leak Above Or Below',
            'DetectedByEquipment' => 'Detected By Equipment',
            'EquipmentSerialNumber' => 'Equipment Serial Number',
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
            'BadDogPresent' => 'Bad Dog Present',
			'NIFReason' => 'NIF Reason',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'CreatedByUserID']);
    }
}
