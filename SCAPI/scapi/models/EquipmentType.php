<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EquipmentTypeTb".
 *
 * @property integer $EquipmentTypeID
 * @property string $EquipmentType
 * @property string $EquipmentTypeDescription
 * @property string $EquipmentTypeArchiveFlag
 * @property string $EquipmentTypeCreatedDate
 * @property string $EquipmentTypeCreatedBy
 * @property string $EquipmentTypeModifiedDate
 * @property string $EquipmetTypeModifiedBy
 */
class EquipmentType extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'EquipmentTypeTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EquipmentTypeID'], 'integer'],
            [['EquipmentType', 'EquipmentTypeDescription', 'EquipmentTypeArchiveFlag', 'EquipmentTypeCreatedDate', 'EquipmentTypeCreatedBy', 'EquipmentTypeModifiedDate', 'EquipmetTypeModifiedBy'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EquipmentTypeID' => 'Equipment Type ID',
            'EquipmentType' => 'Equipment Type',
            'EquipmentTypeDescription' => 'Equipment Type Description',
			'EquipmentTypeArchiveFlag' => 'Equipment Type Archive Flag',
			'EquipmentTypeCreatedDate' => 'Equipment Type Created Date',
			'EquipmentTypeCreatedBy' => 'Equipment Type Created By',
			'EquipmentTypeModifiedDate' => 'Equipment Type Modified Date',
			'EquipmetTypeModifiedBy' => 'Equipmet Type Modified By',
        ];
    }
}
