<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\Project;
use app\modules\v2\models\Client;
use app\modules\v2\models\ProjectUser;
use app\modules\v2\models\ActivityCode;
use app\modules\v2\models\Equipment;
use app\modules\v2\models\PayCode;
use app\modules\v2\models\AllTimeCardsCurrentWeek;
use app\modules\v2\models\AllMileageCardsCurrentWeek;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\PermissionsController;
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
use yii\data\Pagination;


/**
 * UserController implements the routes for the User model.
 */
class UserController extends BaseActiveController
{
    public $modelClass = 'app\modules\v2\models\SCUser';

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
                    'get-user-dropdowns' => ['get'],
                    'get-me' => ['get'],
                    'get-projects' => ['get'],
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

    //use GetAll;
	//use DeleteMethodNotAllowed;

    /**
     * Creates a new user record in the database and a corresponding key record
     * @returns json body of the user data
     * @throws \yii\web\HttpException
     */
    public function actionCreate()
    {
        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userCreate');

            //read the post input (use this technique if you have no post variable name):
            $post = file_get_contents("php://input");
            //decode json post input as php array:
            $data = json_decode(utf8_decode($post), true);

            //create response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $existingUser = SCUser::find()
                ->where(['UserName' => $data['UserName']])
                ->all();

            if ($existingUser != null) {
                $response->setStatusCode(400);
                $response->data = 'UserName already exist.';
                return $response;
            }

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
            $hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT, $options);

            //maps the data to a new user model and save
            $user = new SCUser();
            $user->attributes = $data;
            $user->UserPassword = $hashedPass;

            //rbac check if attempting to create an admin
            if ($user['UserAppRoleType'] == 'Admin') {
                PermissionsController::requirePermission('userCreateAdmin');
            }

            //created date/by
            $userID = self::getUserFromToken()->UserID;
            $user->UserCreatedUID = $userID;
            $user->UserCreatedDate = Parent::getDate();

