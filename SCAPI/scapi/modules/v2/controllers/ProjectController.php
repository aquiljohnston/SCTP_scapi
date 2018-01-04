<?php

namespace app\modules\v2\controllers;

use app\modules\v2\models\MenusProjectModule;
use Yii;
use app\modules\v2\models\Project;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\ProjectUser;
use app\modules\v2\models\MenusModuleMenu;
use app\modules\v2\models\BaseActiveRecord;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends BaseActiveController
{
	public $modelClass = 'app\modules\v2\models\Project'; 
	
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
					'view-all-users'  => ['get'],
					'get-project-dropdowns'  => ['get'],
					'get-user-relationships'  => ['get'],
					'add-remove-users' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use DeleteMethodNotAllowed;
	
	/**
	* Gets the data for a project based on a project id
	* @param $id the id of a project record
	* @returns Response json body of the project data
	* @throws \yii\web\HttpException
	*/	
	public function actionView($id, $joinNames = false)
    {
		try
		{
			//set db target
			Project::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('projectView');
			if($joinNames) {
			    $sql =
                    'SELECT CreatedUser.UserID as CreatedUserID, CreatedUser.UserName as CreatedUserName, ProjectTb.*, 
                    ClientTb.ClientName
                    FROM ProjectTb 
                    LEFT JOIN [UserTb] CreatedUser ON ProjectTb.ProjectCreatedBy = CreatedUser.UserID
                    LEFT JOIN [ClientTb] ON ProjectTb.ProjectClientID = ClientTb.ClientID
                    WHERE ProjectTb.ProjectId = :id';
			    $project = Project::getDb()->createCommand($sql)->bindValue(':id', $id)
                    ->queryOne();
            } else {
			    $project = Project::findOne($id);
            }
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $project;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	} 
	
	/**
	* Gets all of the subclass's model's records
	*
	* @return Response The records in a JSON format
	* @throws \yii\web\HttpException 400 if any exceptions are thrown
	* @throws ForbiddenHttpException If permissions are not granted for request
	*/
    public function actionGetAll($limitToUser = null, $listPerPage = null,
                                $page = 1, $filter = null)
	{
		if(($limitToUser != "true" && $limitToUser != "1") && PermissionsController::can("projectGetAll")) {
			try
			{
				//set db target
				Project::setClient(BaseActiveController::urlPrefix());

				$projects = Project::find();



			}
			catch(\Exception $e)
			{
				throw new \yii\web\HttpException(400);
			}
		} else if (PermissionsController::can("projectGetOwnProjects")) {
			$userID = self::getUserFromToken()->UserID;

		    $projects = Project::find()->select('ProjectTb.*')
                ->leftJoin('Project_User_Tb', "[ProjectTb].[ProjectID] = [Project_User_Tb].[ProjUserProjectID]")
                ->where(['[Project_User_Tb].[ProjUserUserID]' => $userID])
                ->with('projectUserTbs');
		} else {
			throw new ForbiddenHttpException;
		}

        //apply filter to query
        if($filter != null)
        {
            $projects->andFilterWhere([
                'or',
                ['like', 'ProjectName', $filter],
                ['like', 'ProjectType', $filter],
                ['like', 'ProjectDescription', $filter],
                ['like', 'ProjectType', $filter],
                ['like', 'ProjectState', $filter],
            ]);
        }

        //pass query with pagination data to helper method
        $paginationResponse = BaseActiveController::paginationProcessor($projects, $page, $listPerPage);
        //use updated query with pagination
        $projectArr = $paginationResponse['Query']->all();
        $responseArray['pages'] = $paginationResponse['pages'];

        //populate response array
        $responseArray['assets'] = $projectArr;

        //if (!empty($responseArray['assets'])) {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->setStatusCode(200);
            $response->data = $responseArray;
        //}
    }
	
	/**
	* Creates a new project record in the database
	* @returns json body of the project data
	* @throws \yii\web\HttpException
	*/	
	public function actionCreate()
	{
		try
		{
			//set db target
			Project::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('projectCreate');
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new Project();

			$model->attributes = $data;  
			$model->ProjectCreatedBy = self::getUserFromToken()->UserName;

			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create date
			$model->ProjectCreateDate = Parent::getDate();
			
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
	* Updates a project record in the database
	* @param $id the id of a project record
	* @returns json body of the project data
	* @throws \yii\web\HttpException
	*/	
	public function actionUpdate($id)
	{
		try
		{
			//set db target
			Project::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('projectUpdate');
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			$model = Project::findOne($id);
			
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->ProjectModifiedDate = Parent::getDate();
			$model->ProjectModifiedBy = self::getUserFromToken()->UserName;
			
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
	* Creates an associative array of project id/name pairs
	* @returns json body of id name pairs
	* @throws \yii\web\HttpException
	*/
	public function actionGetProjectDropdowns()
	{
		try
		{
			//set db target
			Project::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('projectGetDropdown');
		
			$projects = Project::find()
				->orderBy('ProjectName')
				->all();
			$namePairs = [null => "Unassigned"];
			$projectSize = count($projects);
			
			for($i=0; $i < $projectSize; $i++)
			{
				$namePairs[$projects[$i]->ProjectID]= $projects[$i]->ProjectName;
			}
				
			
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
	* Creates two arrays, one of all users associated with a project 
	* the other of all users not associated with a project
	* @param $id the id of a project record
	* @returns json containing two user arrays
	* @throws \yii\web\HttpException
    */	
	public function actionGetUserRelationships($projectID, $uaFilter = null, $aFilter = null)
	{
		try
		{
			//set db target
			SCUser::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('projectGetUserRelationships');
			
			//get all users for the project
			$project = Project::findOne($projectID);
			$assignedUsers = $project->getUsers()
				->where(['UserActiveFlag' => 1]);
            if ($aFilter != null){
                $assignedUsers->andFilterWhere([
                    'or',
                    ['like', 'UserFirstName', $aFilter],
                    ['like', 'UserLastName', $aFilter],
                    ['like', 'UserName', $aFilter],
                ]);
            }
            $assignedUsers = $assignedUsers->orderBy('UserLastName')
								           ->all();
			$assignedPairs = [];
			$assignedSize = count($assignedUsers);
			
			//create array of included user id/name pairs
			for($i=0; $i < $assignedSize; $i++)
			{
				$assignedPairs[$assignedUsers[$i]->UserID] = 
				[
				'userID'	=>$assignedUsers[$i]->UserID,
				'content' 	=> $assignedUsers[$i]->UserLastName. ", ". $assignedUsers[$i]->UserFirstName . " (" . $assignedUsers[$i]->UserName . ")"
			];
			}
			
			//get all users
			$allUsers = SCUser::find()
				->where(['UserActiveFlag' => 1]);
            if ($uaFilter != null){
                $allUsers->andFilterWhere([
                    'or',
                    ['like', 'UserFirstName', $uaFilter],
                    ['like', 'UserLastName', $uaFilter],
                    ['like', 'UserName', $uaFilter],
                ]);
            }
            $allUsers = $allUsers->orderBy('UserLastName')
                                 ->all();
			
			$unassignedPairs = [];
			$unassignedSize = count($allUsers);
			
			//create array of all user id/name pairs
			for($i=0; $i < $unassignedSize; $i++)
			{
				$unassignedPairs[$allUsers[$i]->UserID]=[
					'userID' 	=> $allUsers[$i]->UserID,
					'content' 	=> $allUsers[$i]->UserLastName. ", ". $allUsers[$i]->UserFirstName . " (" . $allUsers[$i]->UserName . ")"];
			}
			
			//filter included pairs
			foreach($unassignedPairs as $uk => $uv)
			{
				foreach($assignedPairs as $ak => $av)
				{
					if($uk == $ak)
					{
						unset($unassignedPairs[$uk]);
					}
				}
			}
			
			//build response json
			$data = [];
			$data["unassignedUsers"] = $unassignedPairs;
			$data["assignedUsers"] = $assignedPairs; 
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $data;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	/**
	* Accepts two arrays one of users to add to a project and one of users to remove from a project.
	* Creates ProjectUser records for added users and deletes ProjectUser records for removed users.
	* Calls SPs to handle creation/activation of time and mileage cards for added users
	* Calls SPs to handle deactivation of time and mileage cards for users removed
	* @param $id the id of a project record
	* @returns json containing two user arrays that were processed
	* @throws \yii\web\HttpException
    */	
	public function actionAddRemoveUsers($projectID)
	{
		try
		{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permission check
			PermissionsController::requirePermission('projectAddRemoveUsers');
			
			//create response
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			
			//get project from param
			$project = Project::findOne($projectID);
			
			//decode post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			//Yii::trace("DUMPSTER: ".$data);
			
			//check if key exist
			if(array_key_exists("usersAdded", $data) && array_key_exists("usersRemoved", $data))
			{
				//parse post data
				$usersAdded = $data['usersAdded'];
				$usersRemoved = $data['usersRemoved'];
			} else {
				//set failure response
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";

				return $response;
			}
			
			//loop usersAdded and create relationships and cards
            if (count($usersAdded) > 0 && $usersAdded[0] != null) {
                foreach ($usersAdded as $i) {
                    //find user
                    $user = SCUser::findOne($i);
                    //create user in project db
                    if(UserController::createInProject($user, $project->ProjectUrlPrefix) == null)
					{
						//reset target db after external call
						BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
						//fucntion call to add to project
						self::addToProject($user, $project);
					}
                }
            }
			
			//loop usersRemoved and delete relationships and deactivate cards
            if (count($usersRemoved) > 0 && $usersRemoved[0] != null) {
                foreach ($usersRemoved as $i) {
					BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
                    $projUser = ProjectUser::find()
                        ->where(['and', "ProjUserUserID = $i", "ProjUserProjectID = $projectID"])
                        ->one();
                    $projUser->delete();
                    //call sps to deactivate time cards and mileage cards
                    try {
                        $userID = $i;
                        $connection = SCUser::getDb();
                        $transaction = $connection->beginTransaction();
                        $timeCardCommand = $connection->createCommand("EXECUTE DeactivateTimeCardByUserByProject_proc :PARAMETER1,:PARAMETER2");
                        $timeCardCommand->bindParam(':PARAMETER1', $userID, \PDO::PARAM_INT);
                        $timeCardCommand->bindParam(':PARAMETER2', $projectID, \PDO::PARAM_INT);
                        $timeCardCommand->execute();
                        $mileageCardCommand = $connection->createCommand("EXECUTE DeactivateMileageCardByUserByProject_proc :PARAMETER1,:PARAMETER2");
                        $mileageCardCommand->bindParam(':PARAMETER1', $userID, \PDO::PARAM_INT);
                        $mileageCardCommand->bindParam(':PARAMETER2', $projectID, \PDO::PARAM_INT);
                        $mileageCardCommand->execute();
                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollBack();
                    }
                }
            }
			
			//set success response 
			$response->setStatusCode(200);
			$response -> data = $data;
			
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
	
	public static function addToProject($user, $project = null)
	{
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		if($project == null)
		{
			$headers = getallheaders();
			$project = Project::find()
				->where(['ProjectUrlPrefix' => $headers['X-Client']])
				->one();
		}
		$userID = $user->UserID;
		$projectID = $project->ProjectID;
		//link user to project
		//TODO add created by via third param extraColumns array if possible http://www.yiiframework.com/doc-2.0/yii-db-baseactiverecord.html#link()-detail
		$user->link('projects',$project);
		//call sps to create new time cards and mileage cards
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
		}
		//call sps to ensure time/mileage cards are active.
		try
		{
			$connection = SCUser::getDb();
			$transaction = $connection-> beginTransaction();
			$timeCardCommand = $connection->createCommand("EXECUTE ActivateTimeCardByUserByProject_proc :UserParam,:ProjectParam");
			$timeCardCommand->bindParam(':UserParam', $userID,  \PDO::PARAM_INT);
			$timeCardCommand->bindParam(':ProjectParam', $projectID,  \PDO::PARAM_INT);
			$timeCardCommand->execute();
			$mileageCardCommand = $connection->createCommand("EXECUTE ActivateMileageCardByUserByProject_proc :UserParam ,:ProjectParam");
			$mileageCardCommand->bindParam(':UserParam', $userID,  \PDO::PARAM_INT);
			$mileageCardCommand->bindParam(':ProjectParam', $projectID,  \PDO::PARAM_INT);
			$mileageCardCommand->execute();
			$transaction->commit();
			
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
		}
	}

	public function actionGetProjectModules($projectID) {
		//PermissionsController::requirePermission('projectGetProjectModules');
		try {
			//set db target
			MenusProjectModule::setClient(BaseActiveController::urlPrefix());

			//TODO: Sanitize $projectID

			// get all modules for project
			$projectModules = MenusProjectModule::find()
				->where("ProjectModulesProjectID = $projectID")
				->all();

			$assignedModules = [];
			foreach ($projectModules as $module) {
				$assignedModules[$module->ProjectModulesName] = ['content' => $module->ProjectModulesName];
			}

			// get all modules

			$allModules = MenusModuleMenu::find()
				->distinct()
				//moduleActiveFlag == 1
				->all();

			$unassignedModules = [];

			foreach($allModules as $module) {
				$unassignedModules[$module->ModuleMenuName] = ['content' => $module->ModuleMenuName];
			}

			foreach($unassignedModules as $unassignedKey => $unassignedValue) {
				foreach($assignedModules as $assignedKey => $assignedValue) {
					if($unassignedKey == $assignedKey) {
						unset($unassignedModules[$unassignedKey]);
					}
				}
			}


			$data["assignedModules"] = $assignedModules;
			$data["unassignedModules"] = $unassignedModules;
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;

			return $response;
		} catch (Exception $e) {
			throw new BadRequestHttpException;
		}
	}

	public function actionAddRemoveModule($projectID) {
		try{
			//set db target
			Project::setClient(BaseActiveController::urlPrefix());

			$username = self::getUserFromToken()->Username;

			//create response
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;

			//get project from param
			$project = Project::findOne($projectID);


			//decode post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			//check if key exist
			if(array_key_exists("modulesAdded", $data) && array_key_exists("modulesRemoved", $data))
			{
				//parse post data
				$modulesAdded = $data['modulesAdded'];
				$modulesRemoved = $data['modulesRemoved'];
			} else {
				//set failure response
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";

				return $response;
			}

			//loop modulesAdded and create relationships
			foreach($modulesAdded as $i)
			{
				$model = new MenusProjectModule();
				$model->ProjectModulesName = $i;
				$model->ProjectModulesProjectID = $projectID;
				$model->ProjectModulesCreatedBy = $username;
				if(!$model->save()) {
					throw new BadRequestHttpException("Could not validate and save lookup table model instance.");
				}

			}

			//loop usersRemoved and delete relationships
			foreach($modulesRemoved as $i)
			{
				$modules = MenusProjectModule::find()
					->where("ProjectModulesProjectID = $projectID")
					->andwhere("ProjectModulesName = '$i'")
					->all();
				foreach($modules as $module) {
					$module->delete();
				}
			}

			//set success response
			$response->setStatusCode(200);
			$response -> data = $data;

			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
