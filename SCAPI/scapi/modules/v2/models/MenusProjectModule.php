<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "menus.ProjectModuleTb".
 *
 * @property integer $ProjectModulesID
 * @property integer $ProjectModulesProjectID
 * @property string $ProjectModulesName
 * @property string $ProjectModulesCreateDate
 * @property int $ProjectModulesCreatedBy
 * @property string $ProjectModulesModifiedDate
 * @property int $ProjectModulesModifiedBy
 */
class MenusProjectModule extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menus.ProjectModuleTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ProjectModulesProjectID', 'ProjectModulesCreatedBy', 'ProjectModulesModifiedBy'], 'integer'],
            [['ProjectModulesName'], 'string'],
            [['ProjectModulesCreateDate', 'ProjectModulesModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ProjectModulesID' => 'Project Modules ID',
            'ProjectModulesProjectID' => 'Project Modules Project ID',
            'ProjectModulesName' => 'Project Modules Name',
            'ProjectModulesCreateDate' => 'Project Modules Create Date',
            /*'ProjectModulesCreatedBy' => 'Project Modules Created By',*/
            'ProjectModulesModifiedDate' => 'Project Modules Modified Date',
            'ProjectModulesModifiedBy' => 'Project Modules Modified By',
        ];
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getProjModProject()
    {
        return $this->hasOne(ProjectTb::className(), ['ProjectID' => 'ProjectModulesProjectID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjModModule()
    {
        return $this->hasOne(MenusModuleMenu::className(), ['ModuleMenuName' => 'ProjectModulesName']);
    }
}
