<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementUsers".
 *
 * @property string $GroupName
 * @property string $Status
 * @property string $LastName
 * @property string $UserFirstName
 * @property string $UserLANID
 * @property string $UserEmployeeType
 * @property string $OQ
 * @property string $Role
 */
class WebManagementUsers extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vWebManagementUsers';
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
            [['GroupName', 'Status', 'LastName', 'UserFirstName', 'UserLANID', 'UserEmployeeType', 'OQ', 'Rolef'], 'string'],
            [['Status', 'OQ'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'GroupName' => 'Group Name',
            'Status' => 'Status',
            'LastName' => 'Last Name',
            'UserFirstName' => 'User First Name',
            'UserLANID' => 'User Lanid',
            'UserEmployeeType' => 'User Employee Type',
            'OQ' => 'Oq',
			'Role' => 'Role',
        ];
    }
}
