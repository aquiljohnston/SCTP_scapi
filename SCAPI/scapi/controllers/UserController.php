<?php

namespace app\controllers;

use Yii;
use app\models\SCUser;
use app\models\Project;
use app\models\Client;
use app\models\ProjectUser;
use app\models\Key;
use app\models\ActivityCode;
use app\models\Equipment;
use app\models\PayCode;
use app\models\AllTimeCardsCurrentWeek;
use app\models\AllMileageCardsCurrentWeek;
use app\controllers\BaseActiveController;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
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
	public $modelClass = 'app\models\SCUser'; 
	
	/**
	* sets verb filters for http request
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
					'get-all-projects'  => ['get'],
					'get-all-active-users' => ['get'],
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
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Key::setClient($headers['X-Client']);
			
			//create response
			$response = Yii::$app->response;
			
			//iv and key for openssl
			$iv = "abcdefghijklmnop";
			$sKey ="sparusholdings12";
		
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
			//decode the base 64 encoding
			$decodedPass = base64_decode($securedPass);
			//decrypt with openssl using the key and iv
			$decryptedPass = openssl_decrypt($decodedPass,  'AES-128-CBC', $sKey, OPENSSL_RAW_DATA, $iv);
			Yii::trace('decryptedPass: '.$decryptedPass);
			//hash pass with bcrypt
			$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
			
			//create row in the db to hold the hashedPass
			$keyData = new Key();
			$keyData->Key1 = $hashedPass;
			$keyData-> save();
			
			//Replace the encoded pass with the ID for the new KeyTb row
			$data["UserKey"] = $keyData -> KeyID;
			
			//maps the data to a new user model and save
			$model = new SCUser();
			$model->attributes = $data;  
			
			//created date
			$model->UserCreatedDate = Parent::getDate();
			
			if($model-> save())
			{
				$response->setStatusCode(201);
				$response->data = $model;
			}
			else
			{
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
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/	
	public function actionUpdate($id)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Key::setClient($headers['X-Client']);
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//get model to be updated
			$model = SCUser::findOne($id);
			
			//iv and key for openssl
			$iv = "abcdefghijklmnop";
			$sKey ="sparusholdings12";
			
			//options for bcrypt
			$options = [
				'cost' => 12,
			];
			//handle the password
			//get pass from data
			if(array_key_exists("UserKey", $data))
			{
				$securedPass = $data["UserKey"];
				//decode the base 64 encoding
				$decodedPass = base64_decode($securedPass);
				//decrypt with openssl using the key and iv
				$decryptedPass = openssl_decrypt($decodedPass,  'AES-128-CBC', $sKey, OPENSSL_RAW_DATA, $iv);
				
				//check if new passowrd
				if($decryptedPass != $model->UserKey)
				{
					//hash pass with bcrypt
					$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
					
					//create row in the db to hold the hashedPass
					$keyData = Key::findOne($model->UserKey);
					$keyData->Key1 = $hashedPass;
					if(array_key_exists("UserCreatedBy", $data))
					{
						$keyData->KeyCreatedBy = $data["UserCreatedBy"];
					}
					else
					{
						$response->setStatusCode(400);
						$response->data = "Http:400 Bad Request";
					}
					if($keyData-> update())
					{
						//Replace the encoded pass with the ID for the new KeyTb row
						$data["UserKey"] = $keyData -> KeyID;
					}
					else
					{
						$response->setStatusCode(400);
						$response->data = "Http:400 Bad Request";
					}
				}
				else
				{
					$data["UserKey"] = $decryptedPass;
				}
			}
			
			//pass new data to model
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->UserModifiedDate = Parent::getDate();
			
			if($model-> update())
			{
				$response->setStatusCode(201);
				$response->data = $model; 
			}
			else
			{
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
	
	/**
	* The delete method is not allowed for user so the parent function is overridden to reflect that
	* @returns json body method not allowed message
	*/	
	public function actionDelete()
	{
		$response = Yii::$app->response;
		$response ->format = Response::FORMAT_JSON;
		$response->data = "Method Not Allowed";
		$response->setStatusCode(405);
		return $response;
	}
	
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
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			
			//get user to be deactivated
			$model = SCUser::findOne($userID);
			
			//pass new data to model
			$model->UserActiveFlag = 0;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->UserModifiedDate = Parent::getDate();
			
			if($model-> update())
			{
				$response->setStatusCode(200);
				$response->data = $model; 
			}
			else
			{
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
	* Creates an associative array of user id/lastname, firstname pairs
	* @returns json body id name pairs
	* @throws \yii\web\HttpException
	*/
	public function actionGetUserDropdowns()
	{	
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
		
			$users = SCUser::find()
				->all();
			$namePairs = [null => "Unassigned"];
			$tempPairs = [];
			$userSize = count($users);
			
			for($i=0; $i < $userSize; $i++)
			{
				$tempPairs[$users[$i]->UserID]= $users[$i]->UserLastName. ", ". $users[$i]->UserFirstName;
			}
			natcasesort($tempPairs);
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
	public function actionGetMe($userID)
	{
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
	public function actionGetAllProjects($userID)
	{
		try
		{
			//set db target
			$headers = getallheaders();
			SCUser::setClient($headers['X-Client']);
			Project::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);
			
			//get users realtionship to projects
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
	public function actionGetAllActiveUsers()
	{
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

}
