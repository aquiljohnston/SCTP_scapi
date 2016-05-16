<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "BreadcrumbTb".
 *
 * @property integer $BreadcrumbOBJECTID
 * @property integer $BreadcrumbRecID
 * @property integer $BreadcrumbPersonID
 * @property integer $BreadcrumbLoginID
 * @property integer $BreadcrumbAddressID
 * @property string $BreadcrumbTimetag
 * @property integer $BreadcrumbNumSats
 * @property string $BreadcrumbLatitude
 * @property string $BreadcrumbLongitude
 * @property string $BreadcrumbPosSrc
 * @property integer $BreadcrumbSpeed
 * @property integer $BreadcrumbHeading
 * @property string $BreadcrumbDirection
 * @property integer $BreadcrumbSeqValue
 * @property string $BreadcrumbDeviceID
 * @property string $BreadcrumbLocalTimeTag
 * @property string $BreadcrumbGpsAccuracy
 * @property integer $BreadcrumbAssociatedPhoneData
 * @property string $BreadcrumbSpatialPosition
 * @property integer $BreadcrumbViolationZoneID
 * @property integer $BreadcrumbTrackingGroupID
 * @property string $BreadcrumbLast_
 * @property string $BreadcrumbFirst_
 * @property string $BreadcrumbGlobalID
 * @property integer $BreadcrumbMap_ID_Num
 * @property integer $BreadcrumbShape
 * @property string $BreadcrumbArchiveFlag
 * @property string $BreadcrumbComments
 * @property string $BreadcrumbCreatedBy
 * @property string $BreadcrumbCreatedDate
 * @property string $BreadcrumbModifiedBy
 * @property string $BreadcrumbModifiedDate
 */
class Breadcrumb extends BaseActiveRecord
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
            [['BreadcrumbOBJECTID', 'BreadcrumbTimetag', 'BreadcrumbNumSats', 'BreadcrumbLatitude', 'BreadcrumbLongitude', 'BreadcrumbPosSrc', 'BreadcrumbAssociatedPhoneData', 'BreadcrumbLast_', 'BreadcrumbFirst_', 'BreadcrumbGlobalID'], 'required'],
            [['BreadcrumbOBJECTID', 'BreadcrumbRecID', 'BreadcrumbPersonID', 'BreadcrumbLoginID', 'BreadcrumbAddressID', 'BreadcrumbNumSats', 'BreadcrumbSpeed', 'BreadcrumbHeading', 'BreadcrumbSeqValue', 'BreadcrumbAssociatedPhoneData', 'BreadcrumbViolationZoneID', 'BreadcrumbTrackingGroupID', 'BreadcrumbMap_ID_Num', 'BreadcrumbShape'], 'integer'],
            [['BreadcrumbTimetag', 'BreadcrumbLocalTimeTag'], 'safe'],
            [['BreadcrumbLatitude', 'BreadcrumbLongitude', 'BreadcrumbGpsAccuracy'], 'number'],
            [['BreadcrumbPosSrc', 'BreadcrumbDirection', 'BreadcrumbDeviceID', 'BreadcrumbSpatialPosition', 'BreadcrumbLast_', 'BreadcrumbFirst_', 'BreadcrumbGlobalID', 'BreadcrumbArchiveFlag', 'BreadcrumbComments',
				'BreadcrumbCreatedBy', 'BreadcrumbCreatedDate', 'BreadcrumbModifiedBy', 'BreadcrumbModifiedDate'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'BreadcrumbOBJECTID' => 'Breadcrumb Objectid',
            'BreadcrumbRecID' => 'Breadcrumb Rec ID',
            'BreadcrumbPersonID' => 'Breadcrumb Person ID',
            'BreadcrumbLoginID' => 'Breadcrumb Login ID',
            'BreadcrumbAddressID' => 'Breadcrumb Address ID',
            'BreadcrumbTimetag' => 'Breadcrumb Timetag',
            'BreadcrumbNumSats' => 'Breadcrumb Num Sats',
            'BreadcrumbLatitude' => 'Breadcrumb Latitude',
            'BreadcrumbLongitude' => 'Breadcrumb Longitude',
            'BreadcrumbPosSrc' => 'Breadcrumb Pos Src',
            'BreadcrumbSpeed' => 'Breadcrumb Speed',
            'BreadcrumbHeading' => 'Breadcrumb Heading',
            'BreadcrumbDirection' => 'Breadcrumb Direction',
            'BreadcrumbSeqValue' => 'Breadcrumb Seq Value',
            'BreadcrumbDeviceID' => 'Breadcrumb Device ID',
            'BreadcrumbLocalTimeTag' => 'Breadcrumb Local Time Tag',
            'BreadcrumbGpsAccuracy' => 'Breadcrumb Gps Accuracy',
            'BreadcrumbAssociatedPhoneData' => 'Breadcrumb Associated Phone Data',
            'BreadcrumbSpatialPosition' => 'Breadcrumb Spatial Position',
            'BreadcrumbViolationZoneID' => 'Breadcrumb Violation Zone ID',
            'BreadcrumbTrackingGroupID' => 'Breadcrumb Tracking Group ID',
            'BreadcrumbLast_' => 'Breadcrumb Last',
            'BreadcrumbFirst_' => 'Breadcrumb First',
            'BreadcrumbGlobalID' => 'Breadcrumb Global ID',
            'BreadcrumbMap_ID_Num' => 'Breadcrumb Map  Id  Num',
            'BreadcrumbShape' => 'Breadcrumb Shape',
			'BreadcrumbArchiveFlag' => 'Breadcrumb Archive Flag',
			'BreadcrumbComments' => 'Breadcrumb Comments',
			'BreadcrumbCreatedBy' => 'Breadcrumb Created By',
			'BreadcrumbCreatedDate' => 'Breadcrumb Created Date',
			'BreadcrumbModifiedBy' => 'Breadcrumb Modified By',
			'BreadcrumbModifiedDate' => 'Breadcrumb Modified Date',
			
        ];
    }
}
