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
 *
 * @property EquipmentTb[] $equipmentTbs
 * @property ProjectEmployeeTb[] $projectEmployeeTbs
 * @property ProjectUserTb[] $projectUserTbs
 * @property ProjectOQRequirementsTb[] $projectOQRequirementsTbs
 * @property ClientTb $project
 */
class Project extends \yii\db\ActiveRecord
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
            [['ProjectID', 'ProjectName'], 'required'],
            [['ProjectID', 'ProjectStatus', 'ProjectClientID'], 'integer'],
            [['ProjectName', 'ProjectDescription', 'ProjectNotes', 'ProjectType'], 'string'],
            [['ProjectStartDate', 'ProjectEndDate'], 'safe']
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentTbs()
    {
        return $this->hasMany(EquipmentTb::className(), ['EquipmentProjectID' => 'ProjectID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectEmployeeTbs()
    {
        return $this->hasMany(ProjectEmployeeTb::className(), ['PE_ProjectID' => 'ProjectID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectUserTbs()
    {
        return $this->hasMany(ProjectUserTb::className(), ['ProjUserProjectID' => 'ProjectID']);
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
    public function getProject()
    {
        return $this->hasOne(ClientTb::className(), ['ClientID' => 'ProjectID']);
    }
}
