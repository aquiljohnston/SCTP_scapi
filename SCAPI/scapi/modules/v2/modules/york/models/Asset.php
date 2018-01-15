<?php

namespace app\modules\v2\modules\york\models;

use Yii;
use app\modules\v2\models\BaseUser;

/**
 * This is the model class for table "tAsset".
 *
 * @property integer $ID
 * @property string $AssetTabletID
 * @property integer $InspectionID
 * @property string $MapGrid
 * @property integer $CreatedUserID
 * @property string $HouseNo
 * @property string $Street
 * @property string $Apt
 * @property string $City
 * @property string $State
 * @property string $ReverseGeoLocationString
 * @property string $MeterID
 * @property string $PipelineType
 * @property string $Grade1ReleaseReasonType
 * @property string $Grade1ReleaseDateTime
 * @property string $Comments
 * @property string $Photo1Path
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property string $Inspection
 * @property integer $AOCs
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
 * @property string $CheckSumData
 * @property double $Bearing
 * @property double $Speed
 * @property integer $NumberOfGPSAttempts
 * @property integer $Zip
 *
 * @property BaseUser $createdUser
 */
class Asset extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tAsset';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetTabletID', 'MapGrid', 'HouseNo', 'Street', 'Apt', 'City', 'State', 'ReverseGeoLocationString', 'MeterID', 'PipelineType', 'Grade1ReleaseReasonType', 'Comments', 'Photo1Path', 'Inspection', 'GPSType', 'GPSSentence', 'GPSTime', 'CheckSumData', 'Zip'], 'string'],
            [['InspectionID', 'CreatedUserID', 'AOCs', 'NumberOfSatellites', 'NumberOfGPSAttempts'], 'integer'],
            [['Grade1ReleaseDateTime', 'SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset'], 'safe'],
            [['Latitude', 'Longitude', 'FixQuality', 'HDOP', 'AltitudeMetersAboveMeanSeaLevel', 'HeightOfGeoid', 'TimeSecondsSinceLastDGPS', 'Bearing', 'Speed'], 'number'],
            [['CreatedUserID'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedUserID' => 'UserID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'AssetTabletID' => 'Asset Tablet ID',
            'InspectionID' => 'Inspection ID',
            'MapGrid' => 'Map Grid',
            'CreatedUserID' => 'Created User ID',
            'HouseNo' => 'House No',
            'Street' => 'Street',
            'Apt' => 'Apt',
            'City' => 'City',
            'State' => 'State',
            'ReverseGeoLocationString' => 'Reverse Geo Location String',
            'MeterID' => 'Meter ID',
            'PipelineType' => 'Pipeline Type',
            'Grade1ReleaseReasonType' => 'Grade1 Release Reason Type',
            'Grade1ReleaseDateTime' => 'Grade1 Release Date Time',
            'Comments' => 'Comments',
            'Photo1Path' => 'Photo1 Path',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'Inspection' => 'Inspection',
            'AOCs' => 'Aocs',
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
            'CheckSumData' => 'Check Sum Data',
            'Bearing' => 'Bearing',
            'Speed' => 'Speed',
            'NumberOfGPSAttempts' => 'Number Of Gpsattempts',
            'Zip' => 'Zip',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'CreatedUserID']);
    }
}
