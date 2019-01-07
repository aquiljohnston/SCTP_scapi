<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\MileageEntry;
use app\modules\v2\models\SCUser;
use app\modules\v2\controllers\BaseActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * MileageEntryController implements the CRUD actions for MileageEntry model.
 */
class MileageEntryController extends BaseActiveController
{
    public $modelClass = 'app\modules\v2\models\MileageEntry'; 

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
	
	use UpdateMethodNotAllowed;
	use DeleteMethodNotAllowed;
	
	public function actionDeactivate()
	{
		try
		{
			//set db target
			MileageEntry::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('mileageEntryDeactivate');
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//get current user by auth token
			$deactivatedBy =  self::getUserFromToken()->UserName;
			//parse json
			$entryIDs = $data["entryArray"];
			
			//get mileage entries
			foreach($entryIDs as $id)
			{
				$approvedEntries[]= MileageEntry::findOne($id);
			}
			
			//try to approve time cards
			try
			{
				//create transaction
				$connection = BaseActiveRecord::getDb();
				$transaction = $connection->beginTransaction(); 
			
				foreach($approvedEntries as $entry)
				{
					$entry-> MileageEntryActiveFlag = 0;
					$entry-> MileageEntryModifiedDate = Parent::getDate();
					$entry-> MileageEntryModifiedBy = $deactivatedBy;
					$entry-> update();
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $approvedEntries; 
				return $response;
			}
			//if transaction fails rollback changes and send error
			catch(Exception $e)
			{
				$transaction->rollBack();
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
				return $response;
			}
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
