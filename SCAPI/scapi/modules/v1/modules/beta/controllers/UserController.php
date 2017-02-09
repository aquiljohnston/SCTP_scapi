<?php

namespace app\modules\v1\modules\beta\controllers;

use Yii;
use app\rbac\BetaDbManager;
use app\authentication\TokenAuth;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\Project;
use app\modules\v1\models\Client;
use app\modules\v1\models\ProjectUser;
use app\modules\v1\models\BaseUser;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\controllers\DeleteMethodNotAllowed;
use app\modules\v1\modules\beta\controllers\BetaPermissionsController;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
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
	public $modelClass = 'app\modules\v1\models\BaseUser'; 
	
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
		$behaviors['authenticator'] =
		[
			'class' => TokenAuth::className(),
			'except' => ['reset-password'],
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'create' => ['post'],
					'update' => ['put']
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
	* Creates a new user record in the proejct database and the base comet tracker base
	* @returns json body of the user data
	* @throws \yii\web\HttpException
	*/
	public function actionCreate()
	{		
		try
		{
			//get headers
			$headers = getallheaders();
			BaseUser::setClient($headers['X-Client']);
			BetaPermissionsController::requirePermission('userCreate', $headers['X-Client']);
			
			//read the post input
			$post = file_get_contents("php://input");
			//decode json post input as php array:
			$data = json_decode($post, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//check if a user exist with the desired username
			SCUser::setClient(BaseActiveController::urlPrefix());
			$existingUser = SCUser::find()
				->where(['UserName' =>  $data['UserName']])
				->all();
				
			if($existingUser != null)
			{
				$response->setStatusCode(400);
				$response->data = 'UserName already exist.';
				return $response;
			}
			
			// UID of current user that is creating new user
			$userCreatedUID = self::getUserFromToken()->UserID;
			
			//options for bcrypt
			$options = [
				'cost' => 12,
			];
			
			//handle the password
			//get pass from data
			$securedPass = $data['UserPassword'];
			//decrypt password
			$decryptedPass = BaseActiveController::decrypt($securedPass);
			//hash pass with bcrypt
			$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
			$data['UserPassword'] = $hashedPass;
			
			$scUser = new SCUser;
			$scUser->attributes = $data;
			
			BaseUser::setClient($headers['X-Client']);
			$betaUser = new BaseUser;
			$betaUser->attributes = $data;
			//get db for rbac
			$betaDb = BaseUser::getDb();
			
			//rbac check if attempting to create an admin
			if($betaUser['UserAppRoleType'] == 'Admin')
			{
				BaseUser::setClient($headers['X-Client']);
				BetaPermissionsController::requirePermission('userCreateAdmin', $headers['X-Client']);
			}
			
			//populate created by and date
			$scUser->UserCreatedUID = $userCreatedUID;
			$scUser->UserCreatedDate = Parent::getDate();
			
			$betaUser->UserCreatedUID = $userCreatedUID;
			$betaUser->UserCreatedDate = Parent::getDate();
			
			//save project level
			BaseUser::setClient($headers['X-Client']);
			if($betaUser-> save())
			{
				//assign beta rbac role
				$betaAuth = new BetaDbManager($betaDb);
				if($betaRole = $betaAuth->getRole($betaUser['UserAppRoleType']))
				{
					$betaAuth->assign($betaRole, $betaUser['UserID']);
				}
				
				SCUser::setClient(BaseActiveController::urlPrefix());
				if($scUser-> save())
				{
					//get project based on client header
					$project = Project::find()
						->where(['ProjectUrlPrefix' => $headers['X-Client']])
						->one();
					$projectID = $project->ProjectID;
					$userID = $scUser->UserID;
					$scUser->link('projects', $project);
					try
					{
						$connection = SCUser::getDb();
						$transaction = $connection-> beginTransaction();
						$timeCardCommand = $connection->createCommand("EXECUTE PopulateTimeCardTbForUserToProjectCatchErrors_proc :TechID,:ProjectID");
						$timeCardCommand->bindParam(':TechID', $userID,  \PDO::PARAM_INT);
						$timeCardCommand->bindParam(':ProjectID', $projectID,  \PDO::PARAM_INT);
						$timeCardCommand->execute();
						$mileageCardCommand = $connection->createCommand("EXECUTE PopulateMileageCardTbForUserToProjectCatchErrors_proc :TechID,:ProjectID");
						$mileageCardCommand->bindParam(':TechID', $userID,  \PDO::PARAM_INT);
						$mileageCardCommand->bindParam(':ProjectID', $projectID,  \PDO::PARAM_INT);
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
					if($userRole = $auth->getRole($scUser['UserAppRoleType']))
					{
						$auth->assign($userRole, $scUser['UserID']);
					}
					
					$response->setStatusCode(201);
					$betaUser->UserPassword = '';
					$response->data = $betaUser;
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
	public function actionUpdate($ID)
	{
		try
		{
			$headers = getallheaders();
			BaseUser::setClient($headers['X-Client']);
			BetaPermissionsController::requirePermission('userUpdate', $headers['X-Client']);
			
			SCUser::setClient(BaseActiveController::urlPrefix());
			$modifiedUID = self::getUserFromToken()->UserUID;
			
			//options for bcrypt
			$options = [
				'cost' => 12,
			];
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$responseArray = [];
			
			BaseUser::setClient($headers['X-Client']);
			$betaUser = BaseUser::find()
				->where(['UserID' => $ID])
				->one();
				
			//get beta db for rbac
			$betaDb = BaseUser::getDb();
				
			if($betaUser->UserName != $data['UserName'])
			{
				SCUser::setClient(BaseActiveController::urlPrefix());
				$duplicateUser = SCUser::find()
					->where(['UserName' => $data['UserName']])
					->all();
				if($duplicateUser != null)
				{
					$response->setStatusCode(400);
					$response->data = 'UserName already exist.';
					return $response;
				}
			}
			
			//handle the password
			//get pass from data
			if(array_key_exists('UserPassword', $data))
			{
				//decrypt password
				$securedPass = $data['UserPassword'];
				$decryptedPass = BaseActiveController::decrypt($securedPass);
				
				//check if new password
				if($decryptedPass != "")
				{
					//hash pass with bcrypt
					$hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT,$options);
					$data['UserPassword'] = $hashedPass;
				}
				else
				{
					unset($data['UserPassword']);
				}
			}
			
			//pass new data to user
			$betaUser->attributes = $data;
			// Get modified by from token
			$betaUser->UserModifiedUID = $modifiedUID;
			//set modified dates
			$betaUser->UserModifiedDate = Parent::getDate();
			
			//rbac check if attempting to create an admin
			if($betaUser['UserAppRoleType'] == 'Admin')
			{
				BaseUser::setClient($headers['X-Client']);
				BetaPermissionsController::requirePermission('userCreateAdmin', $headers['X-Client']);
			}
			
			BaseUser::setClient($headers['X-Client']);
			if($betaUser->update())
			{
				//assign beta rbac role
				$betaAuth = new BetaDbManager($betaDb);
				if($betaRole = $betaAuth->getRole($betaUser['UserAppRoleType']))
				{
					$betaAuth->revokeAll($betaUser['UserID']);
					$betaAuth->assign($betaRole, $betaUser['UserID']);
				}
				
				$baseUpdateResponse = Yii::$app->runAction('v1/user/update', ['jsonData' => json_encode($data), 'client' => $headers['X-Client'], 'username' => $betaUser->UserName]);
				$betaUser->UserPassword = '';
				$responseArray = $betaUser->attributes;
				$responseArray['UpdatedProjects'] = $baseUpdateResponse->data['UpdatedProjects'];
				$response->data = $responseArray;
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
		catch(\Exception $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
