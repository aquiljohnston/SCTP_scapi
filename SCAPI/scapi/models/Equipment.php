<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "EquipmentTb".
 *
 * @property string $EquipmentID
 * @property string $EquipmentName
 * @property string $EquipmentSerialNumber
 * @property string $EquipmentDetails
 * @property string $EquipmentType
 * @property string $EquipmentManufacturer
 * @property string $EquipmentManufactureYear
 * @property string $EquipmentCondition
 * @property string $EquipmentMACID
 * @property string $EquipmentModel
 * @property string $EquipmentColor
 * @property string $EquipmentWarrantyDetail
 * @property string $EquipmentComment
 * @property integer $EquipmentClientID
 * @property integer $EquipmentProjectID
 * @property string $EquipmentAnnualCalibrationDate
 * @property string $EquipmentAnnualCalibrationStatus
 * @property string $EquipmentAssignedUserID
 * @property integer $EquipmentAcceptedFlag
 * @property string $EquipmentAcceptedBy
 * @property string $EquipmentCreatedByUser
 * @property string $EquipmentCreateDate
 * @property string $EquipmentModifiedBy
 * @property string $EquipmentModifiedDate
 *
 * @property ClientTb $equipmentClient
 * @property UserTb $equipmentAssignedUser
 */
class Equipment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'EquipmentTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EquipmentName', 'EquipmentSerialNumber', 'EquipmentDetails', 'EquipmentType', 'EquipmentManufacturer', 'EquipmentManufactureYear', 'EquipmentCondition', 'EquipmentMACID', 'EquipmentModel', 'EquipmentColor', 'EquipmentWarrantyDetail', 'EquipmentComment', 'EquipmentAnnualCalibrationStatus', 'EquipmentAcceptedBy', 'EquipmentCreatedByUser', 'EquipmentModifiedBy'], 'string'],
            [['EquipmentClientID', 'EquipmentProjectID', 'EquipmentAssignedUserID', 'EquipmentAcceptedFlag'], 'integer'],
            [['EquipmentAnnualCalibrationDate', 'EquipmentCreateDate', 'EquipmentModifiedDate'], 'safe']
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
            'EquipmentDetails' => 'Equipment Details',
            'EquipmentType' => 'Equipment Type',
            'EquipmentManufacturer' => 'Equipment Manufacturer',
            'EquipmentManufactureYear' => 'Equipment Manufacture Year',
            'EquipmentCondition' => 'Equipment Condition',
            'EquipmentMACID' => 'Equipment Macid',
            'EquipmentModel' => 'Equipment Model',
            'EquipmentColor' => 'Equipment Color',
            'EquipmentWarrantyDetail' => 'Equipment Warranty Detail',
            'EquipmentComment' => 'Equipment Comment',
            'EquipmentClientID' => 'Equipment Client ID',
            'EquipmentProjectID' => 'Equipment Project ID',
            'EquipmentAnnualCalibrationDate' => 'Equipment Annual Calibration Date',
            'EquipmentAnnualCalibrationStatus' => 'Equipment Annual Calibration Status',
            'EquipmentAssignedUserID' => 'Equipment Assigned User ID',
			'EquipmentAcceptedFlag' => 'Equipment Accepted Flag',
			'EquipmentAcceptedBy' => 'Equipment Accepted By',
            'EquipmentCreatedByUser' => 'Equipment Created By User',
            'EquipmentCreateDate' => 'Equipment Create Date',
            'EquipmentModifiedBy' => 'Equipment Modified By',
            'EquipmentModifiedDate' => 'Equipment Modified Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentClient()
    {
        return $this->hasOne(ClientTb::className(), ['ClientID' => 'EquipmentClientID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentAssignedUser()
    {
        return $this->hasOne(SCUser::className(), ['UserID' => 'EquipmentAssignedUserID']);
    }
}
