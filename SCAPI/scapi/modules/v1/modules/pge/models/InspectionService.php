<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tInspectionService".
 *
 * @property integer $tInspecitonServicesID
 * @property string $InspecitonServicesUID
 * @property string $MasterLeakLogUID
 * @property string $MapGridUID
 * @property string $InspectionRequestLogUID
 * @property string $InspecitonEquipmentUID
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
 * @property string $StatusType
 * @property string $EquipmentType
 * @property string $InstrumentType
 * @property string $SerialNumber
 * @property double $CalibrationLevel
 * @property integer $CalibrationVerificationFlag
 * @property integer $WindSpeedStart
 * @property integer $WindSpeedEnd
 * @property string $EquipmentModeType
 * @property integer $EstimatedFeet
 * @property integer $EstimatedServices
 * @property double $EstimatedHours
 * @property integer $ApprovedFlag
 * @property string $AprovaedByUserUID
 * @property string $ApprovedDTLT
 * @property integer $SubmittedFlag
 * @property string $SubmittedStatusType
 * @property string $SubmittedUserUID
 * @property string $SubmittedDTLT
 * @property string $ResponseStatusType
 * @property string $Response
 * @property string $ResponceErrorDescription
 * @property string $ResponseDTLT
 * @property integer $CompletedFlag
 * @property string $CompletedDTLT
 * @property string $SurveyMode
 * @property integer $PlaceHolderFlag
 */
class InspectionService extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tInspectionService';
    }
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['InspecitonServicesUID', 'MasterLeakLogUID', 'MapGridUID', 'InspectionRequestLogUID', 'InspecitonEquipmentUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'RevisionComments', 'StatusType', 'EquipmentType', 'InstrumentType', 'SerialNumber', 'EquipmentModeType', 'AprovaedByUserUID', 'SubmittedStatusType', 'SubmittedUserUID', 'ResponseStatusType', 'Response', 'ResponceErrorDescription', 'SurveyMode'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'CalibrationVerificationFlag', 'WindSpeedStart', 'WindSpeedEnd', 'EstimatedFeet', 'EstimatedServices', 'ApprovedFlag', 'SubmittedFlag', 'CompletedFlag', 'PlaceHolderFlag'], 'integer'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset', 'ApprovedDTLT', 'SubmittedDTLT', 'ResponseDTLT', 'CompletedDTLT'], 'safe'],
            [['CalibrationLevel', 'EstimatedHours'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tInspecitonServicesID' => 'T Inspeciton Services ID',
            'InspecitonServicesUID' => 'Inspeciton Services Uid',
            'MasterLeakLogUID' => 'Master Leak Log Uid',
            'MapGridUID' => 'Map Grid Uid',
            'InspectionRequestLogUID' => 'Inspection Request Log Uid',
            'InspecitonEquipmentUID' => 'Inspeciton Equipment Uid',
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
            'StatusType' => 'Status Type',
            'EquipmentType' => 'Equipment Type',
            'InstrumentType' => 'Instrument Type',
            'SerialNumber' => 'Serial Number',
            'CalibrationLevel' => 'Calibration Level',
            'CalibrationVerificationFlag' => 'Calibration Verification Flag',
            'WindSpeedStart' => 'Wind Speed Start',
            'WindSpeedEnd' => 'Wind Speed End',
            'EquipmentModeType' => 'Equipment Mode Type',
            'EstimatedFeet' => 'Estimated Feet',
            'EstimatedServices' => 'Estimated Services',
            'EstimatedHours' => 'Estimated Hours',
            'ApprovedFlag' => 'Approved Flag',
            'AprovaedByUserUID' => 'Aprovaed By User Uid',
            'ApprovedDTLT' => 'Approved Dtlt',
            'SubmittedFlag' => 'Submitted Flag',
            'SubmittedStatusType' => 'Submitted Status Type',
            'SubmittedUserUID' => 'Submitted User Uid',
            'SubmittedDTLT' => 'Submitted Dtlt',
            'ResponseStatusType' => 'Response Status Type',
            'Response' => 'Response',
            'ResponceErrorDescription' => 'Responce Error Description',
            'ResponseDTLT' => 'Response Dtlt',
            'CompletedFlag' => 'Completed Flag',
            'CompletedDTLT' => 'Completed Dtlt',
            'SurveyMode' => 'Survey Mode',
            'PlaceHolderFlag' => 'Place Holder Flag',
        ];
    }
}
