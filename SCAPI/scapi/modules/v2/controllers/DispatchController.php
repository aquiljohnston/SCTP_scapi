<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\authentication\TokenAuth;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\AvailableWorkOrder;
use app\modules\v2\models\AvailableWorkOrderByMapGrid;
use app\modules\v2\models\AvailableWorkOrderBySection;
use app\modules\v2\models\AssignedWorkQueue;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\WorkOrder;
use app\modules\v2\models\WorkQueue;
use app\modules\v2\models\StatusLookup;
use app\modules\v2\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Connection;

class DispatchController extends Controller 
{

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
					'get' => ['get'],
					'get-surveyors' => ['get'],
					'dispatch' => ['post'],
					'get-assigned' => ['get'],
					'unassign' => ['delete'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGet($mapGridSelected = null, $sectionNumberSelected = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			if($mapGridSelected != null && $sectionNumberSelected !=null)
			{
				$orderBy = 'ComplianceEnd';
				$envelope = 'assets';
				$assetQuery = AvailableWorkOrder::find()
					->where(['MapGrid' => $mapGridSelected])
					->andWhere(['SectionNumber' => $sectionNumberSelected]);
				
				/*if($filter != null)
				{
					$assetQuery->andFilterWhere([
					'or',
					['like', 'MapGrid', $filter],
					['like', 'ComplianceStart', $filter],
					['like', 'ComplianceEnd', $filter],
					['like', 'InspectionAttemptCounter', $filter],
					['like', 'AvailableWorkOrderCount', $filter]
					]);
				}*/
			}
			elseif($mapGridSelected != null)
			{
				$orderBy = 'SectionNumber';
				$envelope = 'sections';
				$assetQuery = AvailableWorkOrderBySection::find()
					->where(['MapGrid' => $mapGridSelected]);
			}
			else
			{
				$orderBy = 'ComplianceEnd';
				$envelope = 'mapGrids';
				$assetQuery = AvailableWorkOrderByMapGrid::find();
				
				if($filter != null)
				{
					$assetQuery->andFilterWhere([
					'or',
					['like', 'MapGrid', $filter],
					['like', 'ComplianceStart', $filter],
					['like', 'ComplianceEnd', $filter],
					['like', 'InspectionAttemptCounter', $filter],
					['like', 'AvailableWorkOrderCount', $filter]
					]);
				}
			}
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$assets = $paginationResponse['Query']->orderBy($orderBy)
				->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray[$envelope] = $assets;
			}
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}

    public function actionGetSurveyors($filter = null)
    {
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
				
			$userQuery = SCUser::find()
				->select(['UserID', "concat(UserLastName, ', ', UserFirstName) as Name", 'UserName'])
				->where(['UserActiveFlag' => 1])
				->andWhere(['<>', 'UserAppRoleType', 'Admin']);
			
			if($filter != null)
			{
				$userQuery->andFilterWhere([
				'or',
				['like', 'UserName', $filter],
				['like', 'UserFirstName', $filter],
				['like', 'UserLastName', $filter],
				]);
			}
			
			$users = $userQuery->asArray()
				->all();
			
			$responseArray['users'] = $users;
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
    }
	
