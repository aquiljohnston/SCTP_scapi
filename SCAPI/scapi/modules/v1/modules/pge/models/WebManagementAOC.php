<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementAOC".
 *
 * @property string $Date
 * @property string $Time
 * @property string $Surveyor
 * @property string $WorkCenter
 * @property string $Map/Plat
 * @property string $SurveyType
 * @property string $AOCType
 * @property string $MeterNumber
 * @property string $HouseNo
 * @property string $Street
 * @property string $Apt
 * @property string $City
 * @property string $Comments
 * @property string $AOCUID
 * @property integer $ApprovedFlag
 * @property string $Photo1
 * @property string $Photo2
 * @property string $Photo3
 * @property string $Division
 * @property string $FLOC
 * @property string $LANID
 */
class WebManagementAOC extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementAOC';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Date', 'Time'], 'safe'],
            [['Surveyor', 'WorkCenter', 'Map/Plat', 'SurveyType', 'AOCType', 'MeterNumber', 'HouseNo', 'Street', 'Apt', 'City', 'Comments', 'AOCUID', 'Photo1', 'Photo2', 'Photo3', 'Division', 'FLOC', 'LANID'], 'string'],
            [['ApprovedFlag'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Date' => 'Date',
            'Time' => 'Time',
            'Surveyor' => 'Surveyor',
            'WorkCenter' => 'Work Center',
            'Map/Plat' => 'Map/ Plat',
            'SurveyType' => 'Survey Type',
            'AOCType' => 'Aoctype',
            'MeterNumber' => 'Meter Number',
            'HouseNo' => 'House No',
            'Street' => 'Street',
            'Apt' => 'Apt',
            'City' => 'City',
            'Comments' => 'Comments',
            'AOCUID' => 'Aocuid',
            'ApprovedFlag' => 'Approved Flag',
            'Photo1' => 'Photo1',
            'Photo2' => 'Photo2',
            'Photo3' => 'Photo3',
            'Division' => 'Division',
            'FLOC' => 'Floc',
            'LANID' => 'Lanid',
        ];
    }
}
