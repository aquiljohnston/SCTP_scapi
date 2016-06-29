<?php

namespace app\controllers;

use Yii;
use app\models\Equipment;
use app\models\SCUser;
use app\models\ProjectUser;
use app\models\DailyEquipmentCalibrationVw;
use yii\filters\VerbFilter;
use yii\web\Response;

class EquipmentController extends BaseActiveController
{
	public $modelClass = 'app\models\Equipment'; 
	public $equipment;

	/**
	 * Activates VerbFilter behaviour
	 * See documentation on behaviours at http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html
	 * @return array An array containing behaviours
	 */
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json  Header
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'accept-equipment'  => ['put'],
					'get-equipment-by-manager' => ['get'],
					'view-all-by-user-by-project' => ['get'],
					'equipment-view' => ['get'],
                ],  
            ];
		return $behaviors;	
	}

	/**
	 * Unsets the view and update actions to prevent security holes.
	 * @return array An array containing parent's actions with view and update removed
	 */
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['update']);
		return $actions;
	}

	/**
	 * Finds a specific Equipment based on ID and returns it to the client. Otherwise
	 * returns a 404.
	 *
	 * @param $id int ID of the Equipment to view
	 * @return Response
	 * @throws \yii\web\HttpException 400 when Exception thrown
	 */
	public function actionView($id)
    {
		// RBAC permission check
		PermissionsController::requirePermission('equipmentView');

		try
		{
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//set db target
			$headers = getallheaders();
			Equipment::setClient($headers['X-Client']);
				
			if($equipment = Equipment::findOne($id))
			{
				$response->data = $equipment;
				$response->setStatusCode(200);
			}
			else
			{
				$response->setStatusCode(404);
			}
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}		
	}

	/**
	 * Creates an Equipment based on POST input and saves it. If it can not save it returns at 400 status code.
	 *
	 * @return Response
	 * @throws \yii\web\HttpException When saving the model fails
	 */
	public function actionCreate()
	{
		// RBAC permission check
		PermissionsController::requirePermission('equipmentCreate');

		try
		{
			//set db target
			$headers = getallheaders();
			Equipment::setClient($headers['X-Client']);
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new Equipment(); 
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create date
			$model->EquipmentCreateDate = Parent::getDate();
			
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
	 * Updates an Equipment model with JSON data from POST.
	 *
	 * @param $id int The ID of the model to update
	 * @return Response JSON object of updated model.
	 * @throws \yii\web\HttpException
	 */
	public function actionUpdate($id)
	{
		// RBAC permission check
		PermissionsController::requirePermission('equipmentUpdate');

		try
		{
			//set db target
			$headers = getallheaders();
			Equipment::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			$model = Equipment::findOne($id);
			$currentProject = $model->EquipmentProjectID;
			
			$model->attributes = $data;  
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->EquipmentModifiedDate = Parent::getDate();
			
			//TODO set flag to "Pending" when the project information is changed.
			if($model->EquipmentProjectID != $currentProject)
			{
				$model-> EquipmentAcceptedFlag = "Pending";
				$model-> EquipmentAcceptedBy = "Pending";
			}
			
			if($model-> update())
			{
				$response->setStatusCode(200);
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

	public function actionGetEquipment() {
		// Will combine actionViewAllByUserByProject and actionEquipmentView
		// after rbac is complete
	}

	/**
	 * Gets all the equipment for the project the user belongs to.
	 * Used by project managers and supervisors.
	 *
	 * @param $userID int ID of the User model to view by
	 * @return Response a json containing all equipment for projects that a user is associated with
	 * @throws \yii\web\HttpException When an exception is thrown.
	 */
	public function actionViewAllByUserByProject($userID)
	{
		// RBAC permission check
		PermissionsController::requirePermission('');

		try{
			//set db target
			$headers = getallheaders();
			DailyEquipmentCalibrationVw::setClient($headers['X-Client']);
			ProjectUser::setClient($headers['X-Client']);

			//format response
			$response = Yii::$app->response;
			$response-> format = Response::FORMAT_JSON;

			//get user project relations array
			$projects = ProjectUser::find()
				->where("ProjUserUserID = $userID")
				->all();
			$projectsSize = count($projects);

			//response array of equipments
			$equipmentArray = [];

			//loop user project array get all equipment WHERE equipmentProjectID is equal
			for($i=0; $i < $projectsSize; $i++)
			{
				$projectID = $projects[$i]->ProjUserProjectID;

				$equipment = DailyEquipmentCalibrationVw::find()
					->where(['EquipmentProjectID' => $projectID])
					->all();
				$equipmentArray = array_merge($equipmentArray, $equipment);
			}

			$response->data = $equipmentArray;
			$response->setStatusCode(200);
			return $response;

		} catch(\Exception $e){
			throw new \yii\web\HttpException(400);
		}
	}

	/**
	 * View all equipment including daily calibration status
	 *
	 * @return Response db view for equipment index
	 * @throws \yii\web\HttpException When an exception is thrown
	 */
	public function actionEquipmentView()
	{
		// RBAC permission check
		PermissionsController::requirePermission('equipmentView');

		try
		{
			//set db target
			$headers = getallheaders();
			DailyEquipmentCalibrationVw::setClient($headers['X-Client']);
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			if($equipArray = DailyEquipmentCalibrationVw::find()->all())
			{
				$equipData = array_map(function ($model) {return $model->attributes;},$equipArray);
				$response->data = $equipData;
				$response->setStatusCode(200);
			}
			else
			{
				$response->setStatusCode(404);
			}
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}

	/**
	 * Accepts one or more Equipments.
	 *
	 * Changes the flag on each Equipment that it receives to indicate that they are accepted.
	 *
	 * @return Response
	 * @throws \yii\web\HttpException
	 */
	public function actionAcceptEquipment()
	{
		// RBAC permission check
		PermissionsController::requirePermission('acceptEquipment');

		try
		{
			//set db target
			$headers = getallheaders();
			Equipment::setClient($headers['X-Client']);
			SCUser::setClient($headers['X-Client']);
			
			//capture put body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//parse json
			$acceptedBy = $data["acceptedByID"];
			$equipmentIDs = $data["equipmentIDArray"];
			
			//get equipment
			foreach($equipmentIDs as $id)
			{
				$approvedEquipment[]= Equipment::findOne($id);
			}
			
			//get user's name by ID
			if ($user = SCUser::findOne(['UserID'=>$acceptedBy]))
			{
				$fname = $user->UserFirstName;
				$lname = $user->UserLastName;
				$acceptedBy = $lname.", ".$fname;
			}
			
			//try to accept equipment
			try
			{
				//create transaction
				$connection = \Yii::$app->db;
				$transaction = $connection->beginTransaction(); 
			
				foreach($approvedEquipment as $equipment)
				{
					$equipment-> EquipmentAcceptedFlag = "Yes";
					$equipment-> EquipmentAcceptedBy = $acceptedBy;
					$equipment-> update();
				}
				$transaction->commit();
				$response->setStatusCode(200);
				$response->data = $approvedEquipment; 
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