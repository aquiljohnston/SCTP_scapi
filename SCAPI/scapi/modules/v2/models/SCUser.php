<?php

namespace app\modules\v2\models;

use Yii;
use yii\web\IdentityInterface;
use app\modules\v2\models\Auth;

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
 * @property string $UserCreatedDTLTOffset
 * @property string $UserModifiedDTLTOffset
 * @property string $UserInactiveDTLTOffset
 * @property string $UserUID
 * @property string $UserPassword
 * @property integer $UserPaySourceID
 * @property string $UserOasisID
 * @property integer $SCCEmployeeID
 * @property string $UserAddress 
 * @property string $UserCity 
 * @property string $UserState 
 * @property string $UserZip 
 * @property string $UserLocation 
 * @property string $UserPayMethod 
 * @property string $UserPreferredEmail 
 * @property string $UserRefreshDateTime 
 *
 * @property EquipmentTb[] $equipmentTbs
 * @property ProjectUserTb[] $projectUserTbs
 * @property TTaskOut[] $tTaskOuts
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
			'UserPassword', 'UserUID', 'UserOasisID', 'UserAddress', 'UserCity', 'UserState', 'UserZip', 'UserLocation', 'UserPayMethod', 'UserPreferredEmail', 'UserRefreshDateTime'], 'string'],
            [['UserActiveFlag', 'UserArchiveFlag', 'UserPaySourceID', 'SCCEmployeeID'], 'integer'],
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
			'UserPaySourceID' => 'User Pay Source ID',
			'UserOasisID' => 'User Oasis ID',
			'SCCEmployeeID' => 'SCC Employee ID',
			'UserAddress' => 'User Address',
			'UserCity' => 'User City',
			'UserState' => 'User State',
			'UserZip' => 'User Zip',
			'UserLocation' => 'User Location',
			'UserPayMethod' => 'User Pay Method',
			'UserPreferredEmail' => 'User Preferred Email',
			'UserRefreshDateTime' => 'User Refresh Date Time',
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
     * @return \yii\db\ActiveQuery
	 * probably need TODO set this up for the join table
     */
	// public function getTTaskOuts()
   // {
	   // return $this->hasMany(TTaskOut::className(), ['CreatedUserID' => 'UserID']);
   // }
	
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
