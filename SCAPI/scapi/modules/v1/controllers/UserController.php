<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\SCUser;
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
	public $modelClass = 'app\modules\v1\models\SCUser'; 
	
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
					'get-user-dropdowns'  => ['get'],
					'get-me'  => ['get'],
					'get-projects'  => ['get'],
					'get-active' => ['get'],
					'add-user-to-project' => ['post'],
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
	use GetAll;
	
	/**
	* Creates a new user record in the database and a corresponding key record
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/
	public function actionCreate()
	{		
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('userCreate');
			
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
			$securedPass = $data["UserKey"];
			
			//decrypt password
			$decryptedPass = BaseActiveController::decrypt($securedPass);
			
			//hash pass with bcrypt
			$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
			
			//begin a transaction to save all user related data
			$transaction = Yii::$app->db->beginTransaction();
			
			try{
				//create row in the db to hold the hashedPass
				$keyData = new Key();
				$keyData->Key1 = $hashedPass;
				$keyData-> save();
				
				//Replace the encoded pass with the ID for the new KeyTb row
				$data["UserKey"] = $keyData -> KeyID;
				
				//maps the data to a new user model and save
				$user = new SCUser();
				$user->attributes = $data;

				$userID = self::getUserFromToken()->UserID;
				$user->UserCreatedBy = $userID;


				//rbac check if attempting to create an admin
				if($user["UserAppRoleType"] == 'Admin')
				{
					PermissionsController::requirePermission('userCreateAdmin');
				}
				
				//created date
				$user->UserCreatedDate = Parent::getDate();

				if($user-> save())
				{
					//assign rbac role
					$auth = Yii::$app->authManager;
					if($userRole = $auth->getRole($user["UserAppRoleType"]))
					{
						$auth->assign($userRole, $user["UserID"]);
					}
					$response->setStatusCode(201);
					$response->data = $user;
				}
				else{
					throw new \yii\web\HttpException(400);
				}
				
				$transaction->commit();
			}
			catch(ForbiddenHttpException $e)
			{
				$transaction->rollBack();
				throw $e;
			}
			catch(Exception $e)
			{
				$transaction->rollBack();
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
	* Updates a user record in the database and a corresponding key record
	* @param $id the id of a user record
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/	
	public function actionUpdate($id)
	{
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('userUpdate');
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get user model to be updated
			$user = SCUser::findOne($id);

			$currentRole = $user["UserAppRoleType"];

			PermissionsController::requirePermission('userUpdate' . $currentRole);
			
			//options for bcrypt
			$options = [
				'cost' => 12,
			];
			
			//begin a transaction to save all user related data
			$transaction = Yii::$app->db->beginTransaction();
			
			try{
				//handle the password
				//get pass from data
				if(array_key_exists("UserKey", $data))
				{
					$securedPass = $data["UserKey"];
					
					//decrypt password
					$decryptedPass = BaseActiveController::decrypt($securedPass);
					
					//check if new password
					if($decryptedPass != $user->UserKey)
					{
						//hash pass with bcrypt
						$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
						
						//create row in the db to hold the hashedPass
						$keyData = Key::findOne($user->UserKey);
						$keyData->Key1 = $hashedPass;
						$keyData->KeyCreatedBy = self::getUserFromToken()->UserID;

						if($keyData-> update())
						{
							//Replace the encoded pass with the ID for the new KeyTb row
							$data["UserKey"] = $keyData -> KeyID;
						}
						else
						{
							throw new \yii\web\HttpException(400);
						}
					}
					else
					{
						$data["UserKey"] = $decryptedPass;
					}
				}

				//Don't let client change this attribute
				if(isset($data["UserCreatedBy"])) {
					unset($data["UserCreatedBy"]);
				}
				//pass new data to user
				$user->attributes = $data;
				// Get modified by from token
				$user->UserModifiedBy = self::getUserFromToken()->UserID;
				
				//rbac check if attempting to create an admin
				if($user["UserAppRoleType"] == 'Admin')
				{
					PermissionsController::requirePermission('userCreateAdmin');
				}
				
				$user->UserModifiedDate = Parent::getDate();
				
				if($user-> update())
				{
					//handle potential role change
					$auth = Yii::$app->authManager;
					if($userRole = $auth->getRole($user["UserAppRoleType"]))
					{
						$auth->revokeAll($user["UserID"]);
						$auth->assign($userRole, $user["UserID"]);
					}
					$response->setStatusCode(201);
					$response->data = $user; 
				}
				else{
					throw new \yii\web\HttpException(400);
				}
					
				$transaction->commit();
			}
			catch(ForbiddenHttpException $e)
			{
				throw new ForbiddenHttpException;
			}
			catch(Exception $e)
			{
				$transaction->rollBack();
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
	* Gets the data for a user based on a user id
	* @param $id the id of a user record
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/	
	public function actionView($id)
	{		
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('userView');
			
			//$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
			$user = SCUser::findOne($id);
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $user;
			
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
	
	use DeleteMethodNotAllowed;
	
	/**
	* Updates the active flag of a user to 0 for inactive
	* @param $userID id of the user record
	* @returns json body of user data
	* @throws \yii\web\HttpException
	*/
	public function actionDeactivate($userID)
	{
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('userDeactivate');
			
			//get user to be deactivated
			$user = SCUser::findOne($userID);
			
			$currentRole = $user["UserAppRoleType"];
			
			PermissionsController::requirePermission('userUpdate'.$currentRole);
			
			//pass new data to user model
			//$user->UserActiveFlag = 0;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//call stored procedure to for cascading deactivation of a user
			try
			{
				$connection = SCUser::getDb();
				$userDeactivateCommand = $connection->createCommand("EXECUTE SetUserInactive_proc :PARAMETER1");
				$userDeactivateCommand->bindParam(':PARAMETER1', $userID,  \PDO::PARAM_INT);
				$userDeactivateCommand->execute();
				$response->data = $user; 
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
	* Creates an associative array of user id/lastname, firstname pairs
	* @returns json body id name pairs
	* @throws \yii\web\HttpException
	*/
	public function actionGetUserDropdowns()
	{		
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('userGetDropdown');
		
			$users = SCUser::find()
				->where("UserActiveFlag = 1")
				->orderBy("UserLastName")
				->all();
			$namePairs = [null => "Unassigned"];
			$tempPairs = [];
			$userSize = count($users);
			
			for($i=0; $i < $userSize; $i++)
			{
				$tempPairs[$users[$i]->UserID]= $users[$i]->UserLastName. ", ". $users[$i]->UserFirstName;
			}
			$namePairs = $namePairs + $tempPairs;

			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $namePairs;
			
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
		
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('userGetMe');
			
			//get user id from auth token
			$userID = self::getUserFromToken()->UserID;
			
			//get user
			$user = SCUser::findOne($userID);
			
			$equipment = [];
			//get equipment for user
			$equipment = Equipment::find()
				->where("EquipmentAssignedUserID = $userID")
				->all();
			
			//get users realtionship to projects
			$projectUser = ProjectUser::find()
				->where("ProjUserUserID = $userID")
				->all();

			//get projects based on relationship
			$projectUserLength = count($projectUser);
			$projects  = [];
			for($i=0; $i < $projectUserLength; $i++)
			{
				//set current projectID
				$projectID = $projectUser[$i]->ProjUserProjectID ;
				
				//get time card for the current week for this project
				$timeCardModel = AllTimeCardsCurrentWeek::find()
					->where("UserID = $userID")
					->andWhere("TimeCardProjectID = $projectID")
					->One();
					
				//get time card for the current week for this project
				$mileageCardModel = AllMileageCardsCurrentWeek::find()
					->where("UserID = $userID")
					->andWhere("MileageCardProjectID = $projectID")
					->One();
				
				//get job codes for project, for now just getting all job codes
				$activityCodes = ActivityCode::find()
				->all();
				$activityCodesArray = array_map(function ($model) {return $model->attributes;},$activityCodes);
				$activityCodesLength = count($activityCodesArray);
				$payCodes = PayCode::find()
				->all();
				$payCodesArray = array_map(function ($model) {return $model->attributes;},$payCodes);
				for($j=0; $j < $activityCodesLength; $j++)
				{
					//get payroll code
					$activityCodesArray[$j]["PayrollCode"] = "TODO";
				}
				
				$projectModel = Project::findOne($projectID);
				$clientModel = Client::findOne($projectModel->ProjectClientID);
				$projectData["ProjectID"]= $projectModel->ProjectID;  
				$projectData["ProjectName"]= $projectModel->ProjectName;  
				$projectData["ProjectClientID"]= $projectModel->ProjectClientID;  
				$projectData["ProjectClientPath"]= $clientModel->ClientFilesPath;  
				$projectData["TimeCard"]= $timeCardModel; 
				$projectData["MileageCard"]= $mileageCardModel;				
				$projectData["ActivityCodes"]= $activityCodesArray; 
				$projectData["PayCodes"]= $payCodesArray; 
				
				$projects[] = $projectData;
			}
			
			//load data into array
			$dataArray = [];
			$dataArray["User"] = $user;
			$dataArray["Projects"] = $projects;
			$dataArray["Equipment"] = $equipment;
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $dataArray;
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
	
	/* Route getAllProjects
	* @Param userID
	* Client clientID
	* @Returns JSON of: Project Name, Project ID, Client ID
	* @throws \yii\web\HttpException
	*/
	public function actionGetProjects($userID)
	{
		// TODO: remove. Replaced by ProjectController::actionGetAll()
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('userGetProjects');
			
			//get users relationship to projects
			$projectUser = ProjectUser::find()
				->where("ProjUserUserID = $userID")
				->all();
				
			//get projects based on relationship
			$projectUserLength = count($projectUser);
			$projects  = [];
			for($i=0; $i < $projectUserLength; $i++)
			{
				$projectID = $projectUser[$i]->ProjUserProjectID ;
				$projectModel = Project::findOne($projectID);
				$projectData["ProjectID"]= $projectModel->ProjectID;  
				$projectData["ProjectName"]= $projectModel->ProjectName;  
				$projectData["ProjectClientID"]= $projectModel->ProjectClientID;  
				
				$projects[] = $projectData;
			}
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $projects;
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
	* Gets a users data for all users with an active flag of 1 for active
	* @returns json body of users 
	* @throws \yii\web\HttpException
	*/
	public function actionGetActive()
	{		
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
		
			PermissionsController::requirePermission('userGetActive');
			
			$users = SCUser::find()
				->where("UserActiveFlag = 1")
				->all();
				
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->setStatusCode(200);
			$response->data = $users;
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
}
