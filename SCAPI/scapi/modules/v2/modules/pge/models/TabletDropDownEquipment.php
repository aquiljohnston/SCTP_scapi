<?php

namespace app\modules\v2\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vTabletDropDownEquipment".
 *
 * @property string $UserUID
 * @property string $UserLoginID
 * @property string $UserLANID
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $EquipmentLogUID
 * @property string $EquipmentDisplayType
 * @property string $SAPEquipmentType
 * @property string $EqSerNo
 * @property string $WCAbbrev
 * @property integer $SortOrder
 */
class TabletDropDownEquipment extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vTabletDropDownEquipment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserUID', 'UserLoginID', 'UserLANID', 'UserFirstName', 'UserLastName', 'EquipmentLogUID', 'EquipmentDisplayType', 'SAPEquipmentType', 'EqSerNo', 'WCAbbrev'], 'string'],
            [['EquipmentLogUID', 'SortOrder'], 'required'],
            [['SortOrder'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserUID' => 'User Uid',
            'UserLoginID' => 'User Login ID',
            'UserLANID' => 'User Lanid',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'EquipmentLogUID' => 'Equipment Log Uid',
            'EquipmentDisplayType' => 'Equipment Display Type',
            'SAPEquipmentType' => 'Sapequipment Type',
            'EqSerNo' => 'Eq Ser No',
            'WCAbbrev' => 'Wcabbrev',
            'SortOrder' => 'Sort Order',
        ];
    }
}
