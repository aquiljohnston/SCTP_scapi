<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "BreadcrumbTb".
 *
 * @property integer $BreadcrumbID
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
 * @property string $BreadcrumbGPSTime
 * @property string $BreadcrumbShape
 * @property string $BreadcrumbWorkQueueFilter
 * @property string $BreadcrumbActivityType
 * @property integer $BreadcrumbTrackingGroupID
 * @property integer $BreadcrumbSatellites
 * @property double $BreadcrumbAltitude
 * @property integer $BreadcrumbMapPlat
 * @property integer $BreadcrumbArchiveFlag
 * @property string $BreadcrumbComments
 * @property string $BreadcrumbCreatedUserUID
 * @property string $BreadcrumbSrcDTLT
 * @property string $BreadcrumbSrvDTLT
 * @property string $BreadcrumbSrvDTLTOffset
 * @property string $BreadcrumbCreatedDate
 * @property double $BreadcrumbBatteryLevel
 */
class Breadcrumb extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'BreadcrumbTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['BreadcrumbUID', 'BreadcrumbActivityUID', 'BreadcrumbSourceID', 'BreadcrumbHeading', 'BreadcrumbDeviceID', 'BreadcrumbGPSAccuracy', 'BreadcrumbGPSType', 'BreadcrumbGPSSentence', 'BreadcrumbShape', 'BreadcrumbWorkQueueFilter', 'BreadcrumbActivityType', 'BreadcrumbComments', 'BreadcrumbCreatedUserUID'], 'string'],
            [['BreadcrumbLatitude', 'BreadcrumbLongitude', 'BreadcrumbSpeed', 'BreadcrumbAltitude', 'BreadcrumbBatteryLevel'], 'number'],
            [['BreadcrumbGPSTime', 'BreadcrumbSrcDTLT', 'BreadcrumbSrvDTLT', 'BreadcrumbSrvDTLTOffset', 'BreadcrumbCreatedDate'], 'safe'],
            [['BreadcrumbTrackingGroupID', 'BreadcrumbSatellites', 'BreadcrumbMapPlat', 'BreadcrumbArchiveFlag'], 'integer']
        ];
    }

    /**
     * @inheritdoc
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
            'BreadcrumbGPSTime' => 'Breadcrumb Gpstime',
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
        ];
    }
}
