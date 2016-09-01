<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "vWebManagementUsers".
 *
 * @property string $GroupName
 * @property string $Status
 * @property string $LastName
 * @property string $FullName
 * @property string $UserFirstName
 * @property string $UserLANID
 * @property string $UserEmployeeType
 * @property string $OQ
 * @property string $Role
 * @property string $UserUID
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['GroupName', 'Status', 'LastName', 'UserFirstName', 'UserLANID', 'UserEmployeeType', 'OQ', 'Role', 'UserUID', 'FullName'], 'string'],
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
			'FullName' => 'Full Name',
            'UserLANID' => 'User Lanid',
            'UserEmployeeType' => 'User Employee Type',
            'OQ' => 'Oq',
			'Role' => 'Role',
			'UserUID' => 'User UID',
        ];
    }
}
