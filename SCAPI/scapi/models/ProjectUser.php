<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Project_User_Tb".
 *
 * @property string $ProjUserID
 * @property string $ProjUserUserID
 * @property string $ProjUserProjectID
 * @property string $ProjUserProjectRoles
 * @property string $ProjUserComment
 * @property string $ProjUserCreateDate
 * @property string $ProjUserCreatedBy
 * @property string $ProjUserModifiedDate
 * @property string $ProjUserModifiedBy
 *
 * @property ProjectTb $projUserProject
 * @property UserTb $projUserUser
 */
class ProjectUser extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Project_User_Tb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ProjUserUserID', 'ProjUserProjectID'], 'integer'],
            [['ProjUserProjectRoles', 'ProjUserComment', 'ProjUserCreatedBy', 'ProjUserModifiedBy'], 'string'],
            [['ProjUserCreateDate', 'ProjUserModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ProjUserID' => 'Proj User ID',
            'ProjUserUserID' => 'Proj User User ID',
            'ProjUserProjectID' => 'Proj User Project ID',
            'ProjUserProjectRoles' => 'Proj User Project Roles',
            'ProjUserComment' => 'Proj User Comment',
            'ProjUserCreateDate' => 'Proj User Create Date',
            'ProjUserCreatedBy' => 'Proj User Created By',
            'ProjUserModifiedDate' => 'Proj User Modified Date',
            'ProjUserModifiedBy' => 'Proj User Modified By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjUserProject()
    {
        return $this->hasOne(ProjectTb::className(), ['ProjectID' => 'ProjUserProjectID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjUserUser()
    {
        return $this->hasOne(SCUser::className(), ['UserID' => 'ProjUserUserID']);
    }
}
