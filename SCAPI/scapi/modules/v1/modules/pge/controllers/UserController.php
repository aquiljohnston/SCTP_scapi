<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use app\modules\v1\modules\pge\models\PGEUser;
use app\modules\v1\modules\pge\models\Role;
use app\modules\v1\modules\pge\models\WebManagementUsers;
use app\modules\v1\modules\pge\models\ReportingGroupEmployeeRef;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\models\Project;
use app\modules\v1\models\Client;
use app\modules\v1\models\ProjectUser;
use app\modules\v1\models\Key;
use app\modules\v1\models\ActivityCode;
use app\modules\v1\models\Equipment;
use app\modules\v1\models\PayCode;
use app\modules\v1\models\AllTimeCardsCurrentWeek;
use app\modules\v1\models\AllMileageCardsCurrentWeek;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\controllers\DeleteMethodNotAllowed;
use app\modules\v1\controllers\PermissionsController;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Link;
use yii\db\mssql\PDO;
use yii\base\ErrorException;
use yii\db\Exception;


/**
 * UserController implements the routes for the User model.
 */
class UserController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\modules\pge\models\PGEUser'; 
	
	//options for bcrypt
	private $options = [
		'cost' => 12,
	];
	
	/**
	* sets verb filters for http request
	* @return an array of behaviors
	*/
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'create' => ['post'],
                    'delete' => ['delete'],
					'update' => ['put'],
					'view' => ['get'],
					'deactivate' => ['put'],
					'get-me'  => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	/**
	* unset parent actions
	*/
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
		
	use DeleteMethodNotAllowed;
	
	/**
	* Creates a new user record in the database and a corresponding key record
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/
	public function actionCreate()
	{
		SCUser::setClient('CometTracker');
		PermissionsController::requirePermission('userCreate');
		
		try
		{
			//create response
			$response = Yii::$app->response;
			
			//options for bcrypt
			$options = [
				'cost' => 12,
			];
			
			//read the post input (use this technique if you have no post variable name):
			$post = file_get_contents("php://input");
			//decode json post input as php array:
			$data = json_decode($post, true);
			
			//handle the password
			//get pass from data
			$securedPass = $data["UserPassword"];
			//decrypt password
			$decryptedPass = BaseActiveController::decrypt($securedPass);
			Yii::Trace("UserPassword ". $decryptedPass);
			//hash pass with bcrypt
			$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);

			SCUser::setClient('CometTracker');
			$scUser = new SCUser;
			$scUser->attributes = $data;
			$scUser->UserPassword = $hashedPass;
			
			PGEUser::setClient('pgedev');
			$pgeUser = new PGEUser;
			$pgeUser->attributes = $data;
			$pgeUser->UserPassword = $hashedPass;
			
			//handle app role type for sc
			$roleName = $pgeUser->UserAppRoleType;
			if ($roleName == 'Administrator')
			{
				$scUser->UserAppRoleType = 'Admin';
			}
			elseif($roleName == 'Supervisor' || $roleName == 'QM' || $roleName == 'BSS/Analyst')
			{
				$scUser->UserAppRoleType = 'Supervisor';
			}
			elseif($roleName == 'Surveyor/Inspector')
			{
				$scUser->UserAppRoleType = 'Technician';
			}
			
			$role = Role::find()
				->where(['RoleName'=>$roleName])
				->one();
			
			//rbac check if attempting to create an admin
			if($scUser["UserAppRoleType"] == 'Admin')
			{
				SCUser::setClient('CometTracker');
				PermissionsController::requirePermission('userCreateAdmin');
			}
			
			// UID of current user that is creating new user
			SCUser::setClient('CometTracker');
			$userCreatedUID = self::getUserFromToken()->UserUID;
			if(array_key_exists('Source', $data))
			{
				$userUID = BaseActiveController::generateUID('User', $data['Source']);
			}
			else
			{
				$response->data = "No Source Data";
				$response->setStatusCode(400);
				return $response;
			}
			
			$scUser->UserCreatedUID = $userCreatedUID;
			$scUser->UserCreatedDate = Parent::getDate();
			$scUser->UserUID = $userUID;
			
			$pgeUser->UserCreatedUID = $userCreatedUID;
			$pgeUser->UserCreatedDate = Parent::getDate();
			$pgeUser->UserUID = $userUID;
			
			$pgeUser->UserName = $pgeUser->UserLANID;
			$scUser->UserName = $pgeUser->UserLANID;
			
			PGEUser::setClient('pgedev');
			if($pgeUser-> save())
			{
				SCUser::setClient('CometTracker');
				if($scUser-> save())
				{
								
					//TODO add users to reporting groups/////////////////////////////////////
					$groups = $data['ReportingGroup'];
					
					foreach($groups as $g)
					{
						$newGroup = new ReportingGroupEmployeeRef;
						$newGroup->UserUID = $pgeUser->UserUID;
						$newGroup->ReportingGroupUID = $g;
						$newGroup->RoleUID = $role->RoleUID;
						$newGroup->CreatedUserUID = $userCreatedUID;
						$newGroup->CreateDatetime = Parent::getDate();
						$newGroup->Revision = 0;
						$newGroup->ActiveFlag = 1;
						$newGroup -> save();
					}
					
					//the project id of the pgedev project will need to change later
					$projectName = 'PG&E Dev';
					$project = Project::find()
						->where(['ProjectName' => $projectName])
						->one();
					$projectID = $project->ProjectID;
					$userID = $scUser->UserID;
					$scUser->link('projects', $project);
					try
					{
						$connection = SCUser::getDb();
						$transaction = $connection-> beginTransaction();
						$timeCardCommand = $connection->createCommand("EXECUTE PopulateTimeCardTbForUserToProjectCatchErrors_proc :PARAMETER1,:PARAMETER2");
						$timeCardCommand->bindParam(':PARAMETER1', $userID,  \PDO::PARAM_INT);
						$timeCardCommand->bindParam(':PARAMETER2', $projectID,  \PDO::PARAM_INT);
						$timeCardCommand->execute();
						$mileageCardCommand = $connection->createCommand("EXECUTE PopulateMileageCardTbForUserToProjectCatchErrors_proc :PARAMETER1,:PARAMETER2");
						$mileageCardCommand->bindParam(':PARAMETER1', $userID,  \PDO::PARAM_INT);
						$mileageCardCommand->bindParam(':PARAMETER2', $projectID,  \PDO::PARAM_INT);
						$mileageCardCommand->execute();
						$transaction->commit();
					}
					catch(Exception $e)
					{
						$transaction->rollBack();
						throw new \yii\web\HttpException(400);
					}
					
					//assign rbac role
					$auth = Yii::$app->authManager;
					if($userRole = $auth->getRole($scUser["UserAppRoleType"]))
					{
						$auth->assign($userRole, $scUser["UserID"]);
					}
					$response->setStatusCode(201);
					$pgeUser->UserPassword = '';
					$response->data = $pgeUser;
				}
				else
				{
					throw new \yii\web\HttpException(400);
				}
			}
			else
			{
				throw new \yii\web\HttpException(400);
			}
			return $response;
        }
		catch(ForbiddenHttpException $e)
		{
			throw new ForbiddenHttpException;
		}
		catch(Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	/**
	* Updates a user record in the database and a corresponding key record
	* @param $id the id of a user record
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/	
	public function actionUpdate($UID)
	{
		SCUser::setClient('CometTracker');
		PermissionsController::requirePermission('userUpdate');

		// try
		// {			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get user model to be updated
			SCUser::setClient('CometTracker');
			$scUser = SCUser::find()
				->where(['UserUID'=>$UID])
				->one();
			
			//get user model to be updated
			PGEUser::setClient('pgedev');
			$pgeUser = PGEUser::find()
				->where(['UserUID'=>$UID])
				->one();

			$currentRole = $scUser["UserAppRoleType"];

			SCUser::setClient('CometTracker');
			PermissionsController::requirePermission('userUpdate' . $currentRole);
			
			//options for bcrypt
			$options = [
				'cost' => 12,
			];

			//handle the password
			//get pass from data
			if(array_key_exists("UserPassword", $data))
			{
				//decrypt password
				$securedPass = $data["UserPassword"];
				$decryptedPass = BaseActiveController::decrypt($securedPass);
				
				//check if new password
				if($decryptedPass != "")
				{
					//hash pass with bcrypt
					$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
					
					$data["UserPassword"] = $hashedPass;
				}
				else
				{
					unset($data['UserPassword']);
				}
			}

			//pass new data to user
			$pgeUser->attributes = $data;
			$scUser->attributes = $data;
			// Get modified by from token
			$modifiedUID = self::getUserFromToken()->UserUID;
			$pgeUser->UserModifiedUID = $modifiedUID;
			$scUser->UserModifiedUID = $modifiedUID;
			//set modified dates
			$pgeUser->UserModifiedDate = Parent::getDate();
			$scUser->UserModifiedDate = Parent::getDate();
			//set user names
			$pgeUser->UserName = $pgeUser->UserLANID;
			$scUser->UserName = $pgeUser->UserLANID;
			
			//handle app role type for sc
			$roleName = $pgeUser->UserAppRoleType;
			if ($roleName == 'Administrator')
			{
				$scUser->UserAppRoleType = 'Admin';
			}
			elseif($roleName == 'Supervisor' || $roleName == 'QM' || $roleName == 'BSS/Analyst')
			{
				$scUser->UserAppRoleType = 'Supervisor';
			}
			elseif($roleName == 'Surveyor/Inspector')
			{
				$scUser->UserAppRoleType = 'Technician';
			}
			
			//rbac check if attempting to create an admin
			if($scUser["UserAppRoleType"] == 'Admin')
			{
				PermissionsController::requirePermission('userCreateAdmin');
			}
			
			Yii::Trace('PGE User ID' . $pgeUser->UserID);
			PGEUser::setClient('pgedev');
			if($pgeUser-> update())
			{
				SCUser::setClient('CometTracker');
				if($scUser-> update())
				{
					//handle potential role change
					$auth = Yii::$app->authManager;
					if($userRole = $auth->getRole($scUser["UserAppRoleType"]))
					{
						$auth->revokeAll($scUser["UserID"]);
						$auth->assign($userRole, $scUser["UserID"]);
					}
					$response->setStatusCode(201);
					$pgeUser->UserPassword = '';
					$response->data = $pgeUser; 
				}
			}
			else
			{
				throw new \yii\web\HttpException(400);
			}
			return $response;
		// }
		// catch(ForbiddenHttpException $e)
		// {
			// throw new ForbiddenHttpException;
		// }
		// catch(\Exception $e)
		// {
			// throw new \yii\web\HttpException(400);
		// }
	}
	
	/**
	* Gets the data for a user based on a user id
	* @param $id the id of a user record
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/	
	public function actionView($LANID)
	{
		try
		{
			//TODO permissions check
			//BaseActiveRecord::setClient('CometTracker');
			//PermissionsController::requirePermission('userView');	
			
			$headers = getallheaders();
			WebManagementUsers::setClient($headers['X-Client']);
		
			$user = WebManagementUsers::find()
				->Where(['UserLANID' => $LANID])
				->One();
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $user;
			
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	/**
	* Updates the active flag of a user to 0 for inactive
	* @param $userID id of the user record
	* @returns json body of user data
	* @throws \yii\web\HttpException
	*/
	public function actionDeactivate($userID)
	{
		PermissionsController::requirePermission('userDeactivate');
		
		try
		{
			//set db target
			$headers = getallheaders();
			PGEUser::setClient($headers['X-Client']);
			
			//get user to be deactivated
			$user = PGEUser::findOne($userID);
			
			$currentRole = $user["UserAppRoleType"];
			
			PermissionsController::requirePermission('userUpdate'.$currentRole);
			
			//pass new data to user model
			//$user->UserActiveFlag = 0;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//call stored procedure to for cascading deactivation of a user
			try
			{
				//deactivate PGEUser
			}
			catch(Exception $e)
			{
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
			return $response;
		}
		catch(ForbiddenHttpException $e)
		{
			throw new ForbiddenHttpException;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
		
	}
	
	/**
	* Gets a users data, the equipment assigned to them, and all projects that they are associated with
	* @param $userID 
	* @returns json body containing userdata, equipment, and projects 
	* @throws \yii\web\HttpException
	*/
	public function actionGetMe()
	{
		PermissionsController::requirePermission('userGetMe');
		
		try
		{
			//get stuff
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionGet($group = null, $type = null, $filter = null)
	{
		// try
		// {
			//TODO RBAC permissions check
			//BaseActiveRecord::setClient('CometTracker');
			//PermissionsController::requirePermission('userView');	
			
			//TODO check headers
			$headers = getallheaders();
			WebManagementUsers::setClient($headers['X-Client']);
			
			$userQuery = WebManagementUsers::find();
			
			if($group != null)
			{
				$userQuery->andWhere(['GroupName'=>$group]);
			}
			
			if($type != null)
			{
				$userQuery->andWhere(['UserEmployeeType'=>$type]);
			}
			
			if($filter != null)
			{
				$userQuery->andFilterWhere([
				'or',
				['like', 'GroupName', $filter],
				['like', 'Status', $filter],
				['like', 'LastName', $filter],
				['like', 'UserFirstName', $filter],
				['like', 'UserLANID', $filter],
				['like', 'UserEmployeeType', $filter],
				['like', 'OQ', $filter],
				['like', 'Role', $filter],
				]);
			}
			
			$users = $userQuery->all();
			
			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $users;
			return $response;
		// }
		// catch(ForbiddenHttpException $e)
		// {
		// throw new ForbiddenHttpException;
		// }
		// catch(\Exception $e)
		// {
		// throw new \yii\web\HttpException(400);
		// }
	}


}
