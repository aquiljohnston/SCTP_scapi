<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
*  LeakLog represents the model behind the update/edit form on the leak log edit/view modals.
*
* @property string $AssetAddressIndicationUID
* @property string $UserLANID
* @property string $Date
* @property string $MapPlat
* @property string $SurveyType
* @property integer $Approved
* @property string $PipelineType
* @property string $HouseNo
* @property string $Street
* @property string $AptSuite
* @property string $City
* @property string $MeterID
* @property string $Comments
* @property string $MapPlatLeakNo
* @property string $LeakNo
* @property string $RouteName
* @property string $FacilityType
* @property string $AboveOrBelow
* @property string $InitialLeakSource
* @property string $ReportedBy
* @property string $DescriptionReadLoc
* @property string $PavedWallToWall
* @property string $SurfaceOverReadLoc
* @property string $OtherLocationSurface
* @property string $Within5FeetOfBuilding
* @property string $SuspectCopper
* @property string $InstFoundBy
* @property string $GradeByInstType
* @property string $InstGradeBy
* @property string $ReadingInPercentGas
* @property string $Grade
* @property string $InfoCodes
* @property string $PotentialHCA
* @property string $ConstructionSupervisor
* @property string $DistPlanningEngineer
* @property string $PipelineEngineer
* @property string $LocationRemarks
* @property integer $addressGPSIcon
* @property integer $leakGPSIcon
* @property integer $LockFlag
*/
class WebManagementLeakLogForm extends  \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementLeakLogForm';
    }

}
