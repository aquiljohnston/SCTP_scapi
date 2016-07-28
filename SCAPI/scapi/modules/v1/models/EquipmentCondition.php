<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "EquipmentConditionTb".
 *
 * @property integer $EquipmentConditionID
 * @property string $EquipmentCondition
 * @property string $EquipmentConditionDescription
 * @property string $EquipmentConditionArchiveFlag
 * @property string $EquipmentConditionCreateDate
 * @property string $EquipmentConditionCreatedBy
 * @property string $EquipmentConditionModifiedDate
 * @property string $EquipmentConditionModifiedBy
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
            [['EquipmentCondition', 'EquipmentConditionDescription', 'EquipmentConditionArchiveFlag', 'EquipmentConditionCreateDate', 'EquipmentConditionCreatedBy',
				'EquipmentConditionModifiedDate', 'EquipmentConditionModifiedBy'], 'string']
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
			'EquipmentConditionArchiveFlag' => 'Equipment Condition Archive Flag',
			'EquipmentConditionCreateDate' => 'Equipment Condition Create Date',
			'EquipmentConditionCreatedBy' => 'Equipment Condition Created By',
			'EquipmentConditionModifiedDate' => 'Equipment Condition Modified Date',
			'EquipmentConditionModifiedBy' => 'Equipment Condition Modified By',
        ];
    }
}
