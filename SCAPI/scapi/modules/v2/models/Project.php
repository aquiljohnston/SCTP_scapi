<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "ProjectTb".
 *
 * @property int $ProjectID
 * @property string $ProjectName
 * @property string $ProjectDescription
 * @property string $ProjectNotes
 * @property string $ProjectType
 * @property int $ProjectStatus
 * @property int $ProjectClientID
 * @property string $ProjectState
 * @property string $ProjectUrlPrefix
 * @property string $ProjectStartDate
 * @property string $ProjectEndDate
 * @property string $ProjectCreateDate
 * @property string $ProjectCreatedBy
 * @property string $ProjectModifiedDate
 * @property string $ProjectModifiedBy
 * @property double $ProjectActivityGPSInterval
 * @property double $ProjectSurveyGPSInterval
 * @property int $ProjectSurveyGPSMinDistance
 * @property string $ProjectMinimumAppVersion
 * @property string $ProjectLandingPage
 * @property string $ProjectQBProjectID
 * @property string $ProjectReferenceID
 * @property string $ProjectRefreshDateTime
 * @property string $ProjectProjectTypeReferenceID
 * @property string $ProjectClass
 * @property double $DistanceThresholdInMeters
 * @property double $TimeThreshold
 * @property double $StationaryThresholdInMeters
 *
 * @property ProjectUserTb[] $projectUserTbs
 * @property ProjectOQRequirementsTb[] $projectOQRequirementsTbs
 * @property ClientTb $projectClient
 */
class Project extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ProjectTb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProjectName', 'ProjectDescription', 'ProjectNotes', 'ProjectType', 'ProjectState', 'ProjectUrlPrefix', 'ProjectCreatedBy', 'ProjectModifiedBy', 'ProjectMinimumAppVersion', 'ProjectLandingPage', 'ProjectQBProjectID', 'ProjectReferenceID', 'ProjectProjectTypeReferenceID', 'ProjectClass'], 'string'],
            [['ProjectStatus', 'ProjectClientID', 'ProjectSurveyGPSMinDistance'], 'integer'],
            [['ProjectStartDate', 'ProjectEndDate', 'ProjectCreateDate', 'ProjectModifiedDate', 'ProjectRefreshDateTime'], 'safe'],
            [['ProjectActivityGPSInterval', 'ProjectSurveyGPSInterval', 'DistanceThresholdInMeters', 'TimeThreshold', 'StationaryThresholdInMeters'], 'number'],
            [['ProjectClientID'], 'exist', 'skipOnError' => true, 'targetClass' => ClientTb::className(), 'targetAttribute' => ['ProjectClientID' => 'ClientID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ProjectID' => 'Project ID',
            'ProjectName' => 'Project Name',
            'ProjectDescription' => 'Project Description',
            'ProjectNotes' => 'Project Notes',
            'ProjectType' => 'Project Type',
            'ProjectStatus' => 'Project Status',
            'ProjectClientID' => 'Project Client ID',
            'ProjectState' => 'Project State',
            'ProjectUrlPrefix' => 'Project Url Prefix',
            'ProjectStartDate' => 'Project Start Date',
            'ProjectEndDate' => 'Project End Date',
            'ProjectCreateDate' => 'Project Create Date',
            'ProjectCreatedBy' => 'Project Created By',
            'ProjectModifiedDate' => 'Project Modified Date',
            'ProjectModifiedBy' => 'Project Modified By',
            'ProjectActivityGPSInterval' => 'Project Activity Gpsinterval',
            'ProjectSurveyGPSInterval' => 'Project Survey Gpsinterval',
            'ProjectSurveyGPSMinDistance' => 'Project Survey Gpsmin Distance',
            'ProjectMinimumAppVersion' => 'Project Minimum App Version',
            'ProjectLandingPage' => 'Project Landing Page',
            'ProjectQBProjectID' => 'Project Qbproject ID',
            'ProjectReferenceID' => 'Project Reference ID',
            'ProjectRefreshDateTime' => 'Project Refresh Date Time',
            'ProjectProjectTypeReferenceID' => 'Project Project Type Reference ID',
            'ProjectClass' => 'Project Class',
            'DistanceThresholdInMeters' => 'Distance Threshold In Meters',
            'TimeThreshold' => 'Time Threshold',
            'StationaryThresholdInMeters' => 'Stationary Threshold In Meters',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectUserTbs()
    {
        return $this->hasMany(ProjectUser::className(), ['ProjUserProjectID' => 'ProjectID']);
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(SCUser::className(), ['UserID' => 'ProjUserUserID'])
			->via('projectUserTbs');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectOQRequirementsTbs()
    {
        return $this->hasMany(ProjectOQRequirementsTb::className(), ['ProjectOQRequirementsProjectID' => 'ProjectID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectClient()
    {
        return $this->hasOne(ClientTb::className(), ['ClientID' => 'ProjectClientID']);
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getMenuProjectModuleTbs()
    {
        return $this->hasMany(MenusProjectModule::className(), ['ProjectModulesProjectID' => 'ProjectID']);
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getModuleMenus()
    {
        return $this->hasMany(MenusModuleMenu::className(), ['ModuleMenuName' => 'ProjectModulesName'])
			->via('menuProjectModuleTbs');
    }
}
