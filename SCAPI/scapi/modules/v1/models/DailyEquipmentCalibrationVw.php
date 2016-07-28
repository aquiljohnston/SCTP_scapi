<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "DailyEquipmentCalibration_vw".
 *
 * @property integer $EquipmentID
 * @property string $EquipmentName
 * @property string $EquipmentSerialNumber
 * @property string $EquipmentSCNumber
 * @property string $EquipmentDetails
 * @property string $EquipmentType
 * @property string $EquipmentManufacturer
 * @property string $EquipmentManufactureYear
 * @property string $EquipmentCondition
 * @property string $EquipmentStatus
 * @property string $EquipmentMACID
 * @property string $EquipmentModel
 * @property string $EquipmentColor
 * @property string $EquipmentWarrantyDetail
 * @property string $EquipmentComment
 * @property integer $EquipmentClientID
 * @property integer $EquipmentProjectID
 * @property string $ClientName
 * @property string $ProjectName
 * @property string $MaxEquipmentAnnualCalibrationDate
 * @property string $EquipmentAnnualCalibrationStatus
 * @property integer $EquipmentAssignedUserID
 * @property string $EquipmentAcceptedFlag
 * @property string $EquipmentAcceptedBy
 * @property string $EquipmentModificationReason
 * @property string $MaxDailyCalibrationDate
 * @property string $EquipmentCalibrationComment
 */
class DailyEquipmentCalibrationVw extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DailyEquipmentCalibration_vw_md';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EquipmentID'], 'required'],
            [['EquipmentID', 'EquipmentClientID', 'EquipmentProjectID', 'EquipmentAssignedUserID'], 'integer'],
            [['EquipmentName', 'EquipmentSerialNumber', 'EquipmentSCNumber', 'EquipmentDetails', 'EquipmentType',
			'EquipmentManufacturer', 'EquipmentManufactureYear', 'EquipmentCondition', 'EquipmentStatus', 'EquipmentMACID',
			'EquipmentModel', 'EquipmentColor', 'EquipmentWarrantyDetail', 'EquipmentComment', 'ClientName', 'ProjectName', 'EquipmentAnnualCalibrationStatus',
			'EquipmentAcceptedFlag', 'EquipmentAcceptedBy', 'EquipmentModificationReason', 'EquipmentCalibrationComment'], 'string'],
            [['MaxEquipmentAnnualCalibrationDate', 'MaxDailyCalibrationDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'EquipmentID' => 'Equipment ID',
            'EquipmentName' => 'Equipment Name',
            'EquipmentSerialNumber' => 'Equipment Serial Number',
            'EquipmentSCNumber' => 'Equipment Scnumber',
            'EquipmentDetails' => 'Equipment Details',
            'EquipmentType' => 'Equipment Type',
            'EquipmentManufacturer' => 'Equipment Manufacturer',
            'EquipmentManufactureYear' => 'Equipment Manufacture Year',
            'EquipmentCondition' => 'Equipment Condition',
            'EquipmentStatus' => 'Equipment Status',
            'EquipmentMACID' => 'Equipment Macid',
            'EquipmentModel' => 'Equipment Model',
            'EquipmentColor' => 'Equipment Color',
            'EquipmentWarrantyDetail' => 'Equipment Warranty Detail',
            'EquipmentComment' => 'Equipment Comment',
            'EquipmentClientID' => 'Equipment Client ID',
            'EquipmentProjectID' => 'Equipment Project ID',
			'ClientName' => 'Client Name',
			'ProjectName' => 'Project Name',
            'MaxEquipmentAnnualCalibrationDate' => 'Max Equipment Annual Calibration Date',
            'EquipmentAnnualCalibrationStatus' => 'Equipment Annual Calibration Status',
            'EquipmentAssignedUserID' => 'Equipment Assigned User ID',
            'EquipmentAcceptedFlag' => 'Equipment Accepted Flag',
            'EquipmentAcceptedBy' => 'Equipment Accepted By',
            'EquipmentModificationReason' => 'Equipment Modification Reason',
            'MaxDailyCalibrationDate' => 'Max Daily Calibration Date',
            'EquipmentCalibrationComment' => 'Equipment Calibration Comment',
        ];
    }
}
