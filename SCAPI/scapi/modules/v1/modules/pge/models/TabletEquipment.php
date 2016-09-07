<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vTabletEquipment".
 *
 * @property string $PrNtfNo
 * @property string $SAPEqID
 * @property string $EqObjType
 * @property string $EqSerNo
 * @property string $MWC
 * @property string $CalbDate
 * @property string $CalbStat
 * @property string $LastCalbStat
 * @property string $MPRNo
 * @property string $UpdateFlag
 * @property string $MPR_Status
 * @property string $UsedYesterday
 * @property string $CalbTime
 * @property string $SrvyLanID
 * @property string $SpvrLanID
 * @property string $CalbHrs
 */
class TabletEquipment extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTabletEquipment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['PrNtfNo', 'SAPEqID', 'EqObjType', 'EqSerNo', 'MWC', 'CalbStat', 'LastCalbStat', 'MPRNo', 'UpdateFlag', 'MPR_Status', 'UsedYesterday', 'SrvyLanID', 'SpvrLanID'], 'string'],
            [['CalbDate', 'CalbTime'], 'safe'],
            [['MPR_Status', 'UsedYesterday'], 'required'],
            [['CalbHrs'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'PrNtfNo' => 'Pr Ntf No',
            'SAPEqID' => 'Sapeq ID',
            'EqObjType' => 'Eq Obj Type',
            'EqSerNo' => 'Eq Ser No',
            'MWC' => 'Mwc',
            'CalbDate' => 'Calb Date',
            'CalbStat' => 'Calb Stat',
            'LastCalbStat' => 'Last Calb Stat',
            'MPRNo' => 'Mprno',
            'UpdateFlag' => 'Update Flag',
            'MPR_Status' => 'Mpr  Status',
            'UsedYesterday' => 'Used Yesterday',
            'CalbTime' => 'Calb Time',
            'SrvyLanID' => 'Srvy Lan ID',
            'SpvrLanID' => 'Spvr Lan ID',
            'CalbHrs' => 'Calb Hrs',
        ];
    }
}
