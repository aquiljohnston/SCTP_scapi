<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "EquipmentStatusTb".
 *
 * @property integer $EquipmentStatusID
 * @property string $EquipmentStatusStatus
 * @property string $EquipmentStatusDescription
 * @property string $EquipmentStatusArchiveFlag
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
            [['EquipmentStatusStatus', 'EquipmentStatusDescription', 'EquipmentStatusCreatedBy', 'EquipmentStatusModifiedBy', 'EquipmentStatusArchiveFlag'], 'string'],
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
            'EquipmentStatusStatus' => 'Equipment Status Status',
            'EquipmentStatusDescription' => 'Equipment Status Description',
			'EquipmentStatusArchiveFlag' => 'Equipment Status Archive Flag',
            'EquipmentStatusCreatedBy' => 'Equipment Status Created By',
            'EquipmentStatusCreatedDate' => 'Equipment Status Created Date',
            'EquipmentStatusModifiedBy' => 'Equipment Status Modified By',
            'EquipmentStatusModifiedDate' => 'Equipment Status Modified Date',
        ];
    }
}
