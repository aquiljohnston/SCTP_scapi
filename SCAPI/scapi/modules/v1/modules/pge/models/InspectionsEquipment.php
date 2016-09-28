<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tInspectionsEquipment".
 *
 * @property integer $InspecitonEquipmentID
 * @property string $InspecitonEquipmentUID
 * @property string $InspectorOQLogUID
 * @property string $EquipmentLogUID
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property string $Comments
 * @property string $RevisionComments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property integer $LastEquipmentDayFlag
 * @property string $EquipmentType
 * @property string $SerialNumber
 * @property double $CalibrationLevel
 * @property integer $CalibrationVerificationFlag
 * @property double $AlarmLevel
 * @property integer $MPRFlag
 * @property string $PrNtfNo
 * @property string $SAPEqID
 * @property string $MWC
 * @property string $CalbDate
 * @property integer $IsUsedToday
 * @property string $MPRStatus
 * @property string $SafteyIssue
 * @property string $InstrumentAge
 */
class InspectionsEquipment extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tInspectionsEquipment';
    }
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['InspecitonEquipmentUID', 'InspectorOQLogUID', 'EquipmentLogUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'RevisionComments', 'EquipmentType', 'SerialNumber', 'PrNtfNo', 'SAPEqID', 'MWC', 'MPRStatus', 'SafteyIssue', 'InstrumentAge'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'LastEquipmentDayFlag', 'CalibrationVerificationFlag', 'MPRFlag', 'IsUsedToday'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset', 'CalbDate'], 'safe'],
            [['CalibrationLevel', 'AlarmLevel'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'InspecitonEquipmentID' => 'Inspeciton Equipment ID',
            'InspecitonEquipmentUID' => 'Inspeciton Equipment Uid',
            'InspectorOQLogUID' => 'Inspector Oqlog Uid',
            'EquipmentLogUID' => 'Equipment Log Uid',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'Comments' => 'Comments',
            'RevisionComments' => 'Revision Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'LastEquipmentDayFlag' => 'Last Equipment Day Flag',
            'EquipmentType' => 'Equipment Type',
            'SerialNumber' => 'Serial Number',
            'CalibrationLevel' => 'Calibration Level',
            'CalibrationVerificationFlag' => 'Calibration Verification Flag',
            'AlarmLevel' => 'Alarm Level',
            'MPRFlag' => 'Mprflag',
            'PrNtfNo' => 'Pr Ntf No',
            'SAPEqID' => 'Sapeq ID',
            'MWC' => 'Mwc',
            'CalbDate' => 'Calb Date',
            'IsUsedToday' => 'Is Used Today',
            'MPRStatus' => 'Mprstatus',
            'SafteyIssue' => 'Saftey Issue',
            'InstrumentAge' => 'Instrument Age',
        ];
    }
}
