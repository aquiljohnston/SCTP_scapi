<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\SCUser;
use app\modules\v1\modules\pge\models\PGEUser;
use yii\db\Connection;
use yii\web\Response;

/**
* This Class establishes the rules of the RBAC system for the API
* Permissions are created and assigned and the role hierarchy is established
*/
class UserController extends Controller
{
	/**
	*Sript built to insert a new user into a database without having to use an existing user
	*/
    public function actionCreatePGEAdmin()
    {		
		//create response
		$response = Yii::$app->response;
		
		//options for bcrypt
		$options = [
			'cost' => 12,
		];
		
		$password = 'pgeAdmin';
		$hashedPass = password_hash($password, PASSWORD_BCRYPT, $options);
		
		//build user data
		$userData = [
			'UserUID' => BaseActiveController::generateUID('User', 'WEB'),
			'UserCreatedUID' => 'command generated',
			'UserCreatedDate' => BaseActiveController::getDate(),
			'UserComments' => 'Generic Admin',
			'UserActiveFlag' => 1,
			'UserArchiveFlag' => 1,
			'UserInActiveFlag' => 0,
			'UserLoginID' => 'pgeAdmin',
			'UserFirstName' => 'Admin',
			'UserLastName' => 'Istrator',
			'UserLANID' => 'pgeAdmin',
			'UserPassword' => $hashedPass,
			'UserEmployeeType' => 'Employee',
			'UserCompanyName' => 'Southern Cross',
			'UserCompanyPhone' => '444-555-6666',
			'UserSupervisorUserUID' => '',
			'UserName' => 'pgeAdmin',
			'UserAppRoleType' => 'Admin',
			'UserPhone' => '111-222-3333',
			'UserCreateDTLTOffset' => '',
			'UserModifiedDTLTOffset' => null,
			'UserInactiveDTLTOffset' => null,
			'UserModifiedDate' => null,
		];
		
		$scUser = new SCUser();
		$scUser->attributes = $userData;
		
		$pgeUser = new pgeUser();
		$pgeUser->attributes = $userData;
		
		SCUser::setClient('CometTracker');
		if($scUser->save())
		{
			PGEUser::setClient('pgedev');
			$pgeUser->save();
			//assign rbac role
			$auth = Yii::$app->authManager;
			if($userRole = $auth->getRole($scUser["UserAppRoleType"]))
			{
				$auth->assign($userRole, $scUser["UserID"]);
			}
		}
    }
}
