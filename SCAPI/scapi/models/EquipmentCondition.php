<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EquipmentConditionTb".
 *
 * @property integer $EquipmentConditionID
 * @property string $EquipmentCondition
 * @property string $EquipmentConditionDescription
 */
class EquipmentCondition extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'EquipmentConditionTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EquipmentConditionID'], 'required'],
            [['EquipmentConditionID'], 'integer'],
            [['EquipmentCondition', 'EquipmentConditionDescription'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EquipmentConditionID' => 'Equipment Condition ID',
            'EquipmentCondition' => 'Equipment Condition',
            'EquipmentConditionDescription' => 'Equipment Condition Description',
        ];
    }
}
