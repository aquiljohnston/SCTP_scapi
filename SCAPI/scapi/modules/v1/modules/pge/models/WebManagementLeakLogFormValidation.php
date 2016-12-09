<?php

namespace app\modules\v1\modules\pge\models;

use Yii;
use yii\base\Model;

// TODO find a better way than this, maybe create a special validator clas and use that class to also validate the frontend model
/**
 *
 * WebManagementLeakLogFormValidation is a model used to validate the WebManagementLeakLogForm
 * It contains the same validation rules as the LeakLog model on the web front end
 */
class WebManagementLeakLogFormValidation extends  Model
{
    public $AssetAddressIndicationUID;
    public $CreatorLANID;
    public $CreatedDate;
    public $ApproverLANID;
    public $ApprovedDate;
    public $MapPlat;
    public $SurveyType;
    public $Approved;
    public $PipelineType;
    public $HouseNo;
    public $Street;
    public $AptSuite;
    public $City;
    public $MeterID;
    public $Comments;
    public $MapPlatLeakNo;
    public $LeakNo;
    public $RouteName;
    public $FacilityType;
    public $AboveOrBelow;
    public $InitialLeakSource;
    public $ReportedBy;
    public $DescriptionReadLoc;
    public $PavedWallToWall;
    public $SurfaceOverReadLoc;
    public $OtherLocationSurface;
    public $Within5FeetOfBuilding;
    public $SuspectCopper;
    public $InstFoundBy;
    public $GradeByInstType;
    public $InstGradeBy;
    public $ReadingInPercentGas;
    public $Grade;
    public $InfoCodes;
    public $PotentialHCA;
    public $ConstructionSupervisor;
    public $DistPlanningEngineer;
    public $PipelineEngineer;
    public $LocationRemarks;
    public $addressGPSIcon;
    public $leakGPSIcon;
    public $LockFlag;
    public $CurrentDate;
    public $CurrentUserLANID;
    public $Photo1;
    public $Photo2;
    public $Photo3;
    public $NonPremise;
    public $MapGridUID;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementLeakLogForm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [
//                [
//                    'AssetAddressIndicationUID','CreatorLANID','CreatedDate','ApproverLANID','ApprovedDate',
//                    'MapPlat','SurveyType','PipelineType','HouseNo','Street','AptSuite',
//                    'City','MeterID','Comments','MapPlatLeakNo',
//                    'LeakNo','RouteName','FacilityType','AboveOrBelow',
//                    'InitialLeakSource','ReportedBy','DescriptionReadLoc','PavedWallToWall',
//                    'SurfaceOverReadLoc','OtherLocationSurface','Within5FeetOfBuilding','SuspectCopper',
//                    'InstFoundBy','GradeByInstType','InstGradeBy','ReadingInPercentGas',
//                    'Grade','InfoCodes','PotentialHCA','ConstructionSupervisor','DistPlanningEngineer',
//                    'PipelineEngineer','LocationRemarks',
//                    'CurrentDate', 'CurrentUserLANID', 'Photo1', 'Photo2', 'Photo3',
//                    'MapGridUID'
//                ], 'string'
//            ],
            [
                [
                    'Approved','addressGPSIcon', 'leakGPSIcon','LockFlag', 'NonPremise'
                ], 'boolean', 'trueValue'=>'1', 'falseValue'=>'0'
            ],
            [
                [
                    'AssetAddressIndicationUID','CreatorLANID','CreatedDate','ApproverLANID','ApprovedDate',
                    'MapPlat','SurveyType',
                    'PipelineType','HouseNo','Street','AptSuite',
                    'City','MeterID','Comments','MapPlatLeakNo',
                    'LeakNo','RouteName','FacilityType','AboveOrBelow',
                    'InitialLeakSource','ReportedBy','DescriptionReadLoc','PavedWallToWall',
                    'SurfaceOverReadLoc','OtherLocationSurface','Within5FeetOfBuilding','SuspectCopper',
                    'InstFoundBy','GradeByInstType','InstGradeBy','ReadingInPercentGas',
                    'Grade','InfoCodes','PotentialHCA','ConstructionSupervisor','DistPlanningEngineer',
                    'PipelineEngineer','LocationRemarks',
                ],
                'filter',
                'filter'=>'trim'
            ],
            [
                [
                    'Street',
                    'City',
                    'FacilityType',
                    'ReportedBy',
                    'DescriptionReadLoc',
                    'PavedWallToWall',
                    'SurfaceOverReadLoc',
                    'SuspectCopper',
                    'PotentialHCA',
                ],
                'required',
                'message' => '{attribute} is required'
            ],
            [
                [
                    'ConstructionSupervisor',
                    'DistPlanningEngineer',
                    'PipelineEngineer',
                ],
                'required',
                'when'=>function($model) {
                    $ret = $model->PotentialHCA && (strtolower($model->PotentialHCA)=='yes' || $model->PotentialHCA =='1');
                    return $ret;
                },
                'whenClient' => "function(attribute, value){
                    var ret = false,
                    jqEl = $('#leaklog-potentialhca');
                    ret = (jqEl && jqEl.val() && ( jqEl.val().toLowerCase()=='yes' || jqEl.val()=='1');
                    return ret;
                };",
                'message' => '{attribute} is required and must be exactly 4 chars long.'
            ],
            [
                [
                    'ConstructionSupervisor',
                    'DistPlanningEngineer',
                    'PipelineEngineer',
                ],
                'validateHCAAdditionalFields'
            ],
            [
                'InitialLeakSource',
                'required',
                'when'=>function($model) {
                    $ret = $model->FacilityType && (strpos(strtolower($model->FacilityType),'s')===0);
                    return $ret;
                },
                'whenClient' => "function(attribute, value){
                    var ret = false,
                    jqEl = $('#leaklog-facilitytype');
                    ret = (jqEl && jqEl.val() && ( jqEl.val().toLowerCase().indexOf('s')===0);
                    return ret;
                };",

            ],
            [
                'OtherLocationSurface',
                'required',
                'when'=>function($model) {
                    $ret = $model->SurfaceOverReadLoc && (strpos(strtolower($model->SurfaceOverReadLoc),'o')===0);
                    return $ret;
                },
                'whenClient' => "function(attribute, value){
                    var ret = false,
                    jqEl = $('#leaklog-surfaceoverreadloc');
                    ret = (jqEl && jqEl.val() && ( jqEl.val().toLowerCase().indexOf('o')===0);
                    return ret;
                };",
                'message' => '{attribute} is required and must be at least 2 chars long. No special characters. No leading spaces or .,?'
            ],
            [
                'OtherLocationSurface',
                'validateOtherLocationSurface'
            ],
            [
                'HouseNo',
                'required',
                'message' => '{attribute} is required. If not available the please write NA in the box.'
            ],
            [
                'Comments',
                'required',
                'when'=>function($model) {
                    $ret = $model->HouseNo && (strtolower($model->HouseNo)=='na' || strtolower($model->HouseNo)=='n/a');
                    return $ret;
                },
                'whenClient' => "function(attribute, value){
                    var ret = false,
                    jqEl = $('#leaklog-houseno');
                    ret = (jqEl && ( jqEl.val().toLowerCase()=='na' || jqEl.val().toLowerCase()=='n/a');
                    return ret;
                };",
                'message' => '{attribute} is required and must be at least 10 chars long.. No special characters. No leading spaces or .,?'
            ],
            [
                'Comments',
                'validateComments'
            ],
            [
                'DescriptionReadLoc',
                'validateDescriptionReadLoc'
            ],
            [
                'InfoCodes',
                'required',
                'when'=>function($model) {
                    $ret = $model->ReadingInPercentGas && ($model->ReadingInPercentGas <2);
                    return $ret;
                },
                'whenClient' => "function(attribute, value){
                    var ret = false,
                    jqEl = $('#leaklog-readinginpercentgas');
                    ret = (jqEl && jqEl.val() < 2);
                    return ret;
                };",
                'message' => '{attribute} is required'
            ],
        ];

    }

    public function attributeLabels()
    {
        return [
            'PipelineType'           => 'PipelineType',
            'HouseNo'                => 'HouseNo',
            'Street'                 => 'Street',
            'AptSuite'               => 'Apt Suite',
            'City'                   => 'City',
            'MeterID'                => 'Meter ID',
            'Comments'               => 'Comments',
            'MapPlatLeakNo'          => 'Map Plat Leak No',
            'LeakNo'                 => 'Leak No',
            'RouteName'              => 'Route Name',
            'FacilityType'           => 'FacilityType',
            'AboveOrBelow'           => 'AboveOrBelow',
            'InitialLeakSource'      => 'Initial Leak Source',
            'ReportedBy'             => 'Reported By',
            'DescriptionReadLoc'     => 'Description Read Loc',
            'PavedWallToWall'        => 'Paved Wall to Wall',
            'SurfaceOverReadLoc'     => 'Surface Over Read Loc',
            'OtherLocationSurface'   => 'Other Location Surface',
            'Within5FeetOfBuilding'  => 'Within 5 Feet Of Building',
            'SuspectCopper'          => 'Suspect Copper',
            'InstFoundBy'            => 'InstFoundBy',
            'GradeByInstType'        => 'GradeByInstType',
            'InstGradeBy'            => 'InstGradeBy',
            'ReadingInPercentGas'    => 'ReadingInPercentGas',
            'Grade'                  => 'Grade',
            'InfoCodes'              => 'Info Codes',
            'PotentialHCA'           => 'Potential HCA',
            'ConstructionSupervisor' => 'Construction Supervisor',
            'DistPlanningEngineer'   => 'Dist Planning Engineer',
            'PipelineEngineer'       => 'Pipeline Engineer',
            'LocationRemarks'        => 'Location Remarks',
        ];
    }

    public function validateHCAAdditionalFields($attribute, $params) {
        $ret = true;
        if ($this->PotentialHCA =='Y' || $this->PotentialHCA =='Yes' || $this->PotentialHCA =='1') {
            if ($this->$attribute=='' || strlen(trim($this->$attribute))!==4) {
                $this->addError($attribute, $this->getAttributeLabel($attribute).' must be 4 chars long');
                $ret = false;
            }
        }

        return $ret;
    }

    public function validateOtherLocationSurface($attribute, $params) {
        $ret = true;
        if ($this->SurfaceOverReadLoc == 'O' || $this->SurfaceOverReadLoc == 'O - Other') {
            if (strlen(trim($this->$attribute))<2 || preg_match('/^[\ \.\?,]+/',$this->$attribute)
                || !preg_match('/^[0-9a-zA-Z_\(\)\/\ \.\?\,]+$/',$this->$attribute)) {  // Allowed chars 0-9A-Za-z_()/ .,?
                $this->addError($attribute, $this->getAttributeLabel($attribute).' must be at least 2 chars long. No special characters. No leading spaces or .,?.  Allowed chars: 0-9A-Za-z_()/ .,?');
                $ret = false;
            }
        }

        return $ret;
    }

    public function validateComments($attribute, $params) {
        $ret = true;

        if (strlen(trim($this->$attribute))<10 || preg_match('/^[\ \.\?,]+/',$this->$attribute)
            || !preg_match('/^[0-9a-zA-Z_\(\)\/\ \.\?\,]+$/',$this->$attribute)) {  // Allowed chars 0-9A-Za-z_()/ .,?
            $this->addError($attribute, $this->getAttributeLabel($attribute).' must be at least 10 chars long when. No special characters. No leading spaces or .,?. Allowed chars: 0-9A-Za-z_()/ .,?');
            $ret = false;
        }

        return $ret;
    }

    public function validateDescriptionReadLoc($attribute, $params) {
        return $this->validateComments($attribute,$params);
    }
}
