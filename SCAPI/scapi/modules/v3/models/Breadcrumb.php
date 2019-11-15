<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "BreadcrumbTb".
 *
 * @property int $BreadcrumbID
 * @property string $BreadcrumbUID
 * @property string $BreadcrumbActivityUID
 * @property string $BreadcrumbSourceID
 * @property double $BreadcrumbLatitude
 * @property double $BreadcrumbLongitude
 * @property double $BreadcrumbSpeed
 * @property string $BreadcrumbHeading
 * @property string $BreadcrumbDeviceID
 * @property string $BreadcrumbGPSAccuracy
 * @property string $BreadcrumbGPSType
 * @property string $BreadcrumbGPSSentence
 * @property string $BreadcrumbShape
 * @property string $BreadcrumbWorkQueueFilter
 * @property string $BreadcrumbActivityType
 * @property int $BreadcrumbTrackingGroupID
 * @property int $BreadcrumbSatellites
 * @property double $BreadcrumbAltitude
 * @property string $BreadcrumbMapPlat
 * @property int $BreadcrumbArchiveFlag
 * @property string $BreadcrumbComments
 * @property string $BreadcrumbCreatedUserUID
 * @property string $BreadcrumbSrcDTLT
 * @property string $BreadcrumbSrvDTLT
 * @property string $BreadcrumbSrvDTLTOffset
 * @property string $BreadcrumbCreatedDate
 * @property double $BreadcrumbBatteryLevel
 * @property string $BreadcrumbGPSTime
 * @property int $IsStationary
 * @property string $PaceOfTravel
 * @property int $IsDistanceBased
 * @property string $DistanceTraveled
 * @property double $OriginBreadcrumbLatitude
 * @property double $OriginBreadcrumbLongitude
 * @property string $SpeedAttribute
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
            [['BreadcrumbTrackingGroupID', 'BreadcrumbArchiveFlag', 'IsStationary', 'IsDistanceBased'], 'integer'],
            [['BreadcrumbSrcDTLT', 'BreadcrumbSrvDTLT', 'BreadcrumbSrvDTLTOffset', 'BreadcrumbCreatedDate', 'BreadcrumbGPSTime'], 'safe'],
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
        ];
    }
}
