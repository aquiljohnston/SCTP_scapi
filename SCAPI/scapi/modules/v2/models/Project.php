<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "ProjectTb".
 *
 * @property integer $ProjectID
 * @property string $ProjectName
 * @property string $ProjectDescription
 * @property string $ProjectNotes
 * @property string $ProjectType
 * @property integer $ProjectStatus
 * @property integer $ProjectClientID
 * @property string $ProjectState
 * @property string $ProjectUrlPrefix
 * @property string $ProjectStartDate
 * @property string $ProjectEndDate
 * @property string $ProjectCreateDate
 * @property integer $ProjectCreatedBy
 * @property string $ProjectModifiedDate
 * @property integer $ProjectModifiedBy
 * @property double $ProjectActivityGPSInterval
 * @property double $ProjectSurveyGPSInterval
 * @property integer $ProjectSurveyGPSMinDistance
 * @property string $ProjectMinimumAppVersion
 * @property string $ProjectRefreshDateTime
 * @property string $ProjectLandingPage 
 * @property integer $ProjectQBProjectID 
 * @property integer $ProjectReferenceID 
 *
 * @property ProjectUserTb[] $projectUserTbs
 * @property ProjectOQRequirementsTb[] $projectOQRequirementsTbs
 * @property ClientTb $projectClient
 */
class Project extends \app\modules\v2\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ProjectTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ProjectName', 'ProjectDescription', 'ProjectNotes', 'ProjectType', 'ProjectState', 'ProjectUrlPrefix', 'ProjectMinimumAppVersion', 'ProjectLandingPage'], 'string'],
            [['ProjectStatus', 'ProjectClientID', 'ProjectCreatedBy', 'ProjectModifiedBy', 'ProjectSurveyGPSMinDistance', 'ProjectQBProjectID', 'ProjectReferenceID','ProjectProjectTypeReferenceID'], 'integer'],
            [['ProjectStartDate', 'ProjectEndDate', 'ProjectCreateDate', 'ProjectModifiedDate', 'ProjectRefreshDateTime'], 'safe'],
			[['ProjectActivityGPSInterval', 'ProjectSurveyGPSInterval'], 'number'],
			[['ProjectUrlPrefix'], 'unique']
        ];
    }

    /**
     * @inheritdoc
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
			'ProjectUrlPrefix' => 'Project Url Prefix',
            'ProjectClientID' => 'Project Client ID',
			'ProjectState' => 'Project State',
            'ProjectStartDate' => 'Project Start Date',
            'ProjectEndDate' => 'Project End Date',
            'ProjectCreateDate' => 'Project Create Date',
            'ProjectCreatedBy' => 'Project Created By',
            'ProjectModifiedDate' => 'Project Modified Date',
            'ProjectModifiedBy' => 'Project Modified By',
			'ProjectActivityGPSInterval' => 'Project Activity GPS Interval',
			'ProjectSurveyGPSInterval' => 'Project Survey GPS Interval',
			'ProjectSurveyGPSMinDistance' => 'Project Survey GPS Min Distance',
			'ProjectMinimumAppVersion' => 'Project Minimum App Version',
			'ProjectLandingPage' => 'Project Landing Page',
			'ProjectQBProjectID' => 'Project QB Project ID',
			'ProjectReferenceID' => 'Project Reference ID',
            'ProjectRefreshDateTime' => 'Project Refresh Date Time',
			'ProjectProjectTypeReferenceID' => 'Project Type Reference ID',
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
