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
 * @property double $ReadPPM
 * @property integer $CalibrationVerificationFlag
 * @property double $AlarmPPM
 * @property integer $MPRFlag
 * @property string $PrNtfNo
 * @property string $SAPEqID
 * @property string $MWC
 * @property string $CalbDate
 * @property integer $IsUsedToday
 * @property string $MPRStatus
 * @property string $SafetyIssue
 * @property string $InstrumentAge
 * @property string $MasterLeakLogUID
 * @property string $StatusType
 * @property double $OMDExmQty
 * @property integer $LaserCalb
 * @property double $PLELRead
 * @property double $PGASRead
 * @property string $SCOPMethod
 * @property integer $StationPass
 * @property string $ActivityUID
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
            [['InspecitonEquipmentUID', 'InspectorOQLogUID', 'EquipmentLogUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'RevisionComments', 'EquipmentType', 'SerialNumber', 'PrNtfNo', 'SAPEqID', 'MWC', 'MPRStatus', 'SafetyIssue', 'InstrumentAge', 'MasterLeakLogUID', 'StatusType', 'SCOPMethod', 'ActivityUID'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'LastEquipmentDayFlag', 'CalibrationVerificationFlag', 'MPRFlag', 'IsUsedToday', 'LaserCalb', 'StationPass'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset', 'CalbDate'], 'safe'],
            [['ReadPPM', 'AlarmPPM', 'OMDExmQty', 'PLELRead', 'PGASRead'], 'number']
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
            'ReadPPM' => 'Read Ppm',
            'CalibrationVerificationFlag' => 'Calibration Verification Flag',
            'AlarmPPM' => 'Alarm Ppm',
            'MPRFlag' => 'Mprflag',
            'PrNtfNo' => 'Pr Ntf No',
            'SAPEqID' => 'Sapeq ID',
            'MWC' => 'Mwc',
            'CalbDate' => 'Calb Date',
            'IsUsedToday' => 'Is Used Today',
            'MPRStatus' => 'Mprstatus',
            'SafetyIssue' => 'Safety Issue',
            'InstrumentAge' => 'Instrument Age',
            'MasterLeakLogUID' => 'Master Leak Log Uid',
            'StatusType' => 'Status Type',
            'OMDExmQty' => 'Omdexm Qty',
            'LaserCalb' => 'Laser Calb',
            'PLELRead' => 'Plelread',
            'PGASRead' => 'Pgasread',
            'SCOPMethod' => 'Scopmethod',
            'StationPass' => 'Station Pass',
            'ActivityUID' => 'Activity Uid',
        ];
    }
}
