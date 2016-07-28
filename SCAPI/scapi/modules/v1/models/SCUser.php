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
 * @property string $UserKey
 * @property integer $UserActiveFlag
 * @property string $UserArchiveFlag
 * @property string $UserCreatedDate
 * @property string $UserModifiedDate
 * @property integer $UserCreatedBy
 * @property integer $UserModifiedBy
 * @property string $UserCreateDTLTOffset
 * @property integer $UserModifiedDTLTOffset
 * @property integer $UserInactiveDTLTOffset
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
            [['UserName', 'UserFirstName', 'UserLastName', 'UserEmployeeType', 'UserPhone', 'UserCompanyName', 'UserCompanyPhone', 'UserAppRoleType', 'UserComments', 'UserArchiveFlag'], 'string'],
            [['UserKey', 'UserActiveFlag', 'UserCreatedBy', 'UserModifiedBy'], 'integer'],
            [['UserCreateDTLTOffset', 'UserModifiedDTLTOffset', 'UserInactiveDTLTOffset', 'UserCreatedDate', 'UserModifiedDate'], 'safe']
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
            'UserKey' => 'User Key',
            'UserActiveFlag' => 'User Active Flag',
			'UserArchiveFlag' => 'User Archive Flag',
            'UserCreatedDate' => 'User Created Date',
            'UserModifiedDate' => 'User Modified Date',
            'UserCreatedBy' => 'User Created By',
            'UserModifiedBy' => 'User Modified By',
            'UserCreateDTLTOffset' => 'User Create Dtltoffset',
            'UserModifiedDTLTOffset' => 'User Modified Dtltoffset',
            'UserInactiveDTLTOffset' => 'User Inactive Dtltoffset',
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
	
	// /**
     // * @return \yii\db\ActiveQuery
     // */
    // public function getProjects()
    // {
        // return $this->hasMany(ProjectTb::className(), ['ProjectID' => 'ProjUserProjectID'])
			// ->viaTable('projectUserTbs', ['ProjUserUserID' => 'UserID']);
    // }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserKey()
    {
        return $this->hasOne(KeyTb::className(), ['KeyID' => 'UserKey']);
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
		$userID = Auth::find()
			->where(['AuthToken' => $token])
			->one();
        return static::findOne(['UserID' => $userID->AuthUserID]);
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
