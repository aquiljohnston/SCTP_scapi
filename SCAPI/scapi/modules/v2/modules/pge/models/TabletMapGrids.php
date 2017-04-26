<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vTabletMapGrids".
 *
 * @property string $MapGridsUID
 * @property string $CreateDTLT
 * @property string $ModifiedDTLT
 * @property string $FLOC
 * @property string $WorkCenter
 * @property string $WorkCenterAbbreviation
 * @property string $WorkCenterAbbreviationFLOC
 * @property string $FuncLocMWC
 * @property string $FuncLocMapBoundary
 * @property string $FuncLocPlatSuffix
 * @property string $FuncLocMap
 * @property string $FuncLocPlat
 * @property string $FuncLocPlatChar2
 * @property integer $FuncLocPlatChar3
 * @property integer $FuncLocPlatChar4
 * @property double $CentroidLat
 * @property double $CentroidLong
 * @property integer $GeoBufferWithDrift
 */
class TabletMapGrids extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTabletMapGrids';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGridsUID', 'CreateDTLT', 'ModifiedDTLT'], 'required'],
            [['MapGridsUID', 'FLOC', 'WorkCenter', 'WorkCenterAbbreviation', 'WorkCenterAbbreviationFLOC', 'FuncLocMWC', 'FuncLocMapBoundary', 'FuncLocPlatSuffix', 'FuncLocMap', 'FuncLocPlat', 'FuncLocPlatChar2'], 'string'],
            [['CreateDTLT', 'ModifiedDTLT'], 'safe'],
            [['FuncLocPlatChar3', 'FuncLocPlatChar4', 'GeoBufferWithDrift'], 'integer'],
            [['CentroidLat', 'CentroidLong'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGridsUID' => 'Map Grids Uid',
            'CreateDTLT' => 'Create Dtlt',
            'ModifiedDTLT' => 'Modified Dtlt',
            'FLOC' => 'Floc',
            'WorkCenter' => 'Work Center',
            'WorkCenterAbbreviation' => 'Work Center Abbreviation',
            'WorkCenterAbbreviationFLOC' => 'Work Center Abbreviation Floc',
            'FuncLocMWC' => 'Func Loc Mwc',
            'FuncLocMapBoundary' => 'Func Loc Map Boundary',
            'FuncLocPlatSuffix' => 'Func Loc Plat Suffix',
            'FuncLocMap' => 'Func Loc Map',
            'FuncLocPlat' => 'Func Loc Plat',
            'FuncLocPlatChar2' => 'Func Loc Plat Char2',
            'FuncLocPlatChar3' => 'Func Loc Plat Char3',
            'FuncLocPlatChar4' => 'Func Loc Plat Char4',
            'CentroidLat' => 'Centroid Lat',
            'CentroidLong' => 'Centroid Long',
            'GeoBufferWithDrift' => 'Geo Buffer With Drift',
        ];
    }
}
