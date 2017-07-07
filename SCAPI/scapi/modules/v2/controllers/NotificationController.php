<?php

namespace app\modules\v2\controllers;

use Yii;
use app\authentication\TokenAuth;
use app\modules\v2\models\SCUser;
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
                    'get-notifications' => ['get']
                ],
            ];
        return $behaviors;
    }

    public function actionGetNotifications()
    {
        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            //check current environment for york
            $headers = getallheaders();
            if ($headers['X-Client'] == BaseActiveRecord::YORK_DEV){
                $CURRENT_PROJECT = "York Dev";
            }elseif ($headers['X-Client'] == BaseActiveRecord::YORK_STAGE){
                $CURRENT_PROJECT = "York Stage";
            }else{
                $CURRENT_PROJECT = "York";
            }

            //get user
            $userID = BaseActiveController::getUserFromToken()->UserID;
            $user = SCUser::findOne($userID);

            // check if login user is Engineer
            if ($user->UserAppRoleType != "Engineer") {

                PermissionsController::requirePermission('notificationsGet');

                //get projects the user belongs to
                $projectData = $user->projects;
                $projectArray = array_map(function ($model) {
                    return $model->attributes;
                }, $projectData);
                $projectSize = count($projectArray);

                //load data into array
                $notifications = [];
                $notifications["firstName"] = $user->UserFirstName;
                $notifications["lastName"] = $user->UserLastName;
                $notifications["notification"] = [];
                $notifications["timeCards"] = [];
                $notifications["mileageCards"] = [];
                $notificationTotal = 0;
                $timeCardTotal = 0;
                $mileageCardTotal = 0;
                $projectHasNotification = false;
                $projectNameHasNotification = null;

                //loop projects to get data
                for ($i = 0; $i < $projectSize; $i++) {
                    $projectID = $projectArray[$i]["ProjectID"];
                    $projectName = $projectArray[$i]["ProjectName"];

                    //get unapproved time cards from last week for project
                    $timeCards = TimeCardSumHoursWorkedPriorWeekWithProjectName::find()
                        ->where(['and', "TimeCardProjectID = $projectID", "TimeCardApprovedFlag = 'No'"])
                        ->all();
                    $timeCardCount = count($timeCards);

                    //get unapproved mileage cards from last week for project
                    $mileageCards = MileageCardSumMilesPriorWeekWithProjectName::find()
                        ->where(['and', "MileageCardProjectID = $projectID", "MileageCardApprovedFlag = 'No'"])
                        ->all();
                    $mileageCardCount = count($mileageCards);

                    //pass time card data for project
                    $timeCardData["Project"] = $projectName;
                    $timeCardData["Number of Items"] = $timeCardCount;

                    //pass mileage card data for project
                    $mileageCardData["Project"] = $projectName;
                    $mileageCardData["Number of Items"] = $mileageCardCount;

                    //appened data to response array
                    $notifications["timeCards"][] = $timeCardData;
                    $notifications["mileageCards"][] = $mileageCardData;

                    //increment total counts
                    $timeCardTotal += $timeCardCount;
                    $mileageCardTotal += $mileageCardCount;

                    //check if the user associated with yorkDev
                    if ($projectName == $CURRENT_PROJECT) {
                        $projectHasNotification = true;
                        $projectNameHasNotification = $projectName;
                    }
                }

                if ($projectHasNotification){

                    //set db
                    $headers = getallheaders();
                    BaseActiveRecord::setClient($headers['X-Client']);

                    //get notification for project
                    $notificationData = Notification::find()
                        ->all();
                    $notificationTotal = count($notificationData);

                    //pass notification data for project;
                    $notificationReturnData["Project"] = $projectNameHasNotification;
                    $notificationReturnData["Number of Items"] = $notificationTotal;

                    //appened data to response array
                    $notifications["notification"][] = $notificationReturnData;
                }

                //pass notification data for total
                $notificationReturnData["Project"] = "Total";
                $notificationReturnData["Number of Items"] = $notificationTotal;

                //pass time card data for total
                $timeCardData["Project"] = "Total";
                $timeCardData["Number of Items"] = $timeCardTotal;

                //pass mileage card data for total
                $mileageCardData["Project"] = "Total";
                $mileageCardData["Number of Items"] = $mileageCardTotal;

                //append totals to response array
                $notifications["notification"][] = $notificationReturnData;
                $notifications["timeCards"][] = $timeCardData;
                $notifications["mileageCards"][] = $mileageCardData;


                //send response
                $response = Yii::$app->response;
                $response->format = Response::FORMAT_JSON;
                $response->data = $notifications;
                return $response;
            }
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetNotificationLanding($filter = null, $listPerPage = 50, $page = 1)
    {
        try {
            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            //get user
            $userID = BaseActiveController::getUserFromToken()->UserID;
            $user = SCUser::findOne($userID);

            $projectHasNotification = false;
            $projectNameHasNotification = null;
            $notificationData = [];

            //get projects the user belongs to
            $projectData = $user->projects;
            $projectArray = array_map(function ($model) {
                return $model->attributes;
            }, $projectData);
            $projectSize = count($projectArray);

            // check if login user is Engineer
            if ($user->UserAppRoleType != "Engineer") {

                PermissionsController::requirePermission('notificationsGet');

                //load data into array
                $notifications = [];
                $notifications["firstName"] = $user->UserFirstName;
                $notifications["lastName"] = $user->UserLastName;
                $notifications["notification"] = [];
                $responseArray = [];

                //loop projects to get data
                for ($i = 0; $i < $projectSize; $i++) {
                    $projectName = $projectArray[$i]["ProjectName"];

                    //check if the user associated with yorkDev
                    if ($projectName == "York Dev") {
                        $projectHasNotification = true;
                    }
                }

                if ($projectHasNotification) {
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
                    if ($page != null) {
                        $orderBy = 'SrvDTLT';
                        //pass query with pagination data to helper method
                        $paginationResponse = BaseActiveController::paginationProcessor($notificationData, $page, $listPerPage);
                        //use updated query with pagination caluse to get data
                        $data = $paginationResponse['Query']->orderBy($orderBy)
                            ->all();
                        $responseArray['pages'] = $paginationResponse['pages'];
                        $responseArray['notification'] = $data;
                    }
                }

                //send response
                $response = Yii::$app->response;
                $response->format = Response::FORMAT_JSON;
                $response->data = $responseArray;
                return $response;
            }
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
}