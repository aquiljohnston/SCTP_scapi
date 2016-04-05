<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EquipmentStatusTb".
 *
 * @property integer $EquipmentStatusID
 * @property string $EquipmentStatus
 * @property string $EquipmentStatusDescription
 * @property string $EquipmentStatusCreatedBy
 * @property string $EquipmentStatusCreatedDate
 * @property string $EquipmentStatusModifiedBy
 * @property string $EquipmentStatusModifiedDate
 */
class EquipmentStatus extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'EquipmentStatusTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EquipmentStatus', 'EquipmentStatusDescription', 'EquipmentStatusCreatedBy', 'EquipmentStatusModifiedBy'], 'string'],
            [['EquipmentStatusCreatedDate', 'EquipmentStatusModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EquipmentStatusID' => 'Equipment Status ID',
            'EquipmentStatus' => 'Equipment Status',
            'EquipmentStatusDescription' => 'Equipment Status Description',
            'EquipmentStatusCreatedBy' => 'Equipment Status Created By',
            'EquipmentStatusCreatedDate' => 'Equipment Status Created Date',
            'EquipmentStatusModifiedBy' => 'Equipment Status Modified By',
            'EquipmentStatusModifiedDate' => 'Equipment Status Modified Date',
        ];
    }
}
