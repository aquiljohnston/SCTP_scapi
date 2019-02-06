<?php

namespace app\modules\v2\models;

use Yii;
use yii\web\IdentityInterface;
use app\modules\v2\models\Auth;
use yii\web\UnauthorizedHttpException;

/**
 * This is the model class for table "UserTb".
 *
 * @property int $UserID
 * @property string $UserName
 * @property string $UserFirstName
 * @property string $UserLastName
 * @property string $UserEmployeeType
 * @property string $UserPhone
 * @property string $UserCompanyName
 * @property string $UserCompanyPhone
 * @property string $UserAppRoleType
 * @property string $UserComments
 * @property int $UserActiveFlag 0 = inactive 1 = active and 9 
 * @property string $UserCreatedDTLTOffset
 * @property string $UserModifiedDTLTOffset
 * @property string $UserCreatedDate
 * @property string $UserCreatedUID
 * @property string $UserModifiedDate
 * @property string $UserModifiedUID
 * @property string $UserUID
 * @property string $UserPassword
 * @property int $UserPaySourceID
 * @property string $UserOasisID
 * @property int $SCCEmployeeID
 * @property string $UserAddress
 * @property string $UserCity
 * @property string $UserState
 * @property string $UserZip
 * @property string $UserLocation
 * @property string $UserPayMethod
 * @property string $UserPreferredEmail
 * @property string $UserRefreshDateTime
 * @property string $UserInactiveDTLTOffset
 * @property string $UserQuickBooksID
 * @property string $UserADPID
 * @property int $hasPersonalVehicle
 *
 * @property EquipmentTb[] $equipmentTbs
 * @property ProjectUserTb[] $projectUserTbs
 * @property ProjectTb[] $projUserProjects
 * @property TTaskOut[] $tTaskOuts
 */
class SCUser extends BaseActiveRecord  implements IdentityInterface
{	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UserTb';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UserName', 'UserFirstName', 'UserLastName', 'UserEmployeeType', 'UserPhone', 'UserCompanyName', 'UserCompanyPhone', 'UserAppRoleType', 'UserComments', 'UserCreatedUID', 'UserModifiedUID', 'UserUID', 'UserPassword', 'UserOasisID', 'UserAddress', 'UserCity', 'UserState', 'UserZip', 'UserLocation', 'UserPayMethod', 'UserPreferredEmail', 'UserQuickBooksID', 'UserADPID'], 'string'],
            [['UserActiveFlag', 'UserPaySourceID', 'SCCEmployeeID', 'hasPersonalVehicle'], 'integer'],
            [['UserCreatedDTLTOffset', 'UserModifiedDTLTOffset', 'UserCreatedDate', 'UserModifiedDate', 'UserRefreshDateTime', 'UserInactiveDTLTOffset'], 'safe'],
            [['UserName'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
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
            'UserCreatedDTLTOffset' => 'User Created Dtltoffset',
            'UserModifiedDTLTOffset' => 'User Modified Dtltoffset',
            'UserCreatedDate' => 'User Created Date',
            'UserCreatedUID' => 'User Created Uid',
            'UserModifiedDate' => 'User Modified Date',
            'UserModifiedUID' => 'User Modified Uid',
            'UserUID' => 'User Uid',
            'UserPassword' => 'User Password',
            'UserPaySourceID' => 'User Pay Source ID',
            'UserOasisID' => 'User Oasis ID',
            'SCCEmployeeID' => 'Sccemployee ID',
            'UserAddress' => 'User Address',
            'UserCity' => 'User City',
            'UserState' => 'User State',
            'UserZip' => 'User Zip',
            'UserLocation' => 'User Location',
            'UserPayMethod' => 'User Pay Method',
            'UserPreferredEmail' => 'User Preferred Email',
            'UserRefreshDateTime' => 'User Refresh Date Time',
            'UserInactiveDTLTOffset' => 'User Inactive Dtltoffset',
            'UserQuickBooksID' => 'User Quick Books ID',
            'UserADPID' => 'User Adpid',
            'hasPersonalVehicle' => 'Has Personal Vehicle',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentTbs()
    {
        return $this->hasMany(EquipmentTb::className(), ['EquipmentAssignedUserName' => 'UserName']);
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
			throw new UnauthorizedHttpException;
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
