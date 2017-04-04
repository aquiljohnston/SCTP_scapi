<?php

namespace app\modules\v1\models;

use Yii;
use yii\web\IdentityInterface;
use app\modules\v1\models\Auth;

/**
 * This is the model class for table "UserTb".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $UserEmployeeType
 * @property string $UserPhone
 * @property string $UserCompanyName
 * @property string $UserCompanyPhone
 * @property string $UserAppRoleType
 * @property string $UserComments
 * @property integer $UserActiveFlag
 * @property integer $UserArchiveFlag
 * @property string $UserCreatedDate
 * @property string $UserModifiedDate
 * @property string $UserCreatedUID
 * @property string $UserModifiedUID
 * @property string $UserCreateDTLTOffset
 * @property string $UserModifiedDTLTOffset
 * @property string $UserInactiveDTLTOffset
 * @property string $UserUID
 * @property string $UserPassword
 *
 * @property EquipmentTb[] $equipmentTbs
 * @property ProjectUserTb[] $projectUserTbs
 * @property KeyTb $userKey
 */
class SCUser extends BaseActiveRecord  implements IdentityInterface
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
            [['UserName', 'UserFirstName', 'UserLastName', 'UserEmployeeType', 'UserPhone', 'UserCompanyName', 'UserCompanyPhone', 'UserAppRoleType', 'UserComments',
			'UserPassword', 'UserUID'], 'string'],
            [['UserActiveFlag', 'UserArchiveFlag'], 'integer'],
            [['UserCreatedDTLTOffset', 'UserModifiedDTLTOffset', 'UserInactiveDTLTOffset', 'UserCreatedDate', 'UserModifiedDate', 'UserCreatedUID', 'UserModifiedUID'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'UserFirstName' => 'User First Name',
            'UserLastName' => 'User Last Name',
            'UserEmployeeType' => 'User Employee Type',
            'UserPhone' => 'User Phone',
            'UserCompanyName' => 'User Company Name',
            'UserCompanyPhone' => 'User Company Phone',
            'UserAppRoleType' => 'User App Role Type',
            'UserComments' => 'User Comments',
            'UserActiveFlag' => 'User Active Flag',
			'UserArchiveFlag' => 'User Archive Flag',
            'UserCreatedDate' => 'User Created Date',
            'UserModifiedDate' => 'User Modified Date',
            'UserCreatedUID' => 'User Created UID',
            'UserModifiedUID' => 'User Modified UID',
            'UserCreatedDTLTOffset' => 'User Created Dtltoffset',
            'UserModifiedDTLTOffset' => 'User Modified Dtltoffset',
            'UserInactiveDTLTOffset' => 'User Inactive Dtltoffset',
			'UserUID' => 'User UID',
			'UserPassword' => 'User Password',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentTbs()
    {
        return $this->hasMany(EquipmentTb::className(), ['EquipmentAssignedUserID' => 'UserID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectUserTbs()
    {
        return $this->hasMany(ProjectUser::className(), ['ProjUserUserID' => 'UserID']);
    }

	/**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::className(), ['ProjectID' => 'ProjUserProjectID'])
			->via('projectUserTbs');
    }
	
	/**
     * Finds an identity by the given ID.
     *
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
		$auth = Auth::find()
			->where(['AuthToken' => $token])
			->one();
		
		//handle if auth record does not exist
		if ($auth == null)
		{
			return null;
		}
		
        return static::findOne(['UserID' => $auth->AuthUserID]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->UserID;
    }

    /**
     * @return string current user auth key
     */
	//todo change to work with auth table
    public function getAuthKey()
    {
		//$authToken = Auth::findOne(['auth_token' => $this->UserID]);
        //return $authToken;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
