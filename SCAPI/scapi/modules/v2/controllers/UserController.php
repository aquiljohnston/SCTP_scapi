<?php

namespace app\modules\v2\controllers;

use app\modules\v2\models\Auth;
use Yii;
use app\modules\v2\constants\Constants;
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
use app\modules\v2\models\Users;
use app\modules\v2\models\InActiveUsers;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\PermissionsController;
use app\modules\v2\controllers\ProjectController;
use app\modules\v2\controllers\DispatchController;
use app\modules\v2\controllers\TaskController;
use app\modules\v2\authentication\TokenAuth;
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
use yii\db\Query;


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
                    'delete' => ['delete'],
                    'update' => ['put'],
                    'view' => ['get'],
                    'deactivate' => ['put'],
                    'reactivate' => ['put'],
                    'get-me' => ['get'],
                    'get-active' => ['get'],
                    'get-inactive' => ['get'],
                    'reset-password' => ['put'],
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
     * Creates a new user record in the database
     * @returns Response json body of the user data
     * @throws \yii\web\HttpException
     */
    public function actionCreate()
    {
        try {
			//get client header
			$client = getallheaders()['X-Client'];
			
            PermissionsController::requirePermission('userCreate', $client);

            //read the post input (use this technique if you have no post variable name):
            $post = file_get_contents("php://input");
            //decode json post input as php array:
            $data = json_decode(utf8_decode($post), true);
			
			$currentRole = $data['UserAppRoleType'];

            PermissionsController::requirePermission('userCreate' . $currentRole, $client);

            //create response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

			//set db target to base to handle scct user creation
            SCUser::setClient(BaseActiveController::urlPrefix());
			
            $existingUser = SCUser::find()
                ->where(['UserName' => $data['UserName']])
                ->all();

            if ($existingUser != null) {
                $response->setStatusCode(400);
                $response->data = Constants::USERNAME_EXIST_MESSAGE;
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
			
			//set payment method
			if($user->UserAppRoleType == 'Technician'){
				$user->UserPayMethod = Constants::PAY_METHOD_HOURLY;
			} else {
				$user->UserPayMethod = Constants::PAY_METHOD_SALARY;
			}

            //created date/by
            $username = self::getUserFromToken()->UserName;
            $user->UserCreatedUID = $username;
            $user->UserCreatedDate = Parent::getDate();

            if ($user->save()) {
                //assign rbac role
                $authClass = BaseActiveRecord::getAuthManager(BaseActiveController::urlPrefix());
                $auth = new $authClass(BaseActiveRecord::getDb());
                if ($userRole = $auth->getRole($user['UserAppRoleType'])) {
                    $auth->assign($userRole, $user['UserID']);
                }
				
				$projectQuery = Project::find()
						->where(['ProjectUrlPrefix' => $client]);
				
				//create user record in project db if necessary and generate user project relationship
				if(BaseActiveController::isSCCT($client))
				{
					$project = $projectQuery
						->andWhere(['ProjectName' => Constants::SCCT_CONFIG['BASE_PROJECT']])
						->one();
					ProjectController::addToProject($user, $project);
					$projectUser = 'SCCT User';
				}
				else{
					$project = $projectQuery
						->one();
					$projectUser = self::createInProject($user, $client, $project);
				}
                $response->setStatusCode(201);
                $user->UserPassword = '';
                $responseData = [];
                if($projectUser) {
                    $responseData['projectUser'] = $projectUser; // Nulls are okay. Empty object.
                } else {
                    $responseData['projectUser'] = false;
                }
                $responseData['scctUser'] = $user;
                $response->data = $responseData;
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
    public function actionUpdate($username = null)
    {
        try {
			//get client header
			//checks to see if request was sent directly to this route or call internally from another api controller.
			$clientHeader = getallheaders()['X-Client'];
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userUpdate');
			
			$put = file_get_contents("php://input");
			$data = json_decode(utf8_decode($put), true);

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $responseArray = [];
			
			//if client header is not scct get client username to pull sc user 
			if(!BaseActiveController::isSCCT($clientHeader))
			{
				BaseActiveRecord::setClient($clientHeader);
				$userModel = BaseActiveRecord::getUserModel($clientHeader);
				$clientUser = $userModel::find()
					->where(['UserName' => $username])
					->one();
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			}
            //get user model to be updated
			$user = SCUser::find()
				->where(['UserName' => $username])
				->one();
			
            $currentRole = $user['UserAppRoleType'];

            PermissionsController::requirePermission('userUpdate' . $currentRole);

            //options for bcrypt
            $options = [
                'cost' => 12,
            ];

            //handle the password
            //get pass from data
            if (array_key_exists('UserPassword', $data)) {
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
			//can't update this value
			if (isset($data['UserActiveFlag'])) {
				unset($data['UserActiveFlag']);
			}

            //pass new data to user
            $user->attributes = $data;
            // Get modified by from token
            $user->UserModifiedUID = self::getUserFromToken()->UserName;

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
				$updateInProjectResponse = self::updateInProject($user, $username);
                $responseArray['UpdatedProjects'] = $updateInProjectResponse;
            } else {
                return 'Failed to update base user.';
                //throw new \yii\web\HttpException(400);
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
    public function actionView($username)
    {
        try {
			//get client header
			$client = getallheaders()['X-Client'];
			
			//create response object
			$response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			
            //set db target
			BaseActiveRecord::setClient($client);

            PermissionsController::requirePermission('userView');
			
			if(BaseActiveController::isSCCT($client))
			{
				$user = SCUser::find()
					->where(['UserName' => $username])
					->one();
			}
			else
			{
				$userModel = BaseActiveRecord::getUserModel($client);
				$user = $userModel::find()
					->where(['UserName' => $username])
					->one();
			}

			if($user == null)
			{
				$user = 'User Not Found.';
				$response->statusCode = 404;
			}
			else
			{
				$user->UserPassword = '';
			}
			
			//pass data to response
            $response->data = $user;
            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * Updates the active flag of a user to 0 for inactive
	 * if deactivated in base scct will propagate to other projects
     * @param $userID id of the user record
     * @returns Response json body of user data
     * @throws \yii\web\HttpException
     */
    public function actionDeactivate($username)
		{
        try {
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//create response object
			$response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			
			//get client header
			$client = getallheaders()['X-Client'];
			
			//archive json
			BaseActiveController::archiveWebJson($username, 'User Deactivate', self::getUserFromToken()->UserName, $client);
			
			//reset db target after external call
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//get scct user
			$user = SCUser::find()
				->where(['UserName' => $username])
				->one();
				
			//check if requesting user has permission to deactivate
            PermissionsController::requirePermission('userDeactivate');			
			//get role to check if requesting user has permissions to updater use they are deactivating  
			$currentRole = $user['UserAppRoleType'];
            PermissionsController::requirePermission('userUpdate' . $currentRole);

			//get user to be deactivated
			if(BaseActiveController::isSCCT($client))
			{
				//if ct user call function to handle sp call and deactivation propagation
				$responseData = self::deactivateInScct($user);
			}else{
				//if client user use helper method to deactivate only given client
				$responseData['DeactivatedProjects'] = self::deactivateInProjects($user, $client);
			} 
			$response->data = $responseData;
            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }

    }
	
	/**
     * Calls Sp on to reactivate a user
     * Expect Json body or userids and client targer
     * @returns Response of success per user?
     * @throws \yii\web\HttpException
     */
	public function actionReactivate()
	{
		try {
			//get client header
			$client = getallheaders()['X-Client'];
            //set db target for permission check
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//RBAC permissions check
            PermissionsController::requirePermission('userReactivate');
			
            //read the post input
            $put = file_get_contents("php://input");
            //decode json post input as php array:
            $data = json_decode(utf8_decode($put), true);
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'ReactivateUser', BaseActiveController::getClientUser($client)->UserName, $client);

            //create response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
			
			//get client from json
			$projectPrefix = $data['ProjectUrlPrefix'];
			//get user array from json
			$users = $data['Usernames'];
			//array of users that errored during sp execution
			$failedUsers= [];
			
			//get user to be deactivated
			if(BaseActiveController::isSCCT($client))
			{
				//ger count in user array
				$userCount = count($users);
				
				//set up connection
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
				$ctConnection = BaseActiveRecord::getDb();
				//loop users for sp execution
				for($i = 0; $i < $userCount; $i++)
				{
					try
					{
						//execute CTSP for cascade reactivation
						$ctUserReactivateCommand = $ctConnection->createCommand("EXECUTE spCTReactivateUser :UserName,:Project");
						$ctUserReactivateCommand->bindParam(':UserName', $users[$i], \PDO::PARAM_STR);
						$ctUserReactivateCommand->bindParam(':Project', $projectPrefix, \PDO::PARAM_STR);
						$ctUserReactivateCommand->execute();
					}
					catch(\Exception $e)
					{
						$failedUsers['Failed Users'] = $users[$i];
					}
				}
			}
			else
			{
				try
				{
					//set up db connection
					BaseActiveRecord::setClient($projectPrefix);
					$connection = SCUser::getDb();
					//execute client SP for reactivation(will call CTSP to determine if reactivation is needed within base aswell)
					$userReactivateCommand = $connection->createCommand("EXECUTE spReactivateUser :JSON_Str");
					$userReactivateCommand->bindParam(':JSON_Str', $put, \PDO::PARAM_STR);
					$userReactivateCommand->execute();
				}
				catch(\Exception $e)
				{
					$failedUsers['FailedUsers'] = $users;
				}
			}
			$response->data = $failedUsers;
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
			//get headers
			$headers = getallheaders();
			//get client header
			$client = $headers['X-Client'];
			
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();

            PermissionsController::requirePermission('userGetMe');

            //get user id from auth token
            $user = self::getUserFromToken();
            $user->UserPassword = '';
			
			$userID = $user->UserID;
            $userName = $user->UserName;
			
			//cast user as an array to add SystemDateTime
			$user = (array)$user->attributes;
			$user['SystemDateTime'] = BaseActiveController::getDate();

            $equipment = [];
            //get equipment for user
            $equipment = Equipment::find()
                ->where(['EquipmentAssignedUserName' => $userName])
                ->all();

            //get users relationship to projects
            $projectQuery = ProjectUser::find()
                ->where("ProjUserUserID = $userID");
				
			//if current header is not scct only get projects for current header
			if(!BaseActiveController::isSCCT($client)){
				$urlPrefixProjects = Project::find()
					->select('ProjectID')
					->where (['ProjectUrlPrefix' => $client]);
				yii::trace('matching projects' . json_encode($urlPrefixProjects));
				$projectQuery->andWhere(['in', 'ProjUserProjectID', $urlPrefixProjects]);
			}
			
			$projectUser = $projectQuery->all();

            //get projects based on relationship
            $projectUserLength = count($projectUser);
            $projects = [];
            for ($i = 0; $i < $projectUserLength; $i++) {
                //set current projectID
                $projectID = $projectUser[$i]->ProjUserProjectID;
				
				//get project
                $projectModel = Project::findOne($projectID);
				
				try{
					//get user id for project external call will set current db to $projectModel->ProjectUrlPrefix
					$projectUserRecord = BaseActiveController::getClientUser($projectModel->ProjectUrlPrefix);
					$projectUserID = $projectUserRecord->UserID;
					$projectUserName = $projectUserRecord->UserName;
				}catch(\Exception $e){
					//set client back to ct after external call, may have changed target db before error
					BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
					continue;
				}
				
				//set client back to ct after external call
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

                //get time card for the current week for this project
                $timeCardModel = AllTimeCardsCurrentWeek::find()
                    ->where("UserID = $userID")
                    ->andWhere("TimeCardProjectID = $projectID")
					->asArray()
                    ->One();
				
				if($timeCardModel != null){
					//format time summary data
					$totalHoursArray = [
						(float)$timeCardModel['Sun'],
						(float)$timeCardModel['Mon'],
						(float)$timeCardModel['Tue'],
						(float)$timeCardModel['Wed'],
						(float)$timeCardModel['Thu'],
						(float)$timeCardModel['Fri'],
						(float)$timeCardModel['Sat']
					];
					
					//add total hours to response data
					$timeCardModel['HoursWorked'] = $totalHoursArray;
					//cast total hours to float
					$timeCardModel['WeeklyTotal'] = (float)$timeCardModel['WeeklyTotal'];
					
					//remove day keys
					unset(
						$timeCardModel['Sun'],
						$timeCardModel['Mon'],
						$timeCardModel['Tue'],
						$timeCardModel['Wed'],
						$timeCardModel['Thu'],
						$timeCardModel['Fri'],
						$timeCardModel['Sat']
					);
				}

                //get time card for the current week for this project
                $mileageCardModel = AllMileageCardsCurrentWeek::find()
                    ->where("UserID = $userID")
                    ->andWhere("MileageCardProjectID = $projectID")
					->asArray()
                    ->One();
					
				if($mileageCardModel != null){
					//format mileage summary data
					$totalMilesArray = [
						(float)$mileageCardModel['Sun'],
						(float)$mileageCardModel['Mon'],
						(float)$mileageCardModel['Tue'],
						(float)$mileageCardModel['Wed'],
						(float)$mileageCardModel['Thu'],
						(float)$mileageCardModel['Fri'],
						(float)$mileageCardModel['Sat']
					];
					
					//add total hours to response data
					$mileageCardModel['MilesTraveled'] = $totalMilesArray;
					//cast total hours to float
					$mileageCardModel['WeeklyTotal'] = (float)$mileageCardModel['WeeklyTotal'];
					
					//remove day keys
					unset(
						$mileageCardModel['Sun'],
						$mileageCardModel['Mon'],
						$mileageCardModel['Tue'],
						$mileageCardModel['Wed'],
						$mileageCardModel['Thu'],
						$mileageCardModel['Fri'],
						$mileageCardModel['Sat']
					);
				}

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
                    $activityCodesArray[$j]['PayrollCode'] = 'TODO';
                }
				
				//error handling to avoid breaking get me if task are not avaliable.
				try{
					$projectTask = TaskController::getTask($projectID);
				}catch(\Exception $e){
					//set client back to ct after external call, logging of error will retarget db
					BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
					$projectTask = [];
				}

                $clientModel = Client::findOne($projectModel->ProjectClientID);
				
                $projectData['ProjectID'] = $projectModel->ProjectID;
                $projectData['ProjectName'] = $projectModel->ProjectName;
                $projectData['ProjectUrlPrefix'] = $projectModel->ProjectUrlPrefix;
                $projectData['ProjectClientID'] = $projectModel->ProjectClientID;
                $projectData['ProjectClientPath'] = $clientModel->ClientFilesPath;
				$projectData['ProjectUserID'] = $projectUserID;
				$projectData['ProjectUserName'] = $projectUserName;
				$projectData['ProjectMinimumAppVersion'] = $projectModel->ProjectMinimumAppVersion;
				$projectData['ProjectActivityGPSInterval'] = $projectModel->ProjectActivityGPSInterval;
				$projectData['ProjectSurveyGPSInterval'] = $projectModel->ProjectSurveyGPSInterval;
				$projectData['ProjectSurveyGPSMinDistance'] = $projectModel->ProjectSurveyGPSMinDistance;
				$projectData['ProjectType'] = $projectModel->ProjectType;
				$projectData['BreakTimeValue'] = $projectModel->BreakTimeValue;
				$projectData['LunchTimeValue'] = $projectModel->LunchTimeValue;
				$projectData['ProjectTask'] = $projectTask;
                $projectData['TimeCard'] = $timeCardModel;
                $projectData['MileageCard'] = $mileageCardModel;
                $projectData['ActivityCodes'] = $activityCodesArray;
                $projectData['PayCodes'] = $payCodesArray;

                $projects[] = $projectData;
            }
			
			$transaction->commit();

            //load data into array
            $dataArray = [];
            $dataArray['User'] = $user;
            $dataArray['Projects'] = $projects;
            $dataArray['Equipment'] = $equipment;

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $dataArray;
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
    public function actionGetActive($listPerPage = null, $page = null, $filter = null, $projectID = 'all', $sortField = 'UserLastName', $sortOrder = 'ASC')
    {
        try {
			//get headers
			$headers = getallheaders();
			//get client header
			$client = $headers['X-Client'];
			
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            PermissionsController::requirePermission('userGetActive');
			
			//initialize response array
			$responseArray['assets'] = [];
			$responseArray['pages'] = [];

			//fetch data from db for projects and users
			if(BaseActiveController::isSCCT($client))
			{
				$showProjectDropdown = true;
				//get user id from auth token
				$user = self::getUserFromToken();
				
				//create base query for projects
				if(PermissionsController::can('projectGetAll')){
					$projectQuery = (new Query())->select('ProjUserProjectID')->distinct()->from('Project_User_Tb');
				}elseif(PermissionsController::can('projectGetOwnProjects')){
					$projectQuery = (new Query())->select('ProjUserProjectID')->from('Project_User_Tb')
						->where(['ProjUserUserID' => $user->UserID]);
				}else{
					throw new ForbiddenHttpException;
				}
				
				//get projects for dropdown before applying filter
				$projects = Project::find()->select('*')->where(['in', 'ProjectID', $projectQuery])->orderBy('ProjectName')->all();

				//create base of user query
				$userQuery = SCUser::find()->select('*')
					->where(['[UserTb].UserActiveFlag' => 1]);
				//handle unassigned users
				if($projectID == 'unassigned'){
					$userQuery->andWhere(['not in', 'UserID', (new Query())->select('ProjUserUserID')->from('Project_User_Tb')
						->where(['in','ProjUserProjectID', $projectQuery])]);
				}elseif($projectID != 'all') {
					// if($projectID != 'assigned') could be used to get all assigned users if this is a route we want to take in the future
					//filter by selected project
					$projectQuery->andWhere(['ProjUserProjectID' => $projectID]);				
					//get assigned users based on selected project(s)
					$userQuery->andWhere(['in', 'UserID', (new Query())->select('ProjUserUserID')->from('Project_User_Tb')
							->where(['in','ProjUserProjectID', $projectQuery])]);
				}
			}else{
				//get projects for dropdown
				$showProjectDropdown = false;
				PermissionsController::requirePermission('projectGetOwnProjects');
				$projects = Project::find()->where(['ProjectUrlPrefix' => $client])->all();
				BaseActiveRecord::setClient($client);
				//create base of user query
				$userQuery = Users::find();
			}
			
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
			
			//check if paging parameters were sent
			if ($page != null) 
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($userQuery, $page, $listPerPage);
				//use updated query with pagination clause to get data
				$usersArr = $paginationResponse['Query']
					->orderBy("$sortField $sortOrder")
					->all();
				$responseArray['pages'] = $paginationResponse['pages'];
			}else{
				//if no pagination params were sent use base query
				$usersArr = $userQuery
					->orderBy("$sortField $sortOrder")
					->all();
			}
			
			//structure project Dropdowns
			$dropdownPairs = [
					'all' => 'All',
					'unassigned' => 'Unassigned'
				];
			//add projects
			foreach($projects as $project)
			{
				$dropdownPairs[$project->ProjectID]= $project->ProjectName;
			}
			
			//get project data for add user modal
			foreach($projects as $project)
			{
				$addUserProjects[]= [
					"ProjectID" => $project->ProjectID,
					"ProjectName" => $project->ProjectName,
					"ProjectReferenceID" => $project->ProjectReferenceID
				];
			}
			
			//populate response array
            $responseArray['assets'] = $usersArr;
            $responseArray['showProjectDropdown'] = $showProjectDropdown;
			$responseArray['projectDropdown'] = $dropdownPairs;
			$responseArray['addUserProjects'] = $addUserProjects;
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->setStatusCode(200);
			$response->data = $responseArray;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
	
	/**
     * Gets users data for all users with an active flag of 0 for inactive
     * @param $filter
     * @returns json body of users
     * @throws \yii\web\HttpException
     */
    public function actionGetInactive($filter = null)
    {
        try {
			//get headers
			$headers = getallheaders();
			//get client header
			$client = $headers['X-Client'];
			
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//TODO create new permission
            PermissionsController::requirePermission('userGetActive');
			
			//initialize response array
			$responseArray['users'] = [];
			
			//set db connection to client db
			BaseActiveRecord::setClient($client);
			$userQuery = InActiveUsers::find();
			
			//apply filter to query
			if($filter != null)
			{
				$userQuery->andFilterWhere([
				'or',
				['like', 'UserName', $filter],
				['like', 'Name', $filter],
				['like', 'UserAppRoleType', $filter],
				]);
			}
			$usersArr = $userQuery->all();
			
			//populate response array
            $responseArray['users'] = $usersArr;
            
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->setStatusCode(200);
			$response->data = $responseArray;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
	
	/*creates a copy of the scuser $user
	//in the project db $client
	//Params
	//$user - user being added to the project
	//$client - project url prefix of the project being added to
	returns ???*/
	public static function createInProject($user, $client, $project = null)
	{
		//get user model based on project 
		$userModel = BaseActiveRecord::getUserModel($client);
		if($userModel == null) return 'No Client User Model Found.';
		$userModel::setClient($client);    
		
		//check if user exist in project db
		$existingUser = $userModel::find()
			->where(['UserName' => $user->UserName])
			->one();
		
		//if user record already exist in the project db, return
		if($existingUser != null) 
		{
			//reactivate user if they are currently inactive
			if($existingUser->UserActiveFlag == 0){
				$existingUser->UserActiveFlag = 1;
				$existingUser->update();
			}
			//add user to project to generate time/mileage cards
			ProjectController::addToProject($user, $project);
            return true;
        }
		
		//create a new user model based on project 
		$projectUser = new $userModel();
		
		//pass $user attributes into new model
		$projectUser->attributes = $user->attributes;
		//get user id for created by in project db
		$createdByInProject = BaseActiveController::getClientUser($client);
		if($createdByInProject != null)
		{
			$projectUser->UserCreatedUID = $createdByInProject->UserName;
		}
		//set comment created on addition to project
		$projectUser->UserComments = 'User created on association to project.';
		//set active flag, is null in user->attributes because it is set on db
		$projectUser->UserActiveFlag = 1;
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
			//add user to project to generate time/mileage cards
			ProjectController::addToProject($user, $project);
		}
		return $projectUser;
	}
	
	public static function updateInProject($user, $username)
	{
		$responseArray = [];
		
		//find all projects
		$userProjects = ProjectUser::find()
			->select('ProjUserProjectID')
			->where(['ProjUserUserID' => $user->UserID])
			->asArray()
			->all();
		$projectCount = count($userProjects);

		//loop projects
		for ($i = 0; $i < $projectCount; $i++) {
			//reset db to Comet Tracker
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			//get project information
			$project = Project::findOne($userProjects[$i]['ProjUserProjectID']);
				
			//get model from base active record based on urlPrefix in project
			$userModel = BaseActiveRecord::getUserModel($project->ProjectUrlPrefix);
			if($userModel == null) continue;
			$userModel::setClient($project->ProjectUrlPrefix);
			$projectUser = $userModel::find()
				->where(['UserName' => $username])
				->one();

			$projectUser->attributes = $user->attributes;
			//get user id for created by in project db
			$updatedByInProject = BaseActiveController::getClientUser($project->ProjectUrlPrefix);
			if($updatedByInProject != null)
			{
				$projectUser->UserModifiedUID = $updatedByInProject->UserName;
			}
			//can't update these values
			if (isset($projectUser['UserCreatedUID'])) {
				unset($projectUser['UserCreatedUID']);
			}
			if (isset($projectUser['UserActiveFlag'])) {
				unset($projectUser['UserActiveFlag']);
			}
			
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
				$responseArray[] = $project->ProjectUrlPrefix;
			}
		}
		return $responseArray;
	}
	
	//deactivate in scct base and propagate to all associated clients
	private static function deactivateInScct($user)
	{
		try {
			//process user deactivation in client dbs
			$deactivatedProjects = self::deactivateInProjects($user);
			$userID = $user->UserID;
			$username = $user->UserName;
			
			 //call stored procedure to for cascading deactivation of a user
			$connection = SCUser::getDb();
			$userDeactivateCommand = $connection->createCommand("EXECUTE SetUserInactive_proc :PARAMETER1");
			$userDeactivateCommand->bindParam(':PARAMETER1', $userID, \PDO::PARAM_INT);
			$userDeactivateCommand->execute();
			
			//Log out user so that they don't receive 403s if logged in or deactivating self
			$auth = Auth::findOne(["AuthUserID" => $userID]);
			if($auth != null) $auth->delete();
			
			//build response data
			//requery user after deactivation
			//may be able to use the function refresh() to do this.
			$user = SCUser::findOne($userID);
			$user->UserPassword = '';
			$userData['User'] = $user->attributes;
			$userData['DeactivatedProjects'] = $deactivatedProjects;
			$response = $userData;
		} catch (Exception $e) {
			$response = 'Failed to Properly Deactivate User';
		}
		return $response;
	}
	
	//deactivate user in all accociated non PG&E clients or given non pge client based on optional param
	public static function deactivateInProjects($user, $client = null)
	{
		try {
			$response = [];
			if($client == null)
			{
				//find all projects
				$userProjects = Project::find()
					->select('ProjectUrlPrefix')
					->innerJoin('Project_User_Tb', '[ProjectTb].[ProjectID] = [Project_User_Tb].[ProjUserProjectID]')
					->where(['ProjUserUserID' => $user->UserID])
					->all();
			}
			else //get project for given client
			{
				$userProjects[]['ProjectUrlPrefix'] = $client;
			}
			$projectCount = count($userProjects);
			
			//loop projects
			for ($i = 0; $i < $projectCount; $i++) {
				//try catch for individual projects
				try {
					//if has an scct prefix skip
					if (BaseActiveController::isSCCT($userProjects[$i]['ProjectUrlPrefix'])) continue;
					//get model from base active record based on urlPrefix in project
					$userModel = BaseActiveRecord::getUserModel($userProjects[$i]['ProjectUrlPrefix']);
					if($userModel == null) continue;
					$userModel::setClient($userProjects[$i]['ProjectUrlPrefix']);
					$projectUser = $userModel::find()
						->where(['UserName' => $user->UserName])
						->andWhere(['UserActiveFlag' => 1])
						->one();

					//check if active user was found
					if ($projectUser != null)
					{
						$projectUser->UserActiveFlag = 0;

						//unassign all associate work queues for the current project prior to deactivating the user
						$unassignedFlag = DispatchController::unassignUser($projectUser->UserID, $userProjects[$i]['ProjectUrlPrefix']);
						
						if ($projectUser->update()) {
							/*remove rbac role
							I dont belive this is reset on reactivation and 
							I feel is unnecessary on deactivation because being inactive would prevent system access entirely*/
							/*$projectAuthClass = BaseActiveRecord::getAuthManager($project->ProjectUrlPrefix);
							if($projectAuthClass != null){
								$projectAuth = new $projectAuthClass($userModel::getDb());
								if ($userRole = $projectAuth->getRole($projectUser['UserAppRoleType'])) {
									$projectAuth->revokeAll($projectUser['UserID']);
								}
							}	*/
							$response[] = ['Client' => $userProjects[$i]['ProjectUrlPrefix'], 'UnassignedFlag' => $unassignedFlag];
						}
					}
					//reset db to Comet Tracker
					BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
				} catch (\Exception $e) {
				   $response[] =  'Failed to deactivate user in ' . $userProjects[$i]['ProjectUrlPrefix'];
				}		
			}
			return $response;
		} catch (\Exception $e) {
           return 'Failed to Deactivate in Project(s)';
        }		
	}
	
	//public route that will allow techs to reset their passwords. In progress
	//excluded from token check and does not have an associated permission
	public function actionResetPassword()
	{
		try{
			//options for bcrypt
			$options = [
				'cost' => 12,
			];
			
			$headers = getallheaders();
			
			$put = file_get_contents('php://input');
			$data = json_decode(utf8_decode($put), true);
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//find user
			if($scUser = SCUser::findOne(['UserName'=>$data['UserName'], 'UserActiveFlag'=>1]))
			{
				$securedPass = $data['Password'];
				
				//decrypt password
				$decryptedPass = BaseActiveController::decrypt($securedPass);

				$previousHash = $scUser->UserPassword;
				//Check the Hash
				if (password_verify($decryptedPass, $previousHash)) 
				{
					//set new password
					$securedPassNew = $data['NewPassword'];
					$decryptedPassNew = BaseActiveController::decrypt($securedPassNew);
					$hashNew = password_hash($decryptedPassNew, PASSWORD_BCRYPT,$options);
					
					$scUser->UserPassword = $hashNew;
					
					if ($scUser->update())
					{
						//update password in associated projects
						self::updateInProject($scUser, $scUser->UserName);
						
						$response->data = 'Password updated successfully.';
						$response->setStatusCode(200);
						return $response;
					}
					else
					{
						$response->data = 'Password failed to update.';
						$response->setStatusCode(400);
						return $response;
					}
				}
				else
				{
					$response->data = 'Password is invalid.';
					$response->setStatusCode(401);
					return $response;
				}
			}
			else
			{
				$response->data = 'User not found or inactive.';
				$response->setStatusCode(401);
				return $response;
			}
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
	}
}
