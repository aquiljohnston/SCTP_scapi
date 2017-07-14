<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vWebManagementMapView".
 *
 * @property integer $ID
 * @property string $ClientWorkOrderID
 * @property string $AssetType
 * @property string $Address
 * @property string $MapGrid
 * @property integer $CompletedFlag
 * @property integer $LineID
 * @property integer $SegmentID
 * @property integer $VerticeID
 * @property double $Latutide
 * @property double $Longitude
 * @property integer $InspectionID
 * @property double $Distance
 * @property string $Verified
 */
class WebManagementMapView extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementMapView';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'Verified'], 'required'],
            [['ID', 'CompletedFlag', 'LineID', 'SegmentID', 'VerticeID', 'InspectionID'], 'integer'],
            [['ClientWorkOrderID', 'AssetType', 'Address', 'MapGrid', 'Verified'], 'string'],
            [['Latutide', 'Longitude', 'Distance'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ClientWorkOrderID' => 'Client Work Order ID',
            'AssetType' => 'Asset Type',
            'Address' => 'Address',
            'MapGrid' => 'Map Grid',
            'CompletedFlag' => 'Completed Flag',
            'LineID' => 'Line ID',
            'SegmentID' => 'Segment ID',
            'VerticeID' => 'Vertice ID',
            'Latutide' => 'Latutide',
            'Longitude' => 'Longitude',
            'InspectionID' => 'Inspection ID',
            'Distance' => 'Distance',
            'Verified' => 'Verified',
        ];
    }
}
