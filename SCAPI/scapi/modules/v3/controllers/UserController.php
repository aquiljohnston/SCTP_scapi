<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\models\SCUser;
use app\modules\v3\models\Project;
use app\modules\v3\models\Client;
use app\modules\v3\models\ProjectUser;
use app\modules\v3\models\Equipment;
use app\modules\v3\models\AllTimeCardsCurrentWeek;
use app\modules\v3\models\AllMileageCardsCurrentWeek;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\ABCCodes;
use app\modules\v3\models\ProjectConfiguration;
use app\modules\v3\models\PerDiem;
use app\modules\v2\controllers\TaskController; //using getTask currently only in v2 TODO update for v3
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\controllers\PermissionsController;
use app\modules\v3\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;


/**
 * UserController implements the routes for the User model.
 */
class UserController extends BaseActiveController
{
    public $modelClass = 'app\modules\v3\models\SCUser';

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
		];
        $behaviors['verbs'] =
            [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-me' => ['get']
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
     * Gets a users data, the equipment assigned to them, and all projects that they are associated with
     * @param $userID
     * @returns json body containing userdata, equipment, and projects
     * @throws \yii\web\HttpException
     */
    public function actionGetMe(){
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
			
			//add user per diem rate to get me call
			$user['hasPerDiem'] = $user['Division'] == null ? 0 : 1;
			
			$perDiem = PerDiem::find()
				->select('Rate')
				->where(['ID' => $user['Division']])
				->one();
				
			$user['PerDiem'] = $perDiem != null ? $perDiem['Rate'] : null;

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
					
				//get ptoBalance, and pass it to user data array
				$ptoBalance = PtoController::queryBalance($timeCardModel['TimeCardID'], $db)['PTOBalance'];
				$user['PTOBalance'] = $ptoBalance;
				
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
				
				//get ABC codes for project, based on project ID
				$abcCodes = ABCCodes::find()
					->where(['ProjectID' => $projectID])
					->andWhere(['IsActive' => 1])
					->andWhere(['IsSource' => 1])
                    ->all();
				$abcCodesArray = array_map(function ($model) {
                    return $model->attributes;
                }, $abcCodes);
				
				//error handling to avoid breaking get me if task are not avaliable.
				try{
					$projectTask = TaskController::getTask($projectID);
				}catch(\Exception $e){
					//set client back to ct after external call, logging of error will retarget db
					BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
					$projectTask = [];
				}

                $clientModel = Client::findOne($projectModel->ProjectClientID);
				
				//get questions list for apk
				$questionsArray = $projectModel->getQuestionData()->all();

				$projectConfig = ProjectConfiguration::find()
					->where(['ProjectID' => $projectID])
					->one();
				
                $projectData['ProjectID'] = $projectModel->ProjectID;
                $projectData['RefProjectID'] = $projectModel->ProjectReferenceID;
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
                $projectData['ABCCodes'] = $abcCodesArray;
                $projectData['QuestionData'] = $questionsArray;
				//get project config values if not available default to 0
                $projectData['IsEndOfDayTaskOut'] = $projectConfig != null ? $projectConfig->IsEndOfDayTaskOut : 0;

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
	
}
