<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "tInspection".
 *
 * @property int $ID
 * @property string $InspectionTabletID
 * @property int $ActivityID
 * @property int $WorkQueueID
 * @property int $WorkQueueStatus
 * @property string $MapGrid
 * @property int $IsAdHocFlag
 * @property int $IsInGridFlag
 * @property int $IsCGEFlag
 * @property int $IsAOCFlag
 * @property int $IsIndicationFlag
 * @property int $IsPipelineFlag
 * @property int $AGLeakCounter
 * @property int $BGLeakCounter
 * @property int $Grade1Counter
 * @property int $CreatedBy
 * @property string $CreatedDate
 * @property int $AssetID
 * @property double $Latitude
 * @property double $Longitude
 * @property string $GPSType
 * @property string $GPSSentence
 * @property string $GPSTime
 * @property double $FixQuality
 * @property int $NumberOfSatellites
 * @property double $HDOP
 * @property double $AltitudeMetersAboveMeanSeaLevel
 * @property double $HeightOfGeoid
 * @property double $TimeSecondsSinceLastDGPS
 * @property string $ChecksumData
 * @property double $Bearing
 * @property double $Speed
 * @property int $NumberOfGPSAttempts
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property int $IsWorkOrderUpdated
 * @property string $Photo1Path
 * @property string $PipelineType
 * @property string $Comments
 * @property int $HardToLocateFlag
 * @property string $MeterNumberPhoto1Path
 * @property string $MeterNumberPhoto2Path
 * @property string $MeterNumberPhoto3Path
 * @property string $TaskOutActivityUID
 *
 * @property UserTb $createdBy
 */
class Inspection extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tInspection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['InspectionTabletID', 'MapGrid', 'GPSType', 'GPSSentence', 'GPSTime', 'ChecksumData', 'Photo1Path', 'PipelineType', 'Comments', 'MeterNumberPhoto1Path', 'MeterNumberPhoto2Path', 'MeterNumberPhoto3Path', 'TaskOutActivityUID'], 'string'],
            [['ActivityID', 'WorkQueueID', 'WorkQueueStatus', 'IsAdHocFlag', 'IsInGridFlag', 'IsCGEFlag', 'IsAOCFlag', 'IsIndicationFlag', 'IsPipelineFlag', 'AGLeakCounter', 'BGLeakCounter', 'Grade1Counter', 'CreatedBy', 'AssetID', 'NumberOfSatellites', 'NumberOfGPSAttempts', 'IsWorkOrderUpdated', 'HardToLocateFlag'], 'integer'],
            [['CreatedDate', 'SrvDTLT', 'SrvDTLTOffset'], 'safe'],
            [['Latitude', 'Longitude', 'FixQuality', 'HDOP', 'AltitudeMetersAboveMeanSeaLevel', 'HeightOfGeoid', 'TimeSecondsSinceLastDGPS', 'Bearing', 'Speed'], 'number'],
            [['CreatedBy'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedBy' => 'UserID']],
        ];
    }

    /**
     * {@inheritdoc}
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
            'Photo1Path' => 'Photo1 Path',
            'PipelineType' => 'Pipeline Type',
            'Comments' => 'Comments',
            'HardToLocateFlag' => 'Hard To Locate Flag',
            'MeterNumberPhoto1Path' => 'Meter Number Photo1 Path',
            'MeterNumberPhoto2Path' => 'Meter Number Photo2 Path',
            'MeterNumberPhoto3Path' => 'Meter Number Photo3 Path',
            'TaskOutActivityUID' => 'Task Out Activity Uid',
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
