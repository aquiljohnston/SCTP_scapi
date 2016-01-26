<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EquipmentTypeTb".
 *
 * @property integer $EquipmentTypeID
 * @property string $EquipmentType
 * @property string $EquipmentTypeDescription
 */
class EquipmentType extends \yii\db\ActiveRecord
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
            [['EquipmentType', 'EquipmentTypeDescription'], 'string']
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
        ];
    }
}
