<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "vWebManagementInspectionsInspections".
 *
 * @property string $MapGrid
 * @property string $SectionNumber
 * @property string $Inspector
 * @property string $InspectionDateTime
 * @property double $InspectionLatutude
 * @property double $InspectionLongitude
 * @property integer $Adhoc
 * @property integer $AOC
 * @property integer $CGE
 * @property integer $IsIndicationFlag
 * @property integer $HasEvents
 */
class WebManagementInspectionsInspections extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementInspectionsInspections';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MapGrid', 'SectionNumber', 'Inspector'], 'string'],
            [['SectionNumber', 'HasEvents'], 'required'],
            [['InspectionDateTime'], 'safe'],
            [['InspectionLatutude', 'InspectionLongitude'], 'number'],
            [['Adhoc', 'AOC', 'CGE', 'IsIndicationFlag', 'HasEvents'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MapGrid' => 'Map Grid',
            'SectionNumber' => 'Section Number',
            'Inspector' => 'Inspector',
            'InspectionDateTime' => 'Inspection Date Time',
            'InspectionLatutude' => 'Inspection Latutude',
            'InspectionLongitude' => 'Inspection Longitude',
            'Adhoc' => 'Adhoc',
            'AOC' => 'Aoc',
            'CGE' => 'Cge',
            'IsIndicationFlag' => 'Is Indication Flag',
            'HasEvents' => 'Has Events',
        ];
    }
}
