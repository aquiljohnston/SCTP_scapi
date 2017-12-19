<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "menus.ModuleMenuTb".
 *
 * @property integer $ModuleMenuID
 * @property string $ModuleMenuName
 * @property string $ModuleMenuNavMenuName
 * @property integer $ModuleMenuNavMenuID
 * @property string $ModuleMenuCreateDate
 * @property string $ModuleMenuCreatedBy
 * @property string $ModuleMenuModifiedDate
 * @property string $ModuleMenuModifiedBy
 * @property string $ModuleMenuPermissionName
 * @property integer $SortSequence
 */
class MenusModuleMenu extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menus.ModuleMenuTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ModuleMenuName', 'ModuleMenuNavMenuName', 'ModuleMenuPermissionName', 'ModuleMenuCreatedBy', 'ModuleMenuModifiedBy'], 'string'],
            [['ModuleMenuNavMenuID', 'SortSequence'], 'integer'],
            [['ModuleMenuCreateDate', 'ModuleMenuModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ModuleMenuID' => 'Module Menu ID',
            'ModuleMenuName' => 'Module Menu Name',
            'ModuleMenuNavMenuName' => 'Module Menu Nav Menu Name',
            'ModuleMenuNavMenuID' => 'Module Menu Nav Menu ID',
            'ModuleMenuCreateDate' => 'Module Menu Create Date',
            'ModuleMenuCreatedBy' => 'Module Menu Created By',
            'ModuleMenuModifiedDate' => 'Module Menu Modified Date',
            'ModuleMenuModifiedBy' => 'Module Menu Modified By',
            'ModuleMenuPermissionName' => 'Module Menu Permission Name',
            'SortSequence' => 'Sort Sequence',
        ];
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getMenuProjectModuleTbs()
    {
        return $this->hasMany(MenusProjectModule::className(), ['ProjectModulesName' => 'ModuleMenuName']);
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(SCUser::className(), ['ProjectID' => 'ProjectModulesProjectID'])
			->via('menuProjectModuleTbs');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenusModuleSubMenuTbs()
    {
        return $this->hasMany(MenusModuleSubMenu::className(), ['ModuleSubMenusModuleMenuID_FK' => 'ModuleMenuNavMenuID']);
    }
}
