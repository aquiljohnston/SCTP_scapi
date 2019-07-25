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
			$userID = $user->UserID;
			
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

			$projectURLPrefix = null;
			if(!BaseActiveController::isSCCT($client)){
				$projectURLPrefix = $client;
			}

			$db = BaseActiveRecord::getDb();
			
			$notificationSpCommand = $db->createCommand("SET NOCOUNT ON EXECUTE spListOfNotifications :userID, :projectURLPrefix");
			$notificationSpCommand->bindParam(':userID', $userID, \PDO::PARAM_INT);
			$notificationSpCommand->bindParam(':projectURLPrefix', $projectURLPrefix, \PDO::PARAM_STR);
			$notificationData = $notificationSpCommand->queryAll();
			
			$timeCardSpCommand = $db->createCommand("SET NOCOUNT ON EXECUTE spCountUnApprovedTimeCardForCurrentAndPriorWeek :userID, :projectURLPrefix");
			$timeCardSpCommand->bindParam(':userID', $userID, \PDO::PARAM_INT);
			$timeCardSpCommand->bindParam(':projectURLPrefix', $projectURLPrefix, \PDO::PARAM_STR);
			$timeCardData = $timeCardSpCommand->queryAll();
			
			$mileageCardSpCommand = $db->createCommand("SET NOCOUNT ON EXECUTE spCountUnApprovedMileageCardForCurrentAndPriorWeek :userID, :projectURLPrefix");
			$mileageCardSpCommand->bindParam(':userID', $userID, \PDO::PARAM_INT);
			$mileageCardSpCommand->bindParam(':projectURLPrefix', $projectURLPrefix, \PDO::PARAM_STR);
			$mileageCardData = $mileageCardSpCommand->queryAll();
			
			//loop notification data for total
			foreach($notificationData as $notification){
				//increment count
				$notificationTotal += $notification['Count'];
			}
			$notificationTotalData['ProjectName'] = 'Total';
			$notificationTotalData['Count'] = $notificationTotal;
			$notificationTotalData['Count'] = 0;
		
			//loop time card data for total
			foreach($timeCardData as $timeCard){
				//increment count
				$timeCardPriorTotal += $timeCard['PriorWeekCount'];
				$timeCardCurrentTotal += $timeCard['CurrentWeekCount'];
			}
			$timeCardTotalData['ProjectName'] = 'Total';
			$timeCardTotalData['PriorWeekCount'] = $timeCardPriorTotal;
			$timeCardTotalData['CurrentWeekCount'] = $timeCardCurrentTotal;
			
			// //loop mileage card data for total
			foreach($mileageCardData as $mileageCard){
				//increment count
				$mileageCardPriorTotal += $mileageCard['PriorWeekCount'];
				$mileageCardCurrentTotal += $mileageCard['CurrentWeekCount'];
			}
			$mileageCardTotalData['ProjectName'] = 'Total';
			$mileageCardTotalData['PriorWeekCount'] = $mileageCardPriorTotal;
			$mileageCardTotalData['CurrentWeekCount'] = $mileageCardCurrentTotal;
			
			//build response array
			$notifications['notifications'] = $notificationData;
			$notifications['timeCards'] = $timeCardData;
			$notifications['mileageCards'] = $mileageCardData;
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
	
	//create a new notification based on params
	public function create($type, $itemIDArray, $description, $appRoleType, $createdBy){
		//set db target headers
        BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		
		$connection = BaseActiveRecord::getDb();
		//TODO set sp name and params based on final SP
		$createCommand = $connection->createCommand("SET NOCOUNT ON EXECUTE spInsertNotifications :Type, :AppRoleName, :JSONItemIDs, :Description, :CreatedBy");
		$createCommand->bindParam(':Type', $type,  \PDO::PARAM_STR);
		$createCommand->bindParam(':AppRoleName', $appRoleType,  \PDO::PARAM_STR);
		$createCommand->bindParam(':JSONItemIDs', $itemIDArray,  \PDO::PARAM_STR);
		$createCommand->bindParam(':Description', $description,  \PDO::PARAM_STR);
		$createCommand->bindParam(':CreatedBy', $createdBy,  \PDO::PARAM_STR);
		$createCommand->execute(); 
	}
}