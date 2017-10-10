<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "tInspection".
 *
 * @property integer $ID
 * @property string $InspectionTabletID
 * @property integer $ActivityID
 * @property integer $WorkQueueID
 * @property string $WorkQueueStatus
 * @property string $MapGrid
 * @property integer $IsAdHocFlag
 * @property integer $IsInGridFlag
 * @property integer $IsCGEFlag
 * @property integer $IsAOCFlag
 * @property integer $IsIndicationFlag
 * @property integer $IsPipelineFlag
 * @property integer $AGLeakCounter
 * @property integer $BGLeakCounter
 * @property integer $Grade1Counter
 * @property integer $CreatedBy
 * @property string $CreatedDate
 * @property integer $AssetID
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
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property integer $IsWorkOrderUpdated
 * @property string $Photo1Path
 *
 * @property UserTb $createdBy
 */
class Inspection extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tInspection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['InspectionTabletID', 'MapGrid', 'GPSType', 'GPSSentence', 'GPSTime', 'ChecksumData', 'Photo1Path'], 'string'],
            [['WorkQueueStatus', 'ActivityID', 'WorkQueueID', 'IsAdHocFlag', 'IsInGridFlag', 'IsCGEFlag', 'IsAOCFlag', 'IsIndicationFlag', 'IsPipelineFlag', 'AGLeakCounter', 'BGLeakCounter', 'Grade1Counter', 'CreatedBy', 'AssetID', 'FixQuality', 'NumberOfSatellites', 'NumberOfGPSAttempts', 'IsWorkOrderUpdated'], 'integer'],
            [['CreatedDate', 'SrvDTLT', 'SrvDTLTOffset'], 'safe'],
            [['Latitude', 'Longitude', 'HDOP', 'AltitudeMetersAboveMeanSeaLevel', 'HeightOfGeoid', 'TimeSecondsSinceLastDGPS', 'Bearing', 'Speed'], 'number'],
            [['CreatedBy'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedBy' => 'UserID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'InspectionTabletID' => 'Inspection Tablet ID',
            'ActivityID' => 'Activity ID',
            'WorkQueueID' => 'Work Queue ID',
            'WorkQueueStatus' => 'Work Queue Status',
            'MapGrid' => 'Map Grid',
            'IsAdHocFlag' => 'Is Ad Hoc Flag',
            'IsInGridFlag' => 'Is In Grid Flag',
            'IsCGEFlag' => 'Is Cgeflag',
            'IsAOCFlag' => 'Is Aocflag',
            'IsIndicationFlag' => 'Is Indication Flag',
            'IsPipelineFlag' => 'Is Pipeline Flag',
            'AGLeakCounter' => 'Agleak Counter',
            'BGLeakCounter' => 'Bgleak Counter',
            'Grade1Counter' => 'Grade1 Counter',
            'CreatedBy' => 'Created By',
            'CreatedDate' => 'Created Date',
            'AssetID' => 'Asset ID',
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
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'IsWorkOrderUpdated' => 'Is Work Order Updated',
            'Photo1Path' => 'Photo 1 Path',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'CreatedBy']);
    }
}
