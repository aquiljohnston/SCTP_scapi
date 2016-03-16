<?php

namespace app\models;

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
 * @property string $ProjectStartDate
 * @property string $ProjectEndDate
 * @property string $ProjectCreateDate
 * @property string $ProjectCreatedBy
 * @property string $ProjectModifiedDate
 * @property string $ProjectModifiedBy
 *
 * @property ProjectUserTb[] $projectUserTbs
 * @property ProjectOQRequirementstb[] $projectOQRequirementstbs
 * @property ClientTb $projectClient
 */
class Project extends BaseActiveRecord
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
            [['ProjectName'], 'required'],
            [['ProjectName', 'ProjectDescription', 'ProjectNotes', 'ProjectType', 'ProjectCreatedBy', 'ProjectModifiedBy'], 'string'],
            [['ProjectStatus', 'ProjectClientID'], 'integer'],
            [['ProjectStartDate', 'ProjectEndDate', 'ProjectCreateDate', 'ProjectModifiedDate'], 'safe']
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
            'ProjectClientID' => 'Project Client ID',
            'ProjectStartDate' => 'Project Start Date',
            'ProjectEndDate' => 'Project End Date',
            'ProjectCreateDate' => 'Project Create Date',
            'ProjectCreatedBy' => 'Project Created By',
            'ProjectModifiedDate' => 'Project Modified Date',
            'ProjectModifiedBy' => 'Project Modified By',
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
    public function getProjectOQRequirementstbs()
    {
        return $this->hasMany(ProjectOQRequirementstb::className(), ['ProjectOQRequirementsProjectID' => 'ProjectID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectClient()
    {
        return $this->hasOne(ClientTb::className(), ['ClientID' => 'ProjectClientID']);
    }
}
