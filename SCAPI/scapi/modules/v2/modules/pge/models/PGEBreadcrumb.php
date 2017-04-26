<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "BreadcrumbTb".
 *
 * @property integer $BreadcrumbID
 * @property string $BreadcrumbUID
 * @property string $BreadcrumbActivityUID
 * @property integer $BreadcrumbProjectID
 * @property string $BreadcrumbSourceID
 * @property string $BreadcrumbCreatedUserUID
 * @property string $BreadcrumbSrcDTLT
 * @property string $BreadcrumbSrvDTLTOffset
 * @property string $BreadcrumbSrvDTLT
 * @property string $BreadcrumbGPSType
 * @property string $BreadcrumbGPSSentence
 * @property double $BreadcrumbLatitude
 * @property double $BreadcrumbLongitude
 * @property string $BreadcrumbShape
 * @property string $BreadcrumbActivityType
 * @property string $BreadcrumbWorkQueueFilter
 * @property double $BreadcrumbBatteryLevel
 * @property string $BreadcrumbGPSTime
 * @property double $BreadcrumbSpeed
 * @property string $BreadcrumbHeading
 * @property string $BreadcrumbGPSAccuracy
 * @property integer $BreadcrumbSatellites
 * @property double $BreadcrumbAltitude
 * @property integer $BreadcrumbTrackingGroupID
 * @property string $BreadcrumbMapPlat
 * @property integer $BreadcrumbArchiveFlag
 * @property string $BreadcrumbComments
 * @property string $BreadcrumbCreatedDate
 * @property string $BreadcrumbDeviceID
 */
class PGEBreadcrumb extends \app\modules\v2\models\BaseActiveRecord
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
            [['BreadcrumbUID', 'BreadcrumbProjectID', 'BreadcrumbCreatedUserUID', 'BreadcrumbSrcDTLT'], 'required'],
            [['BreadcrumbUID', 'BreadcrumbActivityUID', 'BreadcrumbSourceID', 'BreadcrumbCreatedUserUID', 'BreadcrumbGPSType', 'BreadcrumbGPSSentence', 'BreadcrumbShape', 'BreadcrumbActivityType', 'BreadcrumbWorkQueueFilter', 'BreadcrumbHeading', 'BreadcrumbGPSAccuracy', 'BreadcrumbMapPlat', 'BreadcrumbComments', 'BreadcrumbDeviceID'], 'string'],
            [['BreadcrumbProjectID', 'BreadcrumbSatellites', 'BreadcrumbTrackingGroupID', 'BreadcrumbArchiveFlag'], 'integer'],
            [['BreadcrumbSrcDTLT', 'BreadcrumbSrvDTLTOffset', 'BreadcrumbSrvDTLT', 'BreadcrumbGPSTime', 'BreadcrumbCreatedDate'], 'safe'],
            [['BreadcrumbLatitude', 'BreadcrumbLongitude', 'BreadcrumbBatteryLevel', 'BreadcrumbSpeed', 'BreadcrumbAltitude'], 'number']
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
            'BreadcrumbProjectID' => 'Breadcrumb Project ID',
            'BreadcrumbSourceID' => 'Breadcrumb Source ID',
            'BreadcrumbCreatedUserUID' => 'Breadcrumb Created User Uid',
            'BreadcrumbSrcDTLT' => 'Breadcrumb Src Dtlt',
            'BreadcrumbSrvDTLTOffset' => 'Breadcrumb Srv Dtltoffset',
            'BreadcrumbSrvDTLT' => 'Breadcrumb Srv Dtlt',
            'BreadcrumbGPSType' => 'Breadcrumb Gpstype',
            'BreadcrumbGPSSentence' => 'Breadcrumb Gpssentence',
            'BreadcrumbLatitude' => 'Breadcrumb Latitude',
            'BreadcrumbLongitude' => 'Breadcrumb Longitude',
            'BreadcrumbShape' => 'Breadcrumb Shape',
            'BreadcrumbActivityType' => 'Breadcrumb Activity Type',
            'BreadcrumbWorkQueueFilter' => 'Breadcrumb Work Queue Filter',
            'BreadcrumbBatteryLevel' => 'Breadcrumb Battery Level',
            'BreadcrumbGPSTime' => 'Breadcrumb Gpstime',
            'BreadcrumbSpeed' => 'Breadcrumb Speed',
            'BreadcrumbHeading' => 'Breadcrumb Heading',
            'BreadcrumbGPSAccuracy' => 'Breadcrumb Gpsaccuracy',
            'BreadcrumbSatellites' => 'Breadcrumb Satellites',
            'BreadcrumbAltitude' => 'Breadcrumb Altitude',
            'BreadcrumbTrackingGroupID' => 'Breadcrumb Tracking Group ID',
            'BreadcrumbMapPlat' => 'Breadcrumb Map Plat',
            'BreadcrumbArchiveFlag' => 'Breadcrumb Archive Flag',
            'BreadcrumbComments' => 'Breadcrumb Comments',
            'BreadcrumbCreatedDate' => 'Breadcrumb Created Date',
            'BreadcrumbDeviceID' => 'Breadcrumb Device ID',
        ];
    }
}
