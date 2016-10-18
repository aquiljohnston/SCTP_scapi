<?php

namespace app\modules\v1\modules\pge\models;

use Yii;

/**
 * This is the model class for table "UserTb".
 *
 * @property integer $UserID
 * @property string $UserUID
 * @property integer $ProjectID
 * @property string $UserCreatedUID
 * @property string $UserModifiedUID
 * @property string $UserCreatedDate
 * @property string $UserModifiedDate
 * @property string $UserInactiveDTLT
 * @property string $UserComments
 * @property integer $UserRevision
 * @property integer $UserActiveFlag
 * @property integer $UserInActiveFlag
 * @property string $UserLoginID
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $UserLANID
 * @property string $UserPassword
 * @property string $UserEmployeeType
 * @property string $UserCompanyName
 * @property string $UserCompanyPhone
 * @property string $UserSupervisorUserUID
 * @property string $UserName
 * @property string $UserAppRoleType
 * @property string $UserPhone
 * @property string $UserCreatedDTLTOffset
 * @property string $UserModifiedDTLTOffset
 * @property string $UserInactiveDTLTOffset
 * @property integer $UserArchiveFlag
 * @property string $HomeWorkCenterUID
 */
class PGEUser extends \app\modules\v1\models\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'UserTb';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserUID', 'UserCreatedUID', 'UserModifiedUID', 'UserComments', 'UserLoginID', 'UserFirstName', 'UserLastName', 'UserLANID', 'UserPassword', 'UserEmployeeType', 'UserCompanyName', 'UserCompanyPhone', 'UserSupervisorUserUID', 'UserName', 'UserAppRoleType', 'UserPhone', 'HomeWorkCenterUID'], 'string'],
            [['ProjectID', 'UserRevision', 'UserActiveFlag', 'UserInActiveFlag', 'UserArchiveFlag'], 'integer'],
            [['UserCreatedDate', 'UserModifiedDate', 'UserInactiveDTLT', 'UserCreatedDTLTOffset', 'UserModifiedDTLTOffset', 'UserInactiveDTLTOffset'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'UserUID' => 'User Uid',
            'ProjectID' => 'Project ID',
            'UserCreatedUID' => 'User Created Uid',
            'UserModifiedUID' => 'User Modified Uid',
            'UserCreatedDate' => 'User Created Date',
            'UserModifiedDate' => 'User Modified Date',
            'UserInactiveDTLT' => 'User Inactive Dtlt',
            'UserComments' => 'User Comments',
            'UserRevision' => 'User Revision',
            'UserActiveFlag' => 'User Active Flag',
            'UserInActiveFlag' => 'User In Active Flag',
            'UserLoginID' => 'User Login ID',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'UserLANID' => 'User Lanid',
            'UserPassword' => 'User Password',
            'UserEmployeeType' => 'User Employee Type',
            'UserCompanyName' => 'User Company Name',
            'UserCompanyPhone' => 'User Company Phone',
            'UserSupervisorUserUID' => 'User Supervisor User Uid',
            'UserName' => 'User Name',
            'UserAppRoleType' => 'User App Role Type',
            'UserPhone' => 'User Phone',
            'UserCreatedDTLTOffset' => 'User Created Dtltoffset',
            'UserModifiedDTLTOffset' => 'User Modified Dtltoffset',
            'UserInactiveDTLTOffset' => 'User Inactive Dtltoffset',
            'UserArchiveFlag' => 'User Archive Flag',
            'HomeWorkCenterUID' => 'Home Work Center Uid',
        ];
    }
}
