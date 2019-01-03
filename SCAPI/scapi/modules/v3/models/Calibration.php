<?php

namespace app\modules\v3\models;

use Yii;

/**
 * This is the model class for table "tCalibration".
 *
 * @property integer $ID
 * @property integer $ActivityID
 * @property string $EquipmentSourceID
 * @property integer $CreatedUserID
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property string $EquipmentType
 * @property string $SerialNumber
 * @property integer $CalibrationVerification
 * @property double $CalibrationLevel
 * @property string $Comments
 * @property integer $DeletedFlag
 *
 * @property UserTb $createdUser
 */
class Calibration extends \app\modules\v3\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tCalibration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ActivityID', 'CreatedUserID', 'CalibrationVerification', 'DeletedFlag'], 'integer'],
            [['EquipmentSourceID', 'EquipmentType', 'SerialNumber', 'Comments'], 'string'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset'], 'safe'],
            [['CalibrationLevel'], 'number'],
            [['CreatedUserID'], 'exist', 'skipOnError' => true, 'targetClass' => BaseUser::className(), 'targetAttribute' => ['CreatedUserID' => 'UserID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ActivityID' => 'Activity ID',
            'EquipmentSourceID' => 'Equipment Source ID',
            'CreatedUserID' => 'Created User ID',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'EquipmentType' => 'Equipment Type',
            'SerialNumber' => 'Serial Number',
            'CalibrationVerification' => 'Calibration Verification',
            'CalibrationLevel' => 'Calibration Level',
            'Comments' => 'Comments',
            'DeletedFlag' => 'Deleted Flag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(BaseUser::className(), ['UserID' => 'CreatedUserID']);
    }
}
