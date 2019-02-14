<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\MileageEntry;
use app\modules\v3\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * MileageEntryController implements the CRUD actions for MileageEntry model.
 */
class MileageEntryController extends BaseActiveController
{
    public $modelClass = 'app\modules\v3\models\MileageEntry'; 

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'deactivate' => ['put'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use ViewMethodNotAllowed;
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	/**
     * Create New Mileage Entry and Activity in CT DB
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreateTask()
    {
        try {
            //set db target
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			//RBAC permissions check
			PermissionsController::requirePermission('createTaskEntry');

			$successFlag = 0;
			$warningMessage = '';
			
            //get body data
            $body = file_get_contents("php://input");
            $data = json_decode($body, true);
			
			// set up db connection
			$connection = BaseActiveRecord::getDb();
			$processJSONCommand = $connection->createCommand("EXECUTE spAddMileage :MileageCardID , :Date, :TotalMiles, :MileageType, :CreatedByUserName");
			$processJSONCommand->bindParam(':MileageCardID', $data['MileageCardID'], \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':Date', $data['Date'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':TotalMiles', $data['TotalMiles']);
			$processJSONCommand->bindParam(':MileageType', $data['MileageType'], \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':CreatedByUserName', $data['CreatedByUserName'], \PDO::PARAM_STR);
			$processJSONCommand->execute();
			$successFlag = 1;			
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], [
                'MileageCardID' => $data['MileageCardID'],
                'Date' => $data['Date'],
                'CreatedByUserName' => $data['CreatedByUserName'],
                'SuccessFlag' => $successFlag
            ]);
			$warningMessage = 'An error occurred.';
        }
		
		//build response format
		$dataArray =  [
			'MileageCardID' => $data['MileageCardID'],
			'SuccessFlag' => $successFlag,
			'WarningMessage' => $warningMessage,
		];
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->data = $dataArray;
		
		return $response;
    }
	
	public function actionDeactivate()
	{
		try{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryDeactivate');
			
			//capture put body
			$put = file_get_contents("php://input");
			$entries = json_decode($put, true)['entries'];
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get current user to set deactivated by
			$username = BaseActiveController::getUserFromToken()->UserName;
			
			foreach ($entries as $entry) {
				//SPROC has no return so just in case we need a flag.
				$success = 0;
				//add variables to avoid pass by reference error
				$taskString = json_encode($entry['taskName']);
				$day = array_key_exists('day', $entry) ? $entry['day'] : null;
				//call SPROC to deactivateTimeEntry
				try {
					$connection = BaseActiveRecord::getDb();
					//TODO may want to move the transaction outside of the loop to allow full rollback of the request
					$transaction = $connection->beginTransaction(); 
					$timeCardCommand = $connection->createCommand("EXECUTE spDeactivateMileageEntry :PARAMETER1,:PARAMETER2,:PARAMETER3,:PARAMETER4");
					$timeCardCommand->bindParam(':PARAMETER1', $entry['mileageCardID'], \PDO::PARAM_INT);
					$timeCardCommand->bindParam(':PARAMETER2', $taskString, \PDO::PARAM_STR);
					$timeCardCommand->bindParam(':PARAMETER3', $day, \PDO::PARAM_STR);
					$timeCardCommand->bindParam(':PARAMETER4', $username, \PDO::PARAM_STR);
					$timeCardCommand->execute();
					$transaction->commit();
					$success = 1;
				} catch (Exception $e) {
					$transaction->rollBack();
				}
			}
			//TODO could update response to be formated with success flag per entry if we keep individual transactions
			$response->data = $success; 
			return $response;
		} catch (ForbiddenHttpException $e) {
			throw new ForbiddenHttpException;
		} catch(\Exception $e) {
			throw new \yii\web\HttpException(400);
		}
	}
}
