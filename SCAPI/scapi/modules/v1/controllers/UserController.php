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
		PermissionsController::requirePermission('userCreate');
		
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Key::setClient($headers['X-Client']);
			
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
		PermissionsController::requirePermission('userUpdate');

		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Key::setClient($headers['X-Client']);
			
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
		PermissionsController::requirePermission('userView');
		
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			
			//$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
			$user = SCUser::findOne($id);
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
	
	use DeleteMethodNotAllowed;
	
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
			SCUser::setClient($headers['X-Client']);
			
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
		PermissionsController::requirePermission('userGetDropdown');
	
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
		
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
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Project::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			Equipment::setClient($headers['X-Client']);
			ActivityCode::setClient($headers['X-Client']);
			PayCode::setClient($headers['X-Client']);
			Client::setClient($headers['X-Client']);
			AllTimeCardsCurrentWeek::setClient($headers['X-Client']);
			AllMileageCardsCurrentWeek::setClient($headers['X-Client']);
			
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
		PermissionsController::requirePermission('userGetProjects');
		// TODO: remove. Replaced by ProjectController::actionGetAll()
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Project::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
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
		PermissionsController::requirePermission('userGetActive');
		
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
		
			$users = SCUser::find()
				->where("UserActiveFlag = 1")
				->all();
				
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->setStatusCode(200);
			$response->data = $users;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}


	public function actionGet($division = null, $workCenter = null, $type = null, $filter = null)
	{
		//TODO RBAC permissions check
		try
		{
			//TODO check headers

			//stub data
			$users = [];
			//build stub users
			$andre = [];
			$andre["ID"] = 1;
			$andre["Division"] = "Diablo";
			$andre["WorkCenter"] = "Izual";
			$andre["Status"] = "Active";
			$andre["LastName"] = "Vicente";
			$andre["FirstName"] = "Andre";
			$andre["LANID"] = "A6V9";
			$andre["Type"] = "Contractor";
			$andre["OQ"] = "Lapsed";
			$josh = [];
			$josh["ID"] = 2;
			$josh["Division"] = "Belial";
			$josh["WorkCenter"] = "Zoltun Kulle";
			$josh["Status"] = "Active";
			$josh["LastName"] = "Josh";
			$josh["FirstName"] = "Patton";
			$josh["LANID"] = "J0P0";
			$josh["Type"] = "Intern";
			$josh["OQ"] = "Lapsed";
			$michael = [];
			$michael["ID"] = 3;
			$michael["Division"] = "Malthael";
			$michael["WorkCenter"] = array(
				"Adria",
				"Urzael",
			);
			$michael["Status"] = "Active";
			$michael["LastName"] = "Davis";
			$michael["FirstName"] = "Michael";
			$michael["LANID"] = "M3D4";
			$michael["Type"] = "Employee";
			$michael["OQ"] = "Current";
			$tao = [];
			$tao["ID"] = 4;
			$tao["Division"] = "Azmodan";
			$tao["WorkCenter"] = "Cydaea";
			$tao["Status"] = "Active";
			$tao["LastName"] = "Tao";
			$tao["FirstName"] = "Zhang";
			$tao["LANID"] = "T1Z3";
			$tao["Type"] = "Employee";
			$tao["OQ"] = "Lapsed";

			$sarah = [];
			$sarah["ID"] = 5;
			$sarah["Division"] = "Azmodan";
			$sarah["WorkCenter"] = "Cydaea";
			$sarah["Status"] = "Active";
			$sarah["LastName"] = "Smith";
			$sarah["FirstName"] = "Sarah";
			$sarah["LANID"] = "S1S2";
			$sarah["Type"] = "Employee";
			$sarah["OQ"] = "Lapsed";

			$bob = [];
			$bob["ID"] = 6;
			$bob["Division"] = "Azmodan";
			$bob["WorkCenter"] = "Cydaea";
			$bob["Status"] = "Active";
			$bob["LastName"] = "Westerly";
			$bob["FirstName"] = "Bob";
			$bob["LANID"] = "B1W2";
			$bob["Type"] = "Contractor";
			$bob["OQ"] = "Lapsed";

			$jennifer = [];
			$jennifer["ID"] = 7;
			$jennifer["Division"] = "Azmodan";
			$jennifer["WorkCenter"] = "Cydaea";
			$jennifer["Status"] = "Active";
			$jennifer["LastName"] = "Garrett";
			$jennifer["FirstName"] = "Jennifer";
			$jennifer["LANID"] = "B1W2";
			$jennifer["Type"] = "Contractor";
			$jennifer["OQ"] = "Lapsed";
			
			$users[] = $andre;
			$users[] = $josh;
			$users[] = $michael;
			$users[] = $tao;
			$users[] = $sarah;
			$users[] = $jennifer;
			
			$userCount = count($users);
			$userData = [];
			function workCenterSearch($filter, $workCenter) {
				if (is_array($workCenter)) {
					$workCenterCount = count($workCenter);
					for ($j = 0; $j < $workCenterCount; $j++) {
						if (stripos($workCenter[$j], $filter) !== false) {
							return true;
						}
					}
				} elseif (stripos($workCenter, $filter) !== false) {
					return true;
				}
				return false;
			}
			//loop to filter users
			for ($i = 0; $i < $userCount; $i++) {
				if ($filter == null || stripos($users[$i]["Division"], $filter) !== false
					|| stripos($users[$i]["Status"], $filter) !== false || stripos($users[$i]["LastName"], $filter) !== false
					|| stripos($users[$i]["FirstName"], $filter) !== false || stripos($users[$i]["LANID"], $filter) !== false
					|| stripos($users[$i]["Type"], $filter) !== false || stripos($users[$i]["OQ"], $filter) !== false
					|| workCenterSearch($filter, $users[$i]["WorkCenter"])
				) {
					if ($division == null || $division == $users[$i]["Division"]) {
						if ($type == null || $type == $users[$i]["Type"]) {
							if (is_array($users[$i]["WorkCenter"])) {
								$workCenterCount = count($users[$i]["WorkCenter"]);
								for ($j = 0; $j < $workCenterCount; $j++) {
									if ($workCenter == null || $workCenter == $users[$i]["WorkCenter"][$j]) {
										$users[$i]["WorkCenter"] = "Many";
										$userData[] = $users[$i];
										break;
									}
								}
							} elseif ($workCenter == null || $workCenter == $users[$i]["WorkCenter"]) {
								$userData[] = $users[$i];
							}
						}
					}
				}
			}

			//loop to handle many work centers

			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $userData;
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


}
