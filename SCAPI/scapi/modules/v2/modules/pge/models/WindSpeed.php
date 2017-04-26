<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgWindSpeed".
 *
 * @property integer $WindSpeedID
 * @property string $WindSpeedUID
 * @property string $InspectionRequestUID
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $srcDTLT
 * @property string $srvDTLT
 * @property string $srvDTLTOffset
 * @property string $Comments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property double $WindSpeed
 * @property double $Latitude
 * @property double $Longitude
 * @property string $EntryTime
 * @property string $MapPlat
 * @property string $MapGridUID
 * @property integer $AlertFlag
 * @property string $SurveyType
 */
class WindSpeed extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgWindSpeed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['WindSpeedUID', 'InspectionRequestUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'MapPlat', 'MapGridUID', 'SurveyType'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'AlertFlag'], 'integer'],
            [['srcDTLT', 'srvDTLT', 'srvDTLTOffset', 'EntryTime'], 'safe'],
            [['WindSpeed', 'Latitude', 'Longitude'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'WindSpeedID' => 'Wind Speed ID',
            'WindSpeedUID' => 'Wind Speed Uid',
            'InspectionRequestUID' => 'Inspection Request Uid',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'srcDTLT' => 'Src Dtlt',
            'srvDTLT' => 'Srv Dtlt',
            'srvDTLTOffset' => 'Srv Dtltoffset',
            'Comments' => 'Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'WindSpeed' => 'Wind Speed',
            'Latitude' => 'Latitude',
            'Longitude' => 'Longitude',
            'EntryTime' => 'Entry Time',
            'MapPlat' => 'Map Plat',
            'MapGridUID' => 'Map Grid Uid',
            'AlertFlag' => 'Alert Flag',
            'SurveyType' => 'Survey Type',
        ];
    }
}