	public function actionDispatch()
	{
		try
		{
			//get client headers
			$headers = getallheaders();
			// get created by
			$createdBy = BaseActiveController::getClientUser($headers['X-Client'])->UserID;
			//set db
			BaseActiveRecord::setClient($headers['X-Client']);
			
			//get post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			//create response format
			$responseData = [];
			$responseData['dispatchMap'] = [];
			$responseData['dispatchSection'] = [];
			$mapCount = 0;
			$sectionCount = 0;
			
			//check if items exist to dispatch by map, and get map count
			if(array_key_exists('dispatchMap', $data))
			{
				$mapCount = count($data['dispatchMap']);
			}
			//check if items exist to dispatch by section, and get section count
			if(array_key_exists('dispatchSection', $data))
			{
				$sectionCount = count($data['dispatchSection']);
			}
			
			//process map dispatch
			for($i = 0; $i < $mapCount; $i++)
			{
				//calls helper method to process assingments
				$results = self::processDispatch(
					$data['dispatchMap'][$i]['AssignedUserID'],
					$createdBy,
					$data['dispatchMap'][$i]['MapGrid']
				);
				$responseData['dispatchMap'][] = $results;
			}
			//process section dispatch
			for($i = 0; $i < $sectionCount; $i++)
			{
				//calls helper method to process assingments
				$results = self::processDispatch(
					$data['dispatchSection'][$i]['AssignedUserID'],
					$createdBy,
					$data['dispatchSection'][$i]['MapGrid'],
					$data['dispatchSection'][$i]['SectionNumber']
				);
				$responseData['dispatchSection'][] = $results;
			}
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGetAssigned($filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			
			$assetQuery = AssignedWorkQueue::find()->where(['CompletedFlag' => 0]);
			
			// if($filter != null)
			// {
				// $assetQuery->andFilterWhere([
				// 'or',
				// ['like', 'Division', $filter],
				// ['like', 'WorkCenter', $filter],
				// ['like', 'SurveyType', $filter],
				// ['like', 'FLOC', $filter],
				// ['like', 'Notification ID', $filter],
				// ['like', 'ComplianceDueDate', $filter],
				// ['like', 'SAP Released', $filter],
				// ['like', 'ComplianceYearMonth', $filter],
				// ['like', 'PreviousServices', $filter],
				// ]);
			// }
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$assets = $paginationResponse['Query']->orderBy('ComplianceEnd')
				->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray['assets'] = $assets;
			}
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionUnassign()
	{
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true);
			//create response format
			$responseData = [];
			
			//count number of items to unassign
			$unassignCount = count($data['data']);
			
			//get assinged status code
			$assignedCode = self::statusCodeLookup('Assigned');
			
			//process unassign
			//nested for loop needed because map grid does not exist in work queue
			//planned to iterate on this design and change to work order id
			for($i = 0; $i < $unassignCount; $i++)
			{
				$workOrders = WorkOrder::find()
					->where(['MapGrid' => $data['data'][$i]['MapGrid']])
					->all();
				$workOrdersCount = count($workOrders);
				for($j = 0; $j < $workOrdersCount; $j++)
				{
					$successFlag = 0;
					$workQueue = WorkQueue::find()
						->where(['WorkOrderID' => $workOrders[$j]->ID])
						->andWhere(['AssignedUserID' => $data['data'][$i]['AssignedUserID']])
						->andWhere(['WorkQueueStatus' => $assignedCode])
						->one();
					if($workQueue != null)
					{
						if($workQueue->delete())
						{
							$successFlag = 1;
						}
						$responseData[$data['data'][$i]['AssignedUserID']][$data['data'][$i]['MapGrid']] = [
							'MapGrid' => $data['data'][$i]['MapGrid'],
							'AssignedUserID' => $data['data'][$i]['AssignedUserID'],
							'WorkOrderID' => $workOrders[$j]->ID,
							'SuccessFlag' => $successFlag
						];
					}
				}
			}
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
	
	/*Helper method that gets all work orders associated with given mapGrid/section.
	**Then checks for existing assigned work queue records and removes any from 
	**results that already exist. Finally creates new records and returns results.
	*/
	private static function processDispatch($userID, $createdBy, $mapGrid, $section = null)
	{
		$results = [];
		
		//get status code for Assigned work			
		$assignedCode = self::statusCodeLookup('Assigned');
		
		//build query to get work orders based on map grid and section(optional)
		$workOrdersQuery = WorkOrder::find()
			->where(['MapGrid' => $mapGrid]);
		if($section != null)
		{
			$workOrdersQuery->andWhere(['SectionNumber' => $section]);
		}
		$workOrders = $workOrdersQuery->all();
		
		$workOrdersCount = count($workOrders);
		
		//loop work orders to assign
		for($i = 0; $i < $workOrdersCount; $i++)
		{
			try{
				$successFlag = 0;
				
				//check for existing records
				$assignedWork = WorkQueue::find()
					->where(['WorkOrderID' => $workOrders[$i]['ID']])
					->andWhere(['AssignedUserID' => $userID])
					->count();
				//if no record exist create one
				if($assignedWork < 1)
				{				
					$newAssignment = new WorkQueue;
					$newAssignment->CreatedBy = $createdBy;
					$newAssignment->CreatedDate = BaseActiveController::getDate();
					$newAssignment->WorkOrderID = $workOrders[$i]->ID;
					$newAssignment->AssignedUserID = $userID;
					$newAssignment->WorkQueueStatus = $assignedCode;
					$newAssignment->SectionNumber = $section;
					if($newAssignment->save())
					{
						$successFlag = 1;
					}
					else
					{
						throw BaseActiveController::modelValidationException($newAssignment);
					}
				}
				else
				{
					$successFlag = 1;
				}
				//add to results
				if($section != null)
				{
					$results[$userID][$mapGrid][$section][] = [
						'MapGrid' => $mapGrid,
						'AssignedUserID' => $userID,
						'SectionNumber' => $section,
						'WorkOrderID' => $workOrders[$i]->ID,
						'SuccessFlag' => $successFlag
					];
				}
				else
				{
					$results[$userID][$mapGrid][] = [
						'MapGrid' => $mapGrid,
						'AssignedUserID' => $userID,
						'WorkOrderID' => $workOrders[$i]->ID,
						'SuccessFlag' => $successFlag
					];
				}
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $workOrders[$i]);
				$results[] = [
						'MapGrid' => $mapGrid,
						'AssignedUserID' => $userID,
						'WorkOrderID' => $workOrders[$i]->ID,
						'SuccessFlag' => $successFlag
				];
			}
		}
		
		return $results;
	}
	
	//helper method gets status code based on StatusDescription
	private static function statusCodeLookup($description)
	{
		$statusLookup = StatusLookup::find()
				->select('StatusCode')
				->where(['StatusType' => 'Dispatch'])
				->andWhere(['StatusDescription' => $description])
				->one();
		$statusCode = $statusLookup['StatusCode'];
		return $statusCode;
	}
}