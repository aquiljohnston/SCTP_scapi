<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "rRole".
 *
 * @property integer $rRoleID
 * @property string $RoleUID
 * @property integer $ProjectID
 * @property string $CreatedUserUID
 * @property string $ModifiedUserUID
 * @property string $CreateDTLT
 * @property string $ModifiedDTLT
 * @property string $Comments
 * @property integer $Revision
 * @property integer $ActiveFlag
 * @property string $RoleLevelType
 * @property string $RoleName
 * @property string $RoleDescription
 * @property integer $RoleSortSeq
 */
class Role extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rRole';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('pgeDevDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['RoleUID', 'CreatedUserUID', 'ModifiedUserUID', 'Comments', 'RoleLevelType', 'RoleName', 'RoleDescription'], 'string'],
            [['ProjectID', 'Revision', 'ActiveFlag', 'RoleSortSeq'], 'integer'],
            [['CreateDTLT', 'ModifiedDTLT'], 'safe'],
            [['Revision', 'ActiveFlag'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rRoleID' => 'R Role ID',
            'RoleUID' => 'Role Uid',
            'ProjectID' => 'Project ID',
            'CreatedUserUID' => 'Created User Uid',
            'ModifiedUserUID' => 'Modified User Uid',
            'CreateDTLT' => 'Create Dtlt',
            'ModifiedDTLT' => 'Modified Dtlt',
            'Comments' => 'Comments',
            'Revision' => 'Revision',
            'ActiveFlag' => 'Active Flag',
            'RoleLevelType' => 'Role Level Type',
            'RoleName' => 'Role Name',
            'RoleDescription' => 'Role Description',
            'RoleSortSeq' => 'Role Sort Seq',
        ];
    }
}