            if ($user->save()) {
                //assign rbac role
                $auth = Yii::$app->authManager;
                if ($userRole = $auth->getRole($user['UserAppRoleType'])) {
                    $auth->assign($userRole, $user['UserID']);
                }
                $response->setStatusCode(201);
                $user->UserPassword = '';
                $response->data = $user;
            } else {
                throw new \yii\web\HttpException(400);
            }
            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * Updates a user record in the database and a corresponding key record
     * @param $id the id of a user record
     * @returns json body of the user data
     * @throws \yii\web\HttpException
     */
    public function actionUpdate($id = null, $jsonData = null, $client = null, $username = null)
    {
        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userUpdate');
			
            if ($jsonData != null) {
				$data = json_decode(utf8_decode($jsonData), true);
            } else {
                $put = file_get_contents("php://input");
                $data = json_decode(utf8_decode($put), true);
            }

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $responseArray = [];

            //get user model to be updated
            //check params
            if ($id != null) {
                $user = SCUser::findOne($id);
                //set username for future use
                $username = $user->UserName;
            } elseif ($username != null) {
                $user = SCUser::find()
                    ->where(['UserName' => $username])
                    ->one();
            } else {
                return 'no id or username';
                throw new \yii\web\HttpException(400);
            }
			
            $currentRole = $user['UserAppRoleType'];

            PermissionsController::requirePermission('userUpdate' . $currentRole);

            //options for bcrypt
            $options = [
                'cost' => 12,
            ];

            //handle the password
            //get pass from data
            if (array_key_exists('UserPassword', $data) && $jsonData == null) {
                $securedPass = $data['UserPassword'];

                //decrypt password
                $decryptedPass = BaseActiveController::decrypt($securedPass);

                //check if new password
                if ($decryptedPass != '') {
                    //hash pass with bcrypt
                    $hashedPass = password_hash($decryptedPass, PASSWORD_BCRYPT, $options);
                    $data['UserPassword'] = $hashedPass;
                } else {
                    unset($data['UserPassword']);
                }
            }

            //Don't let client change this attribute
            if (isset($data['UserCreatedUID'])) {
                unset($data['UserCreatedUID']);
            }

            //pass new data to user
            $user->attributes = $data;
            // Get modified by from token
            $user->UserModifiedUID = self::getUserFromToken()->UserID;

            //rbac check if attempting to create an admin
            if ($user['UserAppRoleType'] == 'Admin') {
                PermissionsController::requirePermission('userCreateAdmin');
            }

            $user->UserModifiedDate = Parent::getDate();

            if ($user->update()) {
                //handle potential role change
                $auth = Yii::$app->authManager;
                if ($userRole = $auth->getRole($user['UserAppRoleType'])) {
                    $auth->revokeAll($user['UserID']);
                    $auth->assign($userRole, $user['UserID']);
                }
                $response->setStatusCode(201);
                $responseArray = $user->attributes;

                //propagate update to all associated projects
                //find all projects
                $projectUser = ProjectUser::find()
                    ->select('ProjUserProjectID')
                    ->where(['ProjUserUserID' => $user->UserID])
                    ->all();
                $projectCount = count($projectUser);

                //loop projects
                for ($i = 0; $i < $projectCount; $i++) {
                    //get project information
                    $project = Project::findOne($projectUser[$i]['ProjUserProjectID']);

                    //if client is populated than original call was to a client controller in which the record has already been updated
                    //so an update does not need to be preformed for that project again
                    if ($project->ProjectUrlPrefix == $client) {
						$responseArray['UpdatedProjects'][] = $project->ProjectUrlPrefix;
                        continue;
                    }
						
                    //get model from base active record based on urlPrefix in project
                    $userModel = BaseActiveRecord::getUserModel($project->ProjectUrlPrefix);
					if($userModel == null) continue;
                    $userModel::setClient($project->ProjectUrlPrefix);
                    $projectUser = $userModel::find()
                        ->where(['UserName' => $username])
                        ->andWhere(['UserActiveFlag' => 1])
                        ->one();

                    $projectUser->attributes = $data;

                    if ($projectUser->update()) {
						 //handle potential role change
						$projectAuthClass = BaseActiveRecord::getAuthManager($project->ProjectUrlPrefix);
						if($projectAuthClass != null){
							$projectAuth = new $projectAuthClass($userModel::getDb());
							if ($userRole = $projectAuth->getRole($projectUser['UserAppRoleType'])) {
								$projectAuth->revokeAll($projectUser['UserID']);
								$projectAuth->assign($userRole, $projectUser['UserID']);
							}
						}	
                        $responseArray['UpdatedProjects'][] = $project->ProjectUrlPrefix;
                    }
                }
            } else {
                return 'failed to update base user';
                throw new \yii\web\HttpException(400);
            }
            $response->data = $responseArray;
            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
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
        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userView');

            //$userData = array_map(function ($model) {return $model->attributes;},$arrayUser);
            $user = SCUser::findOne($id);
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $user;

            $user->UserPassword = '';

            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
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
        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userDeactivate');

            //get user to be deactivated
            $user = SCUser::findOne($userID);

            $currentRole = $user["UserAppRoleType"];

            PermissionsController::requirePermission('userUpdate' . $currentRole);

            //pass new data to user model
            //$user->UserActiveFlag = 0;

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //call stored procedure to for cascading deactivation of a user
            try {
                $connection = SCUser::getDb();
                $userDeactivateCommand = $connection->createCommand("EXECUTE SetUserInactive_proc :PARAMETER1");
                $userDeactivateCommand->bindParam(':PARAMETER1', $userID, \PDO::PARAM_INT);
                $userDeactivateCommand->execute();
                $response->data = $user;
            } catch (Exception $e) {
                $response->setStatusCode(400);
                $response->data = "Http:400 Bad Request";
            }
            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
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
        try {
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

            for ($i = 0; $i < $userSize; $i++) {
                $tempPairs[$users[$i]->UserID] = $users[$i]->UserLastName . ", " . $users[$i]->UserFirstName;
            }
            $namePairs = $namePairs + $tempPairs;

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $namePairs;

            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
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

        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userGetMe');

            //get user id from auth token
            $userID = self::getUserFromToken()->UserID;

            //get user
            $user = SCUser::findOne($userID);
            $user->UserPassword = '';

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
            $projects = [];
            for ($i = 0; $i < $projectUserLength; $i++) {
                //set current projectID
                $projectID = $projectUser[$i]->ProjUserProjectID;

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
                $activityCodesArray = array_map(function ($model) {
                    return $model->attributes;
                }, $activityCodes);
                $activityCodesLength = count($activityCodesArray);
                $payCodes = PayCode::find()
                    ->all();
                $payCodesArray = array_map(function ($model) {
                    return $model->attributes;
                }, $payCodes);
                for ($j = 0; $j < $activityCodesLength; $j++) {
                    //get payroll code
                    $activityCodesArray[$j]["PayrollCode"] = "TODO";
                }

                $projectModel = Project::findOne($projectID);
                $clientModel = Client::findOne($projectModel->ProjectClientID);
                $projectData["ProjectID"] = $projectModel->ProjectID;
                $projectData["ProjectName"] = $projectModel->ProjectName;
                $projectData["ProjectClientID"] = $projectModel->ProjectClientID;
                $projectData["ProjectClientPath"] = $clientModel->ClientFilesPath;
                $projectData["TimeCard"] = $timeCardModel;
                $projectData["MileageCard"] = $mileageCardModel;
                $projectData["ActivityCodes"] = $activityCodesArray;
                $projectData["PayCodes"] = $payCodesArray;

                $projects[] = $projectData;
            }

            //load data into array
            $dataArray = [];
            $dataArray["User"] = $user;
            $dataArray["Projects"] = $projects;
            $dataArray["Equipment"] = $equipment;

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $dataArray;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
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
        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userGetProjects');

            //get users relationship to projects
            $projectUser = ProjectUser::find()
                ->where("ProjUserUserID = $userID")
                ->all();

            //get projects based on relationship
            $projectUserLength = count($projectUser);
            $projects = [];
            for ($i = 0; $i < $projectUserLength; $i++) {
                $projectID = $projectUser[$i]->ProjUserProjectID;
                $projectModel = Project::findOne($projectID);
                $projectData["ProjectID"] = $projectModel->ProjectID;
                $projectData["ProjectName"] = $projectModel->ProjectName;
                $projectData["ProjectClientID"] = $projectModel->ProjectClientID;

                $projects[] = $projectData;
            }

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $projects;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * Gets a users data for all users with an active flag of 1 for active
     * @param $listPerPage
     * @param $page
     * @returns json body of users
     * @throws \yii\web\HttpException
     */
    public function actionGetActive($listPerPage = null, $page = null, $filter = null,
                                    $filterusername = null, $filterfirstname = null, $filterlastname = null,
                                    $filterroletype = null)
    {
        try {

            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userGetActive');
			
			//initialize response array
			$responseArray['assets'] = [];
			$responseArray['pages'] = [];
			
			//create base of user query
            $userQuery = SCUser::find()->where(['UserActiveFlag' => 1]);
			//apply filter to query
			if($filter != null)
			{
				$userQuery->andFilterWhere([
				'or',
				['like', 'UserName', $filter],
				['like', 'UserFirstName', $filter],
				['like', 'UserLastName', $filter],
				['like', 'UserAppRoleType', $filter],
				]);
			}
			if($filterusername != null) {
			    $userQuery->andWhere([
			        'like', 'UserName', $filterusername
                ]);
            }
            if($filterfirstname != null) {
			    $userQuery->andWhere([
			        'like', 'UserFirstName', $filterfirstname
                ]);
            }
            if($filterlastname != null) {
			    $userQuery->andWhere([
			        'like', 'UserLastName', $filterlastname
                ]);
            }
            if($filterroletype != null) {
			    $userQuery->andWhere([
			        'like', 'UserAppROleType', $filterroletype
                ]);
            }
			//check if paging parameters were sent
			if ($page != null) 
			{
				//pass query with pagination data to helper method
				$paginationResponse = self::paginationProcessor($userQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$usersArr = $paginationResponse['Query']->all();
				$responseArray['pages'] = $paginationResponse['pages'];
			}
			else
			{
				//if no pagination params were sent use base query
				$usersArr = $userQuery->all();
			}
			//populate response array
            $responseArray['assets'] = $usersArr;
            
            if (!empty($responseArray['assets'])) {
                $response = Yii::$app->response;
                $response->format = Response::FORMAT_JSON;
                $response->setStatusCode(200);
                $response->data = $responseArray;
            }
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    public function paginationProcessor($assetQuery, $page, $listPerPage)
    {
		// set pagination
		$countAssetQuery = clone $assetQuery;
		$pages = new Pagination(['totalCount' => $countAssetQuery->count()]);
		$pages->pageSizeLimit = [1, 100];
		$offset = $listPerPage * ($page - 1);
		$pages->setPageSize($listPerPage);
		$pages->pageParam = 'userPage';
		$pages->params = ['per-page' => $listPerPage, 'userPage' => $page];
		
		//append pagination clause to query
		$assetQuery->offset($offset)
			->limit($listPerPage);

		$asset['pages'] = $pages;
		$asset['Query'] = $assetQuery;

		return $asset;
    }
	
	//creates a copy of the scuser $user
	//in the project db $client
	//Params
	//$user - user being added to the project
	//$client - project url prefix of the project being added to
	//returns ???
	public static function createInProject($user, $client)
	{
		//get user model based on project 
		$userModel = BaseActiveRecord::getUserModel($client);
		if($userModel == null) return;
        $userModel::setClient($client);
		
		//check if user exist in project
		$existingUser = $userModel::find()
			->where(['UserName' => $user->UserName])
			->one();
		if($existingUser != null) return;
		
		//create a new user model based on project 
		$projectUser = new $userModel();
		
		//set userid to null to allow auto increment on sql
		$user->UserID = null;
		//pass $user attributes into new model
		$projectUser->attributes = $user->attributes;
		//set comment created on addition to project
		$projectUser->UserComments = 'User created on association to project.';
		//save into project database
		if($projectUser->save())
		{
			//handle app role assignment
			$authClass = BaseActiveRecord::getAuthManager($client);
			if($authClass == null) return;
			$auth = new $authClass($userModel::getDb());
			if ($userRole = $auth->getRole($projectUser['UserAppRoleType'])) {
				$auth->assign($userRole, $projectUser['UserID']);
			}
		}
		return;
	}
}
