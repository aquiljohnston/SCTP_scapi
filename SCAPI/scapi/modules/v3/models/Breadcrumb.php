<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "BreadcrumbTb".
 *
 * @property int $BreadcrumbID
 * @property string|null $BreadcrumbUID
 * @property string|null $BreadcrumbActivityUID
 * @property string|null $BreadcrumbSourceID
 * @property float $BreadcrumbLatitude
 * @property float $BreadcrumbLongitude
 * @property float|null $BreadcrumbSpeed
 * @property string|null $BreadcrumbHeading
 * @property string|null $BreadcrumbDeviceID
 * @property string|null $BreadcrumbGPSAccuracy
 * @property string|null $BreadcrumbGPSType
 * @property string|null $BreadcrumbGPSSentence
 * @property string|null $BreadcrumbShape
 * @property string|null $BreadcrumbWorkQueueFilter
 * @property string|null $BreadcrumbActivityType
 * @property int|null $BreadcrumbTrackingGroupID
 * @property float|null $BreadcrumbSatellites
 * @property float|null $BreadcrumbAltitude
 * @property string|null $BreadcrumbMapPlat
 * @property int|null $BreadcrumbArchiveFlag
 * @property string|null $BreadcrumbComments
 * @property string|null $BreadcrumbCreatedUserUID
 * @property string|null $BreadcrumbSrcDTLT
 * @property string|null $BreadcrumbSrvDTLT
 * @property string|null $BreadcrumbSrvDTLTOffset
 * @property string|null $BreadcrumbCreatedDate
 * @property float|null $BreadcrumbBatteryLevel
 * @property string|null $BreadcrumbGPSTime
 * @property int|null $IsStationary
 * @property float|null $PaceOfTravel
 * @property int|null $IsDistanceBased
 * @property float|null $DistanceTraveled
 * @property float|null $OriginBreadcrumbLatitude
 * @property float|null $OriginBreadcrumbLongitude
 * @property string|null $SpeedAttribute
 * @property int|null $ProjectID
 * @property int|null $TaskID
 *
 * @property ProjectTb $project
 * @property RefTask $task
 */
class Breadcrumb extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'BreadcrumbTb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['BreadcrumbUID', 'BreadcrumbActivityUID', 'BreadcrumbSourceID', 'BreadcrumbHeading', 'BreadcrumbDeviceID', 'BreadcrumbGPSAccuracy', 'BreadcrumbGPSType', 'BreadcrumbGPSSentence', 'BreadcrumbShape', 'BreadcrumbWorkQueueFilter', 'BreadcrumbActivityType', 'BreadcrumbMapPlat', 'BreadcrumbComments', 'BreadcrumbCreatedUserUID', 'SpeedAttribute'], 'string'],
            [['BreadcrumbLatitude', 'BreadcrumbLongitude', 'BreadcrumbSpeed', 'BreadcrumbSatellites', 'BreadcrumbAltitude', 'BreadcrumbBatteryLevel', 'PaceOfTravel', 'DistanceTraveled', 'OriginBreadcrumbLatitude', 'OriginBreadcrumbLongitude'], 'number'],
            [['BreadcrumbTrackingGroupID', 'BreadcrumbArchiveFlag', 'IsStationary', 'IsDistanceBased', 'ProjectID', 'TaskID'], 'integer'],
            [['BreadcrumbSrcDTLT', 'BreadcrumbSrvDTLT', 'BreadcrumbSrvDTLTOffset', 'BreadcrumbCreatedDate', 'BreadcrumbGPSTime'], 'safe'],
            [['ProjectID'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['ProjectID' => 'ProjectID']],
            [['TaskID'], 'exist', 'skipOnError' => true, 'targetClass' => Task::className(), 'targetAttribute' => ['TaskID' => 'TaskID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'BreadcrumbID' => 'Breadcrumb ID',
            'BreadcrumbUID' => 'Breadcrumb Uid',
            'BreadcrumbActivityUID' => 'Breadcrumb Activity Uid',
            'BreadcrumbSourceID' => 'Breadcrumb Source ID',
            'BreadcrumbLatitude' => 'Breadcrumb Latitude',
            'BreadcrumbLongitude' => 'Breadcrumb Longitude',
            'BreadcrumbSpeed' => 'Breadcrumb Speed',
            'BreadcrumbHeading' => 'Breadcrumb Heading',
            'BreadcrumbDeviceID' => 'Breadcrumb Device ID',
            'BreadcrumbGPSAccuracy' => 'Breadcrumb Gpsaccuracy',
            'BreadcrumbGPSType' => 'Breadcrumb Gpstype',
            'BreadcrumbGPSSentence' => 'Breadcrumb Gpssentence',
            'BreadcrumbShape' => 'Breadcrumb Shape',
            'BreadcrumbWorkQueueFilter' => 'Breadcrumb Work Queue Filter',
            'BreadcrumbActivityType' => 'Breadcrumb Activity Type',
            'BreadcrumbTrackingGroupID' => 'Breadcrumb Tracking Group ID',
            'BreadcrumbSatellites' => 'Breadcrumb Satellites',
            'BreadcrumbAltitude' => 'Breadcrumb Altitude',
            'BreadcrumbMapPlat' => 'Breadcrumb Map Plat',
            'BreadcrumbArchiveFlag' => 'Breadcrumb Archive Flag',
            'BreadcrumbComments' => 'Breadcrumb Comments',
            'BreadcrumbCreatedUserUID' => 'Breadcrumb Created User Uid',
            'BreadcrumbSrcDTLT' => 'Breadcrumb Src Dtlt',
            'BreadcrumbSrvDTLT' => 'Breadcrumb Srv Dtlt',
            'BreadcrumbSrvDTLTOffset' => 'Breadcrumb Srv Dtltoffset',
            'BreadcrumbCreatedDate' => 'Breadcrumb Created Date',
            'BreadcrumbBatteryLevel' => 'Breadcrumb Battery Level',
            'BreadcrumbGPSTime' => 'Breadcrumb Gpstime',
            'IsStationary' => 'Is Stationary',
            'PaceOfTravel' => 'Pace Of Travel',
            'IsDistanceBased' => 'Is Distance Based',
            'DistanceTraveled' => 'Distance Traveled',
            'OriginBreadcrumbLatitude' => 'Origin Breadcrumb Latitude',
            'OriginBreadcrumbLongitude' => 'Origin Breadcrumb Longitude',
            'SpeedAttribute' => 'Speed Attribute',
            'ProjectID' => 'Project ID',
            'TaskID' => 'Task ID',
        ];
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['ProjectID' => 'ProjectID']);
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::className(), ['TaskID' => 'TaskID']);
    }
}
