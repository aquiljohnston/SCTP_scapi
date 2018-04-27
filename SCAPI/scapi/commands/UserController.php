<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\SCUser;
use yii\db\Connection;
use yii\web\Response;

//Cmd yii user/create-admin
class UserController extends Controller
{
	/**
	*Sript built to insert a new user into a database without having to use an existing user
	*/
    public function actionCreateAdmin()
    {		
		BaseActiveRecord::setClient('apistage');
	
		//create response
		$response = Yii::$app->response;
		
		//options for bcrypt
		$options = [
			'cost' => 12,
		];
		
		$password = 'mdavis';
		$hashedPass = password_hash($password, PASSWORD_BCRYPT, $options);
		
		//build user data
		$userData = [
			'UserCreatedUID' => 'command generated',
			'UserCreatedDate' => BaseActiveController::getDate(),
			'UserComments' => 'Dev Admin',
			'UserActiveFlag' => 1,
			'UserFirstName' => 'Michael',
			'UserLastName' => 'Davis',
			'UserPassword' => $hashedPass,
			'UserEmployeeType' => 'Employee',
			'UserCompanyName' => 'Southern Cross',
			'UserCompanyPhone' => '770-40-1746',
			'UserName' => 'mdavis',
			'UserAppRoleType' => 'Admin',
			'UserPhone' => '706-340-8368',
		];
		
		$scUser = new SCUser();
		$scUser->attributes = $userData;
		
		if($scUser->save())
		{
			//assign rbac role
			// $auth = Yii::$app->authManager;
			// if($userRole = $auth->getRole($scUser["UserAppRoleType"]))
			// {
				// $auth->assign($userRole, $scUser["UserID"]);
			// }
			//TODO add user project relationship
		}
    }
}
