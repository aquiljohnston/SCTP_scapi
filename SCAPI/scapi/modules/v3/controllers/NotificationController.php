<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\constants\Constants;
use app\modules\v3\models\SCUser;
use app\modules\v3\models\Project;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\TimeCardSumHoursWorkedPriorWeekWithProjectName;
use app\modules\v3\models\TimeCardSumHoursWorkedCurrentWeek;
use app\modules\v3\models\MileageCardSumMilesPriorWeekWithProjectName;
use app\modules\v3\models\MileageCardSumMilesCurrentWeekWithProjectName;
use app\modules\v3\controllers\BaseActiveController;
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

    public function actionGetNotifications(){
        try {
			//get client header 
			$client = getallheaders()['X-Client'];
			
			//set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			PermissionsController::requirePermission('notificationsGet');

            //get user
            $user = BaseActiveController::getUserFromToken();
			
			//build response structure and instantiate variables
			$notifications = [];
			$notifications['notifications'] = [];
			$notifications['timeCards'] = [];
			$notifications['mileageCards'] = [];
			$notificationTotal = 0;
			$timeCardPriorTotal = 0;
			$timeCardCurrentTotal = 0;
			$mileageCardPriorTotal = 0;
			$mileageCardCurrentTotal = 0;

			if(BaseActiveController::isSCCT($client)){
				//get projects the user belongs to
				$projectData = $user->projects;
				$projectArray = array_map(function ($model) {
					return $model->attributes;
				}, $projectData);
				$projectSize = count($projectArray);
			}else{
				//get specific non scct client based on urlPrefix
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

				//cast string results from sql counts to int values
				//get count of unapproved time cards from last week for project
				$timeCardPriorCount = (int)TimeCardSumHoursWorkedPriorWeekWithProjectName::find()
					->where(['and', "TimeCardProjectID = $projectID", "TimeCardApprovedFlag = 0"])
					->count();
					
				$timeCardCurrentCount = (int)TimeCardSumHoursWorkedCurrentWeek::find()
					->where(['and', "TimeCardProjectID = $projectID", "TimeCardApprovedFlag = 0"])
					->count();	
				
				$mileageCardPriorCount = (int)MileageCardSumMilesPriorWeekWithProjectName::find()
					->where(['and', "MileageCardProjectID = $projectID", "MileageCardApprovedFlag = 0"])
					->count();
					
				$mileageCardCurrentCount = (int)MileageCardSumMilesCurrentWeekWithProjectName::find()
					->where(['and', "MileageCardProjectID = $projectID", "MileageCardApprovedFlag = 0"])
					->count();
				
				if($timeCardPriorCount != 0 || $timeCardCurrentCount !=0){
					//pass time card data for project
					$timeCardData['Project'] = $projectName;
					$timeCardData['ProjectID'] = $projectID;
					$timeCardData['PriorWeekCount'] = $timeCardPriorCount;
					$timeCardData['CurrentWeekCount'] = $timeCardCurrentCount;
					//append data to response array
					$notifications['timeCards'][] = $timeCardData;
					//increment total count
					$timeCardPriorTotal += $timeCardPriorCount;
					$timeCardCurrentTotal += $timeCardCurrentCount;
				}
				if($mileageCardPriorCount !=0 || $mileageCardCurrentCount != 0){
					//pass time card data for project
					$mileageCardData['Project'] = $projectName;
					$mileageCardData['ProjectID'] = $projectID;
					$mileageCardData['PriorWeekCount'] = $mileageCardPriorCount;
					$mileageCardData['CurrentWeekCount'] = $mileageCardCurrentCount;
					//append data to response array
					$notifications['mileageCards'][] = $mileageCardData;
					//increment total count
					$mileageCardPriorTotal += $mileageCardPriorCount;
					$mileageCardCurrentTotal += $mileageCardCurrentCount;
				}
				
				//get notifications
				$notificationData = self::getNotificationRecords($projectID, $user->UserAppRoleType);
				
				if($notificationData != null){
					//append data to response array
					$notifications['notifications'] = array_merge($notifications['notifications'], $notificationData);
				}
			}

			//pass time card data for total
			$timeCardTotalData['Project'] = 'Total';
			$timeCardTotalData['PriorWeekCount'] = $timeCardPriorTotal;
			$timeCardTotalData['CurrentWeekCount'] = $timeCardCurrentTotal;
			
			//pass mileage card data for total
			$mileageCardTotalData['Project'] = 'Total';
			$mileageCardTotalData['PriorWeekCount'] = $mileageCardPriorTotal;
			$mileageCardTotalData['CurrentWeekCount'] = $mileageCardCurrentTotal;

			//loop notification data for total
			$notificationTotal = 0;
			foreach($notifications['notifications'] as $notification){
				//increment count
				$notificationTotal += $notification['Count'];
			}
			$notificationTotalData['ProjectName'] = 'Total';
			$notificationTotalData['Count'] = $notificationTotal;
			
			//append totals to response array
			$notifications['notifications'][] = $notificationTotalData;
			$notifications['timeCards'][] = $timeCardTotalData;
			$notifications['mileageCards'][] = $mileageCardTotalData;

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

	//get notification data by projectID and roletype from spListOfNotifications
    private function getNotificationRecords($projectID, $roletype){
        try {
			//load data into array
			$notificationData = [];

			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
				
			//if sp call fails concat sp name instead
			$db = BaseActiveRecord::getDb();
			$spCommand = $db->createCommand("SET NOCOUNT ON EXECUTE spListOfNotifications :projectID, :roletype");
			$spCommand->bindParam(':projectID', $projectID, \PDO::PARAM_STR);
			$spCommand->bindParam(':roletype', $roletype, \PDO::PARAM_STR);
			$notificationData = $spCommand->queryAll();

			return $notificationData;
			
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
	
	//create a new notification based on params
	// public function create($type, $itemIDArray, $description){
		// //set db target headers
        // BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		
		// $connection = BaseActiveRecord::getDb();
		// //TODO set sp name and params based on final SP
		// $createCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spCreateNotification :Type, :ItemIDArray, :Description");
		// $createCommand->bindParam(':Type', $type,  \PDO::PARAM_STR);
		// $createCommand->bindParam(':ItemIDArray', $itemIDArray,  \PDO::PARAM_STR);
		// $createCommand->bindParam(':Description', $description,  \PDO::PARAM_STR);
		// $createCommand->execute(); 
	// }
}