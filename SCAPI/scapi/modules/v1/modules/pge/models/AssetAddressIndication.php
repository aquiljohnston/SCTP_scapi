<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "tgAssetAddressIndication".
 *
 * @property integer $AssetAddressIndicationsID
 * @property string $AssetAddressIndicationUID
 * @property string $AssetAddressUID
 * @property string $InspectionRequestLogUID
 * @property string $MapGridUID
 * @property string $MasterLeakLogUID
 * @property integer $ProjectID
 * @property string $SourceID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $SrcDTLT
 * @property string $SrvDTLT
 * @property string $SrvDTLTOffset
 * @property string $SrcOpenDTLT
 * @property string $SrcClosedDTLT
 * @property string $GPSType
 * @property string $GPSSentence
 * @property double $Latitude
 * @property double $Longitude
 * @property string $SHAPE
 * @property string $Comments
 * @property string $RevisionComments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $StatusType
 * @property string $ManualMapPlat
 * @property string $PipelineType
 * @property string $SurveyType
 * @property string $Map
 * @property string $Plat
 * @property string $RecordedMap
 * @property string $RecordedPlat
 * @property string $RecordedBlock
 * @property string $LandmarkType
 * @property string $Route
 * @property string $Line
 * @property integer $HouseNoNAFlag
 * @property string $HouseNo
 * @property string $Street1
 * @property string $City
 * @property string $DescriptionReadingLocation
 * @property string $County
 * @property string $CountyCode
 * @property string $FacilityType
 * @property string $LocationType
 * @property string $InitialLeakSourceType
 * @property string $ReportedByType
 * @property string $LeakNo
 * @property string $SAPNo
 * @property string $PavedType
 * @property string $SORLType
 * @property string $SORLOther
 * @property string $Within5FeetOfBuildingType
 * @property string $SuspectedCopperType
 * @property string $EquipmentFoundByUID
 * @property string $FoundBy
 * @property string $FoundBySerialNumber
 * @property string $InstrumentTypeGradeByType
 * @property string $EquipmentGradeByUID
 * @property string $GradeBy
 * @property string $GradeBySerialNumber
 * @property double $ReadingGrade
 * @property string $GradeType
 * @property string $InfoCodesType
 * @property string $PotentialHCAType
 * @property string $Grade2PlusRequested
 * @property integer $TwoPercentOrLessSuspectCopperFlag
 * @property string $LeakDownGradedFlag
 * @property string $HCAConstructionSupervisorUserUID
 * @property string $HCADistributionPlanningEngineerUserUID
 * @property string $HCAPipelineEngineerUserUID
 * @property string $Photo1
 * @property string $Photo2
 * @property string $Photo3
 * @property string $OptionalData1
 * @property string $OptionalData2
 * @property string $OptionalData3
 * @property string $OptionalData4
 * @property string $OptionalData5
 * @property string $OptionalData6
 * @property string $OptionalData7
 * @property string $OptionalData8
 * @property string $OptionalData9
 * @property string $OptionalData10
 * @property string $OptionalData11
 * @property string $OptionalData12
 * @property integer $ApprovedFlag
 * @property string $ApprovedByUserUID
 * @property string $ApprovedDTLT
 * @property integer $SubmittedFlag
 * @property string $SubmittedStatusType
 * @property string $SubmittedUserUID
 * @property string $SubmittedDTLT
 * @property string $ResponseStatusType
 * @property string $ResponseComments
 * @property string $ResponceErrorComments
 * @property string $ResponseDTLT
 * @property integer $CompletedFlag
 * @property string $CompletedDTLT
 */
