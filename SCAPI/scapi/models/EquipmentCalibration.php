<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EquipmentCalibrationTb".
 *
 * @property integer $EquipmentCalibrationID
 * @property integer $EquipmentCalibrationEquipmentID
 * @property string $EquipmentCalibrationDate
 * @property integer $EquipmentCalibrationTechnician
 * @property string $EquipmentCalibrationComment
 * @property string $EquipmentCalibrationCreateDate
 * @property string $EquipmentCalibrationCreatedBy
 * @property string $EquipmentCalibrationMofifiedDate
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
            [['EquipmentCalibrationID', 'EquipmentCalibrationEquipmentID', 'EquipmentCalibrationTechnician', 'EquipmentCalibrationCreatedBy', 'EquipmentCalibrationModifiedBy'], 'integer'],
            [['EquipmentCalibrationDate', 'EquipmentCalibrationCreateDate', 'EquipmentCalibrationMofifiedDate'], 'safe'],
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
            'EquipmentCalibrationTechnician' => 'Equipment Calibration Technician',
            'EquipmentCalibrationComment' => 'Equipment Calibration Comment',
            'EquipmentCalibrationCreateDate' => 'Equipment Calibration Create Date',
            'EquipmentCalibrationCreatedBy' => 'Equipment Calibration Created By',
            'EquipmentCalibrationMofifiedDate' => 'Equipment Calibration Mofified Date',
            'EquipmentCalibrationModifiedBy' => 'Equipment Calibration Modified By',
        ];
    }
}
