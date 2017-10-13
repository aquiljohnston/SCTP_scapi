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
use app\modules\v2\models\AssignedWorkQueueByMapGrid;
use app\modules\v2\models\AssignedWorkQueueBySection;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\WorkOrder;
use app\modules\v2\models\WorkQueue;
use app\modules\v2\models\StatusLookup;
use app\modules\v2\models\AvailableWorkOrderCGEByMapGridDetail;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\PermissionsController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Connection;

class DispatchController extends Controller 
{
    const DISPATCH_CGE_TYPE = "DISPATCH_CGE_TYPE";

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
					'get-available' => ['get'],
					'get-available-assets' => ['get'],
					'get-surveyors' => ['get'],
					'dispatch' => ['post'],
					'get-assigned' => ['get'],
					'get-assigned-assets' => ['get'],
					'unassign' => ['delete'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGetAvailable($mapGridSelected = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//get headers
			$headers = getallheaders();
			
			//set db
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			
			if($mapGridSelected != null)
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
					['like', 'AvailableWorkOrderCount', $filter],
					['like', 'Frequency', $filter],
					['like', 'Division', $filter],
					]);
				}
			}
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$data = $paginationResponse['Query']->orderBy($orderBy)
				->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray[$envelope] = $data;
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
	
	public function actionGetAvailableAssets($mapGridSelected, $sectionNumberSelected = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//set dbl
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			$orderBy = 'ComplianceEnd';
			$envelope = 'assets';
			$assetQuery = AvailableWorkOrder::find()
				->where(['MapGrid' => $mapGridSelected]);
			if($sectionNumberSelected !=null)
			{
				$assetQuery->andWhere(['SectionNumber' => $sectionNumberSelected]);
			}
			
			if($filter != null)
			{
				$assetQuery->andFilterWhere([
				'or',
				['like', 'InspectionType', $filter],
				['like', 'HouseNumber', $filter],
				['like', 'Street', $filter],
				['like', 'AptSuite', $filter],
				['like', 'City', $filter],
				['like', 'State', $filter],
				['like', 'Zip', $filter],
				['like', 'MeterNumber', $filter],
				['like', 'MapGrid', $filter],
				['like', 'ComplianceStart', $filter],
				['like', 'ComplianceEnd', $filter],
				['like', 'SectionNumber', $filter],
				]);
			}
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$data = $paginationResponse['Query']->orderBy($orderBy)
				->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray[$envelope] = $data;
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
	
	public function actionDispatch($dispatchType = null)
	{
		/*try
		{*/
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
			$responseData['dispatchAsset'] = [];
			$mapCount = 0;
			$sectionCount = 0;
			$assetCount = 0;
			
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
			//check if items exist to dispatch by asset, and get asset count
			if(array_key_exists('dispatchAsset', $data))
			{
				$assetCount = count($data['dispatchAsset']);
			}
			
			//process map dispatch
			for($i = 0; $i < $mapCount; $i++)
			{
			    if ($dispatchType != null) {
                    //calls helper method to process assingments
                    $results = self::processDispatch(
                        $data['dispatchMap'][$i]['AssignedUserID'],
                        $createdBy,
                        $data['dispatchMap'][$i]['MapGrid']
                    );
                } else if ($dispatchType == self::DISPATCH_CGE_TYPE) {
                    //calls helper method to process assingments
                    $results = self::processDispatch(
                        $data['dispatchMap'][$i]['AssignedUserID'],
                        $createdBy,
                        $data['dispatchMap'][$i]['MapGrid'],
                        null,
                        null,
                        self::DISPATCH_CGE_TYPE
                    );
                }
				$responseData['dispatchMap'][] = $results;
			}
			//process section dispatch
            if ($dispatchType == null) {
                for ($i = 0; $i < $sectionCount; $i++) {
                    //calls helper method to process assingments
                    $results = self::processDispatch(
                        $data['dispatchSection'][$i]['AssignedUserID'],
                        $createdBy,
                        $data['dispatchSection'][$i]['MapGrid'],
                        $data['dispatchSection'][$i]['SectionNumber']
                    );
                    $responseData['dispatchSection'][] = $results;
                }
            }
			//process asset dispatch
			for($i = 0; $i < $assetCount; $i++)
			{
			    if ($dispatchType == self::DISPATCH_CGE_TYPE) {
                    //calls helper method to process assingments
                    $results = self::processDispatch(
                        $data['dispatchAsset'][$i]['AssignedUserID'],
                        $createdBy,
                        null,
                        null,
                        $data['dispatchAsset'][$i]['WorkOrderID'],
                        self::DISPATCH_CGE_TYPE,
                        $data['dispatchAsset'][$i]['ScheduledDate']
                    );
                } else {
                    $results = self::processDispatch(
                        $data['dispatchAsset'][$i]['AssignedUserID'],
                        $createdBy,
                        null,
                        null,
                        $data['dispatchAsset'][$i]['WorkOrderID']
                    );
                }
				$responseData['dispatchAsset'][] = $results;
			}
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		/*}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }*/
	}
	
	public function actionGetAssigned($mapGridSelected = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			if($mapGridSelected != null)
			{
				$orderBy = 'SectionNumber';
				$envelope = 'sections';
				$assetQuery = AssignedWorkQueueBySection::find()
					->where(['MapGrid' => $mapGridSelected]);
			}
			else
			{
				$orderBy = 'ComplianceEnd';
				$envelope = 'mapGrids';
				$assetQuery = AssignedWorkQueueByMapGrid::find();
				
				if($filter != null)
				{
					$assetQuery->andFilterWhere([
					'or',
					['like', 'MapGrid', $filter],
					['like', 'ComplianceStart', $filter],
					['like', 'ComplianceEnd', $filter],
					['like', 'AssignedWorkOrderCount', $filter],
					['like', 'SearchString', $filter],
					]);
				}
			}
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$data = $paginationResponse['Query']->orderBy($orderBy)
				->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray[$envelope] = $data;
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
	
	public function actionGetAssignedAssets($mapGridSelected, $sectionNumberSelected = null, $filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			$orderBy = 'ComplianceEnd';
			$envelope = 'assets';
			$assetQuery = AssignedWorkQueue::find()
				->where(['MapGrid' => $mapGridSelected]);
			if($sectionNumberSelected !=null)
			{
				$assetQuery->andWhere(['SectionNumber' => $sectionNumberSelected]);
			}
			if($filter != null)
			{
				$assetQuery->andFilterWhere([
				'or',
				['like', 'InspectionType', $filter],
				['like', 'HouseNumber', $filter],
				['like', 'Street', $filter],
				['like', 'AptSuite', $filter],
				['like', 'City', $filter],
				['like', 'State', $filter],
				['like', 'Zip', $filter],
				['like', 'MeterNumber', $filter],
				['like', 'MapGrid', $filter],
				['like', 'ComplianceStart', $filter],
				['like', 'ComplianceEnd', $filter],
				['like', 'SectionNumber', $filter],
				['like', 'AssignedTo', $filter],
				]);
			}
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$data = $paginationResponse['Query']->orderBy($orderBy)
				->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray[$envelope] = $data;
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
			$responseData['unassignMap'] = [];
			$responseData['unassignSection'] = [];
			$responseData['unassignAsset'] = [];
			$mapCount = 0;
			$sectionCount = 0;
			$assetCount = 0;
			
			//check if items exist to unassign by map, and get map count
			if(array_key_exists('unassignMap', $data))
			{
				$mapCount = count($data['unassignMap']);
			}
			//check if items exist to unassign by section, and get section count
			if(array_key_exists('unassignSection', $data))
			{
				$sectionCount = count($data['unassignSection']);
			}
			//check if items exist to unassign by asset, and get asset count
			if(array_key_exists('unassignAsset', $data))
			{
				$assetCount = count($data['unassignAsset']);
			}
			
			//get assinged status code
			$assignedCode = self::statusCodeLookup('Assigned');
			
			//process unassignMap
			for($i = 0; $i < $mapCount; $i++)
			{
				$results = self::processUnassigned(
					$data['unassignMap'][$i]['AssignedUserID'],
					$data['unassignMap'][$i]['MapGrid']
				);
				$responseData['unassignMap'][] = $results;
			}
			
			//process unassignSection
			for($i = 0; $i < $sectionCount; $i++)
			{
				$results = self::processUnassigned(
					$data['unassignSection'][$i]['AssignedUserID'],
					$data['unassignSection'][$i]['MapGrid'],
					$data['unassignSection'][$i]['SectionNumber']
				);
				$responseData['unassignSection'][] = $results;
			}
			
			//process unassign unassignAsset
			for($i = 0; $i < $assetCount; $i++)
			{
				$results = self::processUnassigned(
					$data['unassignAsset'][$i]['AssignedUserID'],
					null,
					null,
					$data['unassignAsset'][$i]['WorkOrderID']
				);
				$responseData['unassignAsset'][] = $results;
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
	private static function processDispatch($userID, $createdBy, $mapGrid = null, $section = null, $workOrder = null, $dispatchType = null, $scheduledDate = null)
	{
            $results = [];

            //get status code for Assigned work
            $assignedCode = self::statusCodeLookup('Assigned');

        if ($dispatchType == null) {
            //build query to get work orders based on map grid and section(optional)
            if ($workOrder == null) {
                $workOrdersQuery = AvailableWorkOrder::find()
                    ->where(['MapGrid' => $mapGrid]);
                if ($section != null) {
                    $workOrdersQuery->andWhere(['SectionNumber' => $section]);
                }
            } else {
                $workOrdersQuery = AvailableWorkOrder::find()
                    ->where(['WorkOrderID' => $workOrder]);
            }
            $workOrders = $workOrdersQuery->all();
        } else {
            $workOrders = self::getCgeWorkOrders($mapGrid, $workOrder);
        }
		
		$workOrdersCount = count($workOrders);

		//loop work orders to assign
		for($i = 0; $i < $workOrdersCount; $i++)
		{
			try{
				$successFlag = 0;

				//check for existing records
				$assignedWork = WorkQueue::find()
					->where(['WorkOrderID' => $workOrders[$i]->ID])
					->andWhere(['<>', 'WorkQueueStatus', 102])
					->count();
				//if no record exist create one
				if($assignedWork < 1)
				{
					$newAssignment = new WorkQueue;
					$newAssignment->CreatedBy = $createdBy;
					$newAssignment->CreatedDate = date(BaseActiveController::DATE_FORMAT);
					$newAssignment->AssignedUserID = $userID;
					$newAssignment->WorkQueueStatus = $assignedCode;
                    if ($dispatchType != self::DISPATCH_CGE_TYPE) {
                        $newAssignment->WorkOrderID = $workOrders[$i]->WorkOrderID;
                        $newAssignment->SectionNumber = $workOrders[$i]->SectionNumber;
                    }else{
                        $newAssignment->WorkOrderID = $workOrders[$i]->ID;
                    }

                    if ($dispatchType == self::DISPATCH_CGE_TYPE)
                        $newAssignment->ScheduledDispatchDate = date(BaseActiveController::DATE_FORMAT,strtotime($scheduledDate));

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
			}
			catch(\Exception $e)
			{
				BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $workOrders[$i]);
			}
			if ($dispatchType != self::DISPATCH_CGE_TYPE) {
                $results[] = [
                    'MapGrid' => $workOrders[$i]->MapGrid,
                    'AssignedUserID' => $userID,
                    'SectionNumber' => $workOrders[$i]->SectionNumber,
                    'WorkOrderID' => $workOrders[$i]->WorkOrderID,
                    'SuccessFlag' => $successFlag
                ];
            }else{
                $results[] = [
                    'MapGrid' => $workOrders[$i]->MapGrid,
                    'AssignedUserID' => $userID,
                    'WorkOrderID' => $workOrders[$i]->ID,
                    'ScheduledDispatchDate' => $scheduledDate,
                    'SuccessFlag' => $successFlag
                ];
            }
		}
		if($workOrdersCount == 0)
		{
			$results[] = [
				'MapGrid' => $mapGrid,
				'AssignedUserID' => $userID,
				'SectionNumber' => $section,
				'WorkOrderID' => $workOrder,
				'SuccessFlag' => 1
			];
		}

		return $results;
	}
	
	private static function processUnassigned($userID, $mapGrid = null, $section = null, $workOrder = null)
	{
		$successFlag = 0;
		try{
			$connection = BaseActiveRecord::getDb();
			$processJSONCommand = $connection->createCommand("EXECUTE spUnassignWO :AssignedUserID,:MapGrid, :SectionNum , :WorkOrderID");
			$processJSONCommand->bindParam(':AssignedUserID', $userID,  \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':MapGrid', $mapGrid,  \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':SectionNum', $section,  \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':WorkOrderID', $workOrder,  \PDO::PARAM_INT);
			$processJSONCommand->execute();
			$successFlag = 1;
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], [
			'AssignedUserID' => $userID,
			'MapGrid' => $mapGrid,
			'SectionNumber' => $section,
			'WorkOrderID' => $workOrder,
			'SuccessFlag' => $successFlag
			]);
		}
		
		//build response format
		return [
			'AssignedUserID' => $userID,
			'MapGrid' => $mapGrid,
			'SectionNumber' => $section,
			'WorkOrderID' => $workOrder,
			'SuccessFlag' => $successFlag
		];
	}
	
	public static function unassignUser($userID, $client)
	{
		try
		{
			//set db target
            BaseActiveRecord::setClient($client);
			//delete all work queues that are not complete(status 102)
			WorkQueue::deleteAll(['and', ['AssignedUserID' => $userID], ['not', ['WorkQueueStatus' => 102]]]);
			return 1;
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, $client, [
			'AssignedUserID' => $userID,
			'MapGrid' => $client,
			'Comment' => 'Failed to delete user work queues.'
			]);
			return 0;
		}
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

	//helper method gets cge work orders from vWebManagementCGIByMapGridDetail
	private static function getCgeWorkOrders($mapGrid = null, $workOrder = null){
        //build query to get work orders based on map grid
        if ($workOrder == null) {
            $workOrdersQuery = AvailableWorkOrderCGEByMapGridDetail::find()
                ->where(['MapGrid' => $mapGrid]);

        } else {
            $workOrdersQuery = AvailableWorkOrderCGEByMapGridDetail::find()
                ->where(['ID' => $workOrder]);
        }
        $workOrders = $workOrdersQuery->all();

        return $workOrders;
    }
}