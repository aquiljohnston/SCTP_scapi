<?php

namespace app\modules\v2\models;

use Yii;

/**
 * This is the model class for table "menus.ModuleSubMenuTb".
 *
 * @property integer $ModuleSubMenusID
 * @property integer $ModuleSubMenusModuleMenuID_FK
 * @property string $ModuleSubMenusNavMenuName
 * @property string $ModuleSubMenusPermissionName
 * @property string $ModuleSubMenusURL
 * @property string $ModuleSubMenusComments
 * @property integer $ModuleSubMenusActiveFlag
 * @property integer $ModuleSubMenusParentID
 * @property integer $ModuleSubMenusSortSeq
 * @property string $ModuleSubMenusCreateDate
 * @property int $ModuleSubMenusCreatedBy
 * @property string $ModuleSubMenusModifiedDate
 * @property int $ModuleSubMenusModifiedBy
 */
class MenusModuleSubMenu extends BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menus.ModuleSubMenuTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ModuleSubMenusModuleMenuID_FK', 'ModuleSubMenusActiveFlag', 'ModuleSubMenusParentID', 'ModuleSubMenusSortSeq',  'ModuleSubMenusCreatedBy', 'ModuleSubMenusModifiedBy'], 'integer'],
            [['ModuleSubMenusNavMenuName', 'ModuleSubMenusPermissionName', 'ModuleSubMenusURL', 'ModuleSubMenusComments'], 'string'],
            [['ModuleSubMenusCreateDate', 'ModuleSubMenusModifiedDate'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ModuleSubMenusID' => 'Module Sub Menus ID',
            'ModuleSubMenusModuleMenuID_FK' => 'Module Sub Menus Module Menu Id  Fk',
            'ModuleSubMenusNavMenuName' => 'Module Sub Menus Nav Menu Name',
            'ModuleSubMenusPermissionName' => 'Module Sub Menus Permission Name',
            'ModuleSubMenusURL' => 'Module Sub Menus Url',
            'ModuleSubMenusComments' => 'Module Sub Menus Comments',
            'ModuleSubMenusActiveFlag' => 'Module Sub Menus Active Flag',
            'ModuleSubMenusParentID' => 'Module Sub Menus Parent ID',
            'ModuleSubMenusSortSeq' => 'Module Sub Menus Sort Seq',
            'ModuleSubMenusCreateDate' => 'Module Sub Menus Create Date',
            'ModuleSubMenusCreatedBy' => 'Module Sub Menus Created By',
            'ModuleSubMenusModifiedDate' => 'Module Sub Menus Modified Date',
            'ModuleSubMenusModifiedBy' => 'Module Sub Menus Modified By',
        ];
    }
	
	/**
     * @return \yii\db\ActiveQuery
     */
    public function getMenusModuleMenu()
    {
        return $this->hasOne(MenusModuleMenu::className(), ['ModuleMenuNavMenuID' => 'ModuleSubMenusModuleMenuID_FK']);
    }
}
