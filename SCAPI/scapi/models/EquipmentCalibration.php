<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EquipmentCalibrationTb".
 *
 * @property integer $EquipmentCalibrationID
 * @property integer $EquipmentCalibrationEquipmentID
 * @property string $EquipmentCalibrationDate
 * @property integer $EquipmentCalibrationTechID
 * @property string $EquipmentCalibrationComment
 * @property string $EquipmentCalibrationArchiveFlag
 * @property string $EquipmentCalibrationCreateDate
 * @property string $EquipmentCalibrationCreatedBy
 * @property string $EquipmentCalibrationModifiedDate
 * @property string $EquipmentCalibrationModifiedBy
 */
class EquipmentCalibration extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'EquipmentCalibrationTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EquipmentCalibrationID', 'EquipmentCalibrationEquipmentID', 'EquipmentCalibrationTechID', 'EquipmentCalibrationCreatedBy', 'EquipmentCalibrationModifiedBy', 'EquipmentCalibrationArchiveFlag'], 'integer'],
            [['EquipmentCalibrationDate', 'EquipmentCalibrationCreateDate', 'EquipmentCalibrationModifiedDate'], 'safe'],
            [['EquipmentCalibrationComment'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EquipmentCalibrationID' => 'Equipment Calibration ID',
            'EquipmentCalibrationEquipmentID' => 'Equipment Calibration Equipment ID',
            'EquipmentCalibrationDate' => 'Equipment Calibration Date',
            'EquipmentCalibrationTechID' => 'Equipment Calibration TechID',
            'EquipmentCalibrationComment' => 'Equipment Calibration Comment',
			'EquipmentCalibrationArchiveFlag' => 'Equipment Calibration Archive Flag',
            'EquipmentCalibrationCreateDate' => 'Equipment Calibration Create Date',
            'EquipmentCalibrationCreatedBy' => 'Equipment Calibration Created By',
            'EquipmentCalibrationModifiedDate' => 'Equipment Calibration Modified Date',
            'EquipmentCalibrationModifiedBy' => 'Equipment Calibration Modified By',
        ];
    }
}
