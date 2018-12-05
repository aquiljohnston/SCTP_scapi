<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "EquipmentTb".
 *
 * @property int $EquipmentID
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
 * @property int $EquipmentClientID
 * @property int $EquipmentProjectID
 * @property string $EquipmentAnnualCalibrationDate
 * @property string $EquipmentAnnualCalibrationStatus
 * @property int $EquipmentCalibrationID
 * @property string $EquipmentAssignedUserName
 * @property string $EquipmentAcceptedFlag
 * @property string $EquipmentAcceptedBy
 * @property string $EquipmentModificationReason
 * @property string $EquipmentArchiveFlag
 * @property string $EquipmentCreateDate
 * @property string $EquipmentCreatedBy
 * @property string $EquipmentModifiedDate
 * @property string $EquipmentModifiedBy
 *
 * @property UserTb $equipmentAssignedUserName
 * @property ClientTb $equipmentClient
 */
class Equipment extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'EquipmentTb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['EquipmentName', 'EquipmentSerialNumber', 'EquipmentSCNumber', 'EquipmentDetails', 'EquipmentType', 'EquipmentManufacturer', 'EquipmentManufactureYear', 'EquipmentCondition', 'EquipmentStatus', 'EquipmentMACID', 'EquipmentModel', 'EquipmentColor', 'EquipmentWarrantyDetail', 'EquipmentComment', 'EquipmentAnnualCalibrationStatus', 'EquipmentAssignedUserName', 'EquipmentAcceptedFlag', 'EquipmentAcceptedBy', 'EquipmentModificationReason', 'EquipmentArchiveFlag', 'EquipmentCreatedBy', 'EquipmentModifiedBy'], 'string'],
            [['EquipmentClientID', 'EquipmentProjectID', 'EquipmentCalibrationID'], 'integer'],
            [['EquipmentAnnualCalibrationDate', 'EquipmentCreateDate', 'EquipmentModifiedDate'], 'safe'],
            [['EquipmentAssignedUserName'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['EquipmentAssignedUserName' => 'UserName']],
            [['EquipmentClientID'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['EquipmentClientID' => 'ClientID']],
        ];
    }

    /**
     * {@inheritdoc}
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
            'EquipmentAnnualCalibrationDate' => 'Equipment Annual Calibration Date',
            'EquipmentAnnualCalibrationStatus' => 'Equipment Annual Calibration Status',
            'EquipmentCalibrationID' => 'Equipment Calibration ID',
            'EquipmentAssignedUserName' => 'Equipment Assigned User Name',
            'EquipmentAcceptedFlag' => 'Equipment Accepted Flag',
            'EquipmentAcceptedBy' => 'Equipment Accepted By',
            'EquipmentModificationReason' => 'Equipment Modification Reason',
            'EquipmentArchiveFlag' => 'Equipment Archive Flag',
            'EquipmentCreateDate' => 'Equipment Create Date',
            'EquipmentCreatedBy' => 'Equipment Created By',
            'EquipmentModifiedDate' => 'Equipment Modified Date',
            'EquipmentModifiedBy' => 'Equipment Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentAssignedUserName()
    {
        return $this->hasOne(BaseUser::className(), ['UserName' => 'EquipmentAssignedUserName']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentClient()
    {
        return $this->hasOne(Client::className(), ['ClientID' => 'EquipmentClientID']);
    }
}