class AssetAddressIndication extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tgAssetAddressIndication';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AssetAddressIndicationUID', 'AssetAddressUID', 'InspectionRequestLogUID', 'MapGridUID', 'MasterLeakLogUID', 'SourceID', 'CreatedUserUID', 'ModifiedUserUID', 'GPSType', 'GPSSentence', 'SHAPE', 'Comments', 'RevisionComments', 'StatusType', 'ManualMapPlat', 'PipelineType', 'SurveyType', 'Map', 'Plat', 'RecordedMap', 'RecordedPlat', 'RecordedBlock', 'LandmarkType', 'Route', 'Line', 'HouseNo', 'Street1', 'City', 'DescriptionReadingLocation', 'County', 'CountyCode', 'FacilityType', 'LocationType', 'InitialLeakSourceType', 'ReportedByType', 'LeakNo', 'SAPNo', 'PavedType', 'SORLType', 'SORLOther', 'Within5FeetOfBuildingType', 'SuspectedCopperType', 'EquipmentFoundByUID', 'FoundBy', 'FoundBySerialNumber', 'InstrumentTypeGradeByType', 'EquipmentGradeByUID', 'GradeBy', 'GradeBySerialNumber', 'GradeType', 'InfoCodesType', 'PotentialHCAType', 'LeakDownGradedFlag', 'HCAConstructionSupervisorUserUID', 'HCADistributionPlanningEngineerUserUID', 'HCAPipelineEngineerUserUID', 'Photo1', 'Photo2', 'Photo3', 'OptionalData1', 'OptionalData2', 'OptionalData3', 'OptionalData4', 'OptionalData5', 'OptionalData6', 'OptionalData7', 'OptionalData8', 'OptionalData9', 'OptionalData10', 'OptionalData11', 'OptionalData12', 'ApprovedByUserUID', 'SubmittedStatusType', 'SubmittedUserUID', 'ResponseStatusType', 'ResponseComments', 'ResponceErrorComments'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'HouseNoNAFlag', 'TwoPercentOrLessSuspectCopperFlag', 'ApprovedFlag', 'SubmittedFlag', 'CompletedFlag'], 'integer'],
            [['ModifiedUserUID', 'SrcDTLT', 'CompletedFlag'], 'required'],
            [['SrcDTLT', 'SrvDTLT', 'SrvDTLTOffset', 'SrcOpenDTLT', 'SrcClosedDTLT', 'Grade2PlusRequested', 'ApprovedDTLT', 'SubmittedDTLT', 'ResponseDTLT', 'CompletedDTLT'], 'safe'],
            [['Latitude', 'Longitude', 'ReadingGrade'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AssetAddressIndicationsID' => 'Asset Address Indications ID',
            'AssetAddressIndicationUID' => 'Asset Address Indication Uid',
            'AssetAddressUID' => 'Asset Address Uid',
            'InspectionRequestLogUID' => 'Inspection Request Log Uid',
            'MapGridUID' => 'Map Grid Uid',
            'MasterLeakLogUID' => 'Master Leak Log Uid',
            'ProjectID' => 'Project ID',
            'SourceID' => 'Source ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'SrcDTLT' => 'Src Dtlt',
            'SrvDTLT' => 'Srv Dtlt',
            'SrvDTLTOffset' => 'Srv Dtltoffset',
            'SrcOpenDTLT' => 'Src Open Dtlt',
            'SrcClosedDTLT' => 'Src Closed Dtlt',
            'GPSType' => 'Gpstype',
            'GPSSentence' => 'Gpssentence',
            'Latitude' => 'Latitude',
            'Longitude' => 'Longitude',
            'SHAPE' => 'Shape',
            'Comments' => 'Comments',
            'RevisionComments' => 'Revision Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'StatusType' => 'Status Type',
            'ManualMapPlat' => 'Manual Map Plat',
            'PipelineType' => 'Pipeline Type',
            'SurveyType' => 'Survey Type',
            'Map' => 'Map',
            'Plat' => 'Plat',
            'RecordedMap' => 'Recorded Map',
            'RecordedPlat' => 'Recorded Plat',
            'RecordedBlock' => 'Recorded Block',
            'LandmarkType' => 'Landmark Type',
            'Route' => 'Route',
            'Line' => 'Line',
            'HouseNoNAFlag' => 'House No Naflag',
            'HouseNo' => 'House No',
            'Street1' => 'Street1',
            'City' => 'City',
            'DescriptionReadingLocation' => 'Description Reading Location',
            'County' => 'County',
            'CountyCode' => 'County Code',
            'FacilityType' => 'Facility Type',
            'LocationType' => 'Location Type',
            'InitialLeakSourceType' => 'Initial Leak Source Type',
            'ReportedByType' => 'Reported By Type',
            'LeakNo' => 'Leak No',
            'SAPNo' => 'Sapno',
            'PavedType' => 'Paved Type',
            'SORLType' => 'Sorltype',
            'SORLOther' => 'Sorlother',
            'Within5FeetOfBuildingType' => 'Within5 Feet Of Building Type',
            'SuspectedCopperType' => 'Suspected Copper Type',
            'EquipmentFoundByUID' => 'Equipment Found By Uid',
            'FoundBy' => 'Found By',
            'FoundBySerialNumber' => 'Found By Serial Number',
            'InstrumentTypeGradeByType' => 'Instrument Type Grade By Type',
            'EquipmentGradeByUID' => 'Equipment Grade By Uid',
            'GradeBy' => 'Grade By',
            'GradeBySerialNumber' => 'Grade By Serial Number',
            'ReadingGrade' => 'Reading Grade',
            'GradeType' => 'Grade Type',
            'InfoCodesType' => 'Info Codes Type',
            'PotentialHCAType' => 'Potential Hcatype',
            'Grade2PlusRequested' => 'Grade2 Plus Requested',
            'TwoPercentOrLessSuspectCopperFlag' => 'Two Percent Or Less Suspect Copper Flag',
            'LeakDownGradedFlag' => 'Leak Down Graded Flag',
            'HCAConstructionSupervisorUserUID' => 'Hcaconstruction Supervisor User Uid',
            'HCADistributionPlanningEngineerUserUID' => 'Hcadistribution Planning Engineer User Uid',
            'HCAPipelineEngineerUserUID' => 'Hcapipeline Engineer User Uid',
            'Photo1' => 'Photo1',
            'Photo2' => 'Photo2',
            'Photo3' => 'Photo3',
            'OptionalData1' => 'Optional Data1',
            'OptionalData2' => 'Optional Data2',
            'OptionalData3' => 'Optional Data3',
            'OptionalData4' => 'Optional Data4',
            'OptionalData5' => 'Optional Data5',
            'OptionalData6' => 'Optional Data6',
            'OptionalData7' => 'Optional Data7',
            'OptionalData8' => 'Optional Data8',
            'OptionalData9' => 'Optional Data9',
            'OptionalData10' => 'Optional Data10',
            'OptionalData11' => 'Optional Data11',
            'OptionalData12' => 'Optional Data12',
            'ApprovedFlag' => 'Approved Flag',
            'ApprovedByUserUID' => 'Approved By User Uid',
            'ApprovedDTLT' => 'Approved Dtlt',
            'SubmittedFlag' => 'Submitted Flag',
            'SubmittedStatusType' => 'Submitted Status Type',
            'SubmittedUserUID' => 'Submitted User Uid',
            'SubmittedDTLT' => 'Submitted Dtlt',
            'ResponseStatusType' => 'Response Status Type',
            'ResponseComments' => 'Response Comments',
            'ResponceErrorComments' => 'Responce Error Comments',
            'ResponseDTLT' => 'Response Dtlt',
            'CompletedFlag' => 'Completed Flag',
            'CompletedDTLT' => 'Completed Dtlt',
        ];
    }
}
