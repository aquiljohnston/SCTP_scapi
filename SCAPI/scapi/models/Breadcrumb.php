<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "BreadcrumbTb".
 *
 * @property integer $OBJECTID
 * @property integer $RecID
 * @property integer $PersonID
 * @property integer $LoginID
 * @property integer $AddressID
 * @property string $Timetag
 * @property integer $NumSats
 * @property string $Latitude
 * @property string $Longitude
 * @property string $PosSrc
 * @property integer $Speed
 * @property integer $Heading
 * @property string $Direction
 * @property integer $SeqValue
 * @property string $DeviceID
 * @property string $LocalTimeTag
 * @property string $GpsAccuracy
 * @property integer $AssociatedPhoneData
 * @property string $SpatialPosition
 * @property integer $ViolationZoneID
 * @property integer $TrackingGroupID
 * @property string $Last_
 * @property string $First_
 * @property string $GlobalID
 * @property integer $Map_ID_Num
 * @property integer $Shape
 */
class Breadcrumb extends \yii\db\ActiveRecord
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
            [['OBJECTID', 'Timetag', 'NumSats', 'Latitude', 'Longitude', 'PosSrc', 'AssociatedPhoneData', 'Last_', 'First_', 'GlobalID'], 'required'],
            [['OBJECTID', 'RecID', 'PersonID', 'LoginID', 'AddressID', 'NumSats', 'Speed', 'Heading', 'SeqValue', 'AssociatedPhoneData', 'ViolationZoneID', 'TrackingGroupID', 'Map_ID_Num', 'Shape'], 'integer'],
            [['Timetag', 'LocalTimeTag'], 'safe'],
            [['Latitude', 'Longitude', 'GpsAccuracy'], 'number'],
            [['PosSrc', 'Direction', 'DeviceID', 'SpatialPosition', 'Last_', 'First_', 'GlobalID'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'OBJECTID' => 'Objectid',
            'RecID' => 'Rec ID',
            'PersonID' => 'Person ID',
            'LoginID' => 'Login ID',
            'AddressID' => 'Address ID',
            'Timetag' => 'Timetag',
            'NumSats' => 'Num Sats',
            'Latitude' => 'Latitude',
            'Longitude' => 'Longitude',
            'PosSrc' => 'Pos Src',
            'Speed' => 'Speed',
            'Heading' => 'Heading',
            'Direction' => 'Direction',
            'SeqValue' => 'Seq Value',
            'DeviceID' => 'Device ID',
            'LocalTimeTag' => 'Local Time Tag',
            'GpsAccuracy' => 'Gps Accuracy',
            'AssociatedPhoneData' => 'Associated Phone Data',
            'SpatialPosition' => 'Spatial Position',
            'ViolationZoneID' => 'Violation Zone ID',
            'TrackingGroupID' => 'Tracking Group ID',
            'Last_' => 'Last',
            'First_' => 'First',
            'GlobalID' => 'Global ID',
            'Map_ID_Num' => 'Map  Id  Num',
            'Shape' => 'Shape',
        ];
    }
}
