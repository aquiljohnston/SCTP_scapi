<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\constants\Constants;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\Project;
use app\modules\v2\models\Notification;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\TimeCardSumHoursWorkedPriorWeekWithProjectName;
use app\modules\v2\models\MileageCardSumMilesPriorWeekWithProjectName;
use app\modules\v2\controllers\BaseActiveController;
use yii\db\Connection;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Link;
use yii\db\mssql\PDO;
use yii\base\ErrorException;
use yii\db\Exception;


/**
 * NotificationController creates user notifications.
 */
class NotificationController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //Implements Token Authentication to check for Auth Token in Json Header
        $behaviors['authenticator'] =
            [
                'class' => TokenAuth::className(),
            ];
        $behaviors['verbs'] =
            [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-notifications' => ['get'],
                    'get-notifications-landing' => ['get'],
                ],
            ];
        return $behaviors;
    }

    public function actionGetNotifications()
    {
        try {
			//get client header 
			$client = getallheaders()['X-Client'];
			
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('notificationsGet');

            //get user
            $userID = BaseActiveController::getUserFromToken()->UserID;
            $user = SCUser::findOne($userID);
			
			//build response structure and instantiate variables
			$notifications = [];
			$notifications['notifications'] = [];
			$notifications['timeCards'] = [];
			$notificationTotal = 0;
			$timeCardTotal = 0;
			
			if(BaseActiveController::isSCCT($client))
			{
				//get projects the user belongs to
				$projectData = $user->projects;
				$projectArray = array_map(function ($model) {
					return $model->attributes;
				}, $projectData);
				$projectSize = count($projectArray);
			}else{
				$projectArray = Project::find()
					->where(['ProjectUrlPrefix' => $client])
					->asArray()
					->all();
				$projectSize = count($projectArray);
			}

			
			//loop projects to get data
			for ($i = 0; $i < $projectSize; $i++) {
				//reset db target to scct
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
				
				$projectID = $projectArray[$i]['ProjectID'];
				$projectName = $projectArray[$i]['ProjectName'];
				$projectUrlPrefix = $projectArray[$i]['ProjectUrlPrefix'];

				//cast string results from sql counts to int values
				//get count of unapproved time cards from last week for project
				$timeCardCount = (int)TimeCardSumHoursWorkedPriorWeekWithProjectName::find()
					->where(['and', "TimeCardProjectID = $projectID", "TimeCardApprovedFlag = 0"])
					->count();

				//get count of notifications
				if($projectUrlPrefix != null)
				{
					//set db target to project db
					BaseActiveRecord::setClient($projectUrlPrefix);
					try{
						//get notification for project
						$notificationCount = (int)Notification::find()
							->count();
					}catch(Exception $e){
						$notificationCount = 0;
					}
				}
				
				if($timeCardCount != 0)
				{
					//pass time card data for project
					$timeCardData['Project'] = $projectName;
					$timeCardData['ProjectID'] = $projectID;
					$timeCardData['Number of Items'] = $timeCardCount;
					//append data to response array
					$notifications['timeCards'][] = $timeCardData;
					//increment total count
					$timeCardTotal += $timeCardCount;
				}
				if($notificationCount != 0)
				{
					//pass notification data for project
					$notificationData['Project'] = $projectName;
					$notificationData['ProjectID'] = $projectID;
					$notificationData['Number of Items'] = $notificationCount;
					//append data to response array
					$notifications['notifications'][] = $notificationData;
					//increment total count
					$notificationTotal += $notificationCount;
				}
			}

			//pass time card data for total
			$timeCardData['Project'] = 'Total';
			$timeCardData['Number of Items'] = $timeCardTotal;

			//pass notification data for total
			$notificationData['Project'] = 'Total';
			$notificationData['Number of Items'] = $notificationTotal;
			
			//append totals to response array
			$notifications['notifications'][] = $notificationData;
			$notifications['timeCards'][] = $timeCardData;


			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $notifications;
			return $response;
			
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

	//get notification data for the active client based on urlprefix
	//not sure if we need to change this to pass a desired project in the future
	//notifications appear to be stored per project on the client dbs
    public function actionGetNotificationLanding($filter = null, $listPerPage = 50, $page = 1)
    {
        try {
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

            //get user
            $user = BaseActiveController::getUserFromToken();

            $projectHasNotification = false;
            $notificationData = [];

            //get projects the user belongs to
            $projectData = $user->projects;
            $projectArray = array_map(function ($model) {
                return $model->attributes;
            }, $projectData);
            $projectSize = count($projectArray);
		
			PermissionsController::requirePermission('notificationsGet');

			//load data into array
			$notifications = [];
			$notifications['firstName'] = $user->UserFirstName;
			$notifications['lastName'] = $user->UserLastName;
			$notifications['notification'] = [];
			$responseArray = [];

			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);

			//get notification for project
			$notificationData = Notification::find();

			if ($filter != null) {
				$notificationData->andFilterWhere([
					'or',
					['like', 'NotificationType', $filter],
					['like', 'SrvDTLT', $filter],
				]);
			}
			$orderBy = 'SrvDTLT';
			//pass query with pagination data to helper method
			$paginationResponse = BaseActiveController::paginationProcessor($notificationData, $page, $listPerPage);
			//use updated query with pagination caluse to get data
			$data = $paginationResponse['Query']->orderBy($orderBy)
				->all();
			$responseArray['pages'] = $paginationResponse['pages'];
			$responseArray['notification'] = $data;

			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
			
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
}