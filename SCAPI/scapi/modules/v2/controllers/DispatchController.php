<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\constants\Constants;
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
use yii\db\Query;
use yii\web\NotFoundHttpException;

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
					'get-available' => ['get'],
					'get-available-assets' => ['get'],
					'get-surveyors' => ['get'],
					'dispatch' => ['post'],
					'get-assigned' => ['get'],
					'get-assigned-assets' => ['get'],
					'unassign' => ['delete'],
					'get-dual-dispatch' => ['get'],
                ],
            ];
		return $behaviors;	
	}

	public function actionGetAvailable($mapGridSelected = null, $inspectionType = null, $billingCode = null, $officeName = null,
		$filter = null, $listPerPage = 10, $page = 1, $sortField = 'ComplianceEnd', $sortOrder = 'ASC', $dateRange = null)
	{
		try{
			//get headers
			$client = getallheaders()['X-Client'];
			//set db
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchGetAvailable', $client);
			
			$responseArray = [];
			$divisionFlag = self::getDivisionFlag();
			
			if($mapGridSelected != null){
				$orderBy = 'SectionNumber';
				$envelope = 'sections';
				$assetQuery = AvailableWorkOrderBySection::find()
					->where(['MapGrid' => $mapGridSelected]);
					
				if($inspectionType != null){
					$assetQuery->andWhere(['InspectionType' => $inspectionType]);
				}
				if($billingCode != null){
					$assetQuery->andWhere(['BillingCode' => $billingCode]);
				}
				if($officeName != null){
					$assetQuery->andWhere(['OfficeName' => $officeName]);
				}
			}else{
				$orderBy = "$sortField $sortOrder";
				$envelope = 'mapGrids';
				$assetQuery = AvailableWorkOrderByMapGrid::find();
				
				if($dateRange != null){
					$explodedDateRange = explode(' ', $dateRange);
					$startDate = $explodedDateRange[0];
					$endDate = $explodedDateRange[2];
					$assetQuery->andWhere(['or',
						['between', 'ComplianceStart', $startDate, $endDate],
						['between', 'ComplianceEnd', $startDate, $endDate],
					]);
				}

				//handle filter
				if($filter != null ){
					//url decode filter value
					$filter = urldecode($filter);
					//explode by delimiter to allow for multi search
					$delimiter = ',';
					$filterArray = explode($delimiter, $filter);

					//loop for multi search
					for($i = 0; $i < count($filterArray); $i++){
						//remove leading space from filter string
						$trimmedFilter = trim($filterArray[$i]);
						$filterQueryArray = ([
							'or',
							['like', 'MapGrid', $trimmedFilter],
							['like', 'ComplianceStart', $trimmedFilter],
							['like', 'ComplianceEnd', $trimmedFilter],
							['like', 'AvailableWorkOrderCount', $trimmedFilter],
							['like', 'Division', $trimmedFilter],
							['like', 'InspectionType', $trimmedFilter],
							['like', 'BillingCode', $trimmedFilter],
							['like', 'OfficeName', $trimmedFilter]
						]);
						$assetQuery->andFilterWhere($filterQueryArray);
					}
				}
				
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//add pagination data to response data
				$responseArray['pages'] = $paginationResponse['pages'];	
				//set asset query to returned value with added pagination clause
				$assetQuery = $paginationResponse['Query'];
			}
			
			//add order by to query and get data
			$data = $assetQuery->orderBy($orderBy)->all();
			$responseArray['divisionFlag'] = $divisionFlag;
			$responseArray[$envelope] = $data;
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveWebErrorJson('actionGetAvailable', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGetAvailableAssets($mapGridSelected, $sectionNumberSelected = null, $filter = null, $listPerPage = 10, $page = 1,
		$inspectionType = null, $billingCode = null, $officeName = null)
	{
		try{
			//set dbl
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchGetAvailableAssets', $client);
			
			$responseArray = [];
			$orderBy = 'ComplianceEnd';
			$envelope = 'assets';

			//handle null or multiple inspection types
			if($inspectionType != null){
				//handle potential multiple inspection types
				$inspectionTypeFilter = ['or',
				['InspectionType' => $inspectionType]];
				$inspectionTypeArray = explode(',', $inspectionType);
				$inspectionTypeCount = count($inspectionTypeArray);
				for($i = 0; $i < $inspectionTypeCount; $i++){
					$inspectionTypeFilter[] = ['InspectionType' => $inspectionTypeArray[$i]];
				}
			}else{
				//if null just use inspection type
				$inspectionTypeFilter = ['InspectionType' => $inspectionType];
			}
			
			$assetQuery = AvailableWorkOrder::find()
				->where(['MapGrid' => $mapGridSelected])
				->andWhere($inspectionTypeFilter);
			
			if($billingCode != null) $assetQuery->andWhere(['BillingCode' => $billingCode]);
			if($officeName != null) $assetQuery->andWhere(['OfficeName' => $officeName]);
			if($sectionNumberSelected != null) $assetQuery->andWhere(['SectionNumber' => $sectionNumberSelected]);
			
			if($filter != null){
				$assetQuery->andFilterWhere([
				'or',
				['like', 'InspectionType', $filter],
				['like', 'BillingCode', $filter],
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
                ['like', 'Address', $filter],
                ['like', 'OfficeName', $filter],
				]);
			}
			
			if($page != null){
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//use updated query with pagination caluse to get data
				$data = $paginationResponse['Query']->orderBy($orderBy)->all();
				$responseArray['pages'] = $paginationResponse['pages'];
				$responseArray[$envelope] = $data;
			}
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}
        catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveWebErrorJson('actionGetAvailableAssets', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}

    public function actionGetSurveyors($filter = null)
    {
		try
		{
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchGetSurveyors', $client);
				
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
			
			$users = $userQuery
				->orderBy(['UserLastName'=>SORT_ASC, 'UserFirstName'=>SORT_ASC])
				->asArray()
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
			BaseActiveController::archiveWebErrorJson('actionGetSurveyors', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
    }
	
	public function actionDispatch()
	{
		try
		{
			//get client headers
			$client = getallheaders()['X-Client'];
			// get created by
			$user = BaseActiveController::getClientUser($client);
			$createdBy = $user->UserID;
			$username = $user->UserName;
			//set db
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatch', $client);
			
			//get post data
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Dispatch', $username, $client);
			
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
				//process map dispatch
				for($i = 0; $i < $mapCount; $i++)
				{
					//calls helper method to process assingments
					$results = self::processDispatch(
						$data['dispatchMap'][$i]['AssignedUserID'],
						$createdBy,
						array_key_exists('IsCge', $data['dispatchMap'][$i]) ? $data['dispatchMap'][$i]['IsCge'] : false,
						$data['dispatchMap'][$i]['MapGrid'],
						null,
						null,
						//hack override for cge dispatch
						array_key_exists('ScheduledDate', $data['dispatchMap'][$i]) ? $data['dispatchMap'][$i]['ScheduledDate'] : null,
						//pass inspection type and billing code if available
						array_key_exists('InspectionType', $data['dispatchMap'][$i]) ? $data['dispatchMap'][$i]['InspectionType'] : null,
						array_key_exists('BillingCode', $data['dispatchMap'][$i]) ? $data['dispatchMap'][$i]['BillingCode'] : null,
						array_key_exists('OfficeName', $data['dispatchMap'][$i]) ? $data['dispatchMap'][$i]['OfficeName'] : null
					);
					$responseData['dispatchMap'][] = $results;
				}
			}
			//check if items exist to dispatch by section, and get section count
			if(array_key_exists('dispatchSection', $data))
			{
				$sectionCount = count($data['dispatchSection']);
				//process section dispatch
				for ($i = 0; $i < $sectionCount; $i++) {
                    //calls helper method to process assingments
                    $results = self::processDispatch(
                        $data['dispatchSection'][$i]['AssignedUserID'],
                        $createdBy,
						array_key_exists('IsCge', $data['dispatchSection'][$i]) ? $data['dispatchSection'][$i]['IsCge'] : false,
                        $data['dispatchSection'][$i]['MapGrid'],
                        $data['dispatchSection'][$i]['SectionNumber'],
						null,
						null,
						//pass inspection type and billing code if available
						array_key_exists('InspectionType', $data['dispatchSection'][$i]) ? $data['dispatchSection'][$i]['InspectionType'] : null,
						array_key_exists('BillingCode', $data['dispatchSection'][$i]) ? $data['dispatchSection'][$i]['BillingCode'] : null,
						array_key_exists('OfficeName', $data['dispatchSection'][$i]) ? $data['dispatchSection'][$i]['OfficeName'] : null
                    );
                    $responseData['dispatchSection'][] = $results;
                }
			}
			//check if items exist to dispatch by asset, and get asset count
			if(array_key_exists('dispatchAsset', $data))
			{
				$assetCount = count($data['dispatchAsset']);
				//process asset dispatch
				for($i = 0; $i < $assetCount; $i++)
				{
					//calls helper method to process assingments
					$results = self::processDispatch(
						$data['dispatchAsset'][$i]['AssignedUserID'],
						$createdBy,
						array_key_exists('IsCge', $data['dispatchAsset'][$i]) ? $data['dispatchAsset'][$i]['IsCge'] : false,
						null,
						array_key_exists('SectionNumber',$data['dispatchAsset'][$i]) ? $data['dispatchAsset'][$i]['SectionNumber'] : null,
						$data['dispatchAsset'][$i]['WorkOrderID'],
						array_key_exists('ScheduledDate',$data['dispatchAsset'][$i]) ? $data['dispatchAsset'][$i]['ScheduledDate'] : null
					);
					$responseData['dispatchAsset'][] = $results;
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
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionGetAssigned($mapGridSelected = null, $inspectionType = null, $billingCode = null, $officeName = null,
		$filter = null, $listPerPage = 10, $page = 1, $sortField = 'ComplianceEnd', $sortOrder = 'ASC', $dateRange = null)
	{
		try{
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchGetAssigned', $client);
			
			$responseArray = [];
			if($mapGridSelected != null){
				$orderBy = 'SectionNumber';
				$envelope = 'sections';
				$assetQuery = AssignedWorkQueueBySection::find()
					->where(['MapGrid' => $mapGridSelected]);
					
				if($inspectionType != null){
					$assetQuery->andWhere(['InspectionType' => $inspectionType]);
				}
				if($billingCode != null){
					$assetQuery->andWhere(['BillingCode' => $billingCode]);
				}
				if($officeName != null){
					$assetQuery->andWhere(['OfficeName' => $officeName]);
				}
			}else{
				$orderBy = "$sortField $sortOrder";
				$envelope = 'mapGrids';
				
				$assetQuery = AssignedWorkQueueByMapGrid::find()
					->select([
						'MapGrid',
						'AssignedUser',
						'ComplianceStart',
						'ComplianceEnd',
						'Sum(InspectionAttemptcounter) [InspectionAttemptcounter]',
						'SectionFlag',
						'Sum(AssignedWorkOrderCount) [AssignedWorkOrderCount]',
						'AssignedCount',
						'[Percent Completed]',
						'Total',
						'Remaining',
						'InspectionType',
						'BillingCode',
						'InProgressFlag',
						'OfficeName',
					]);
				
				if($dateRange != null){
                    $explodedDateRange = explode(' ', $dateRange);
                    $startDate = $explodedDateRange[0];
                    $endDate = $explodedDateRange[2];
                    $assetQuery->andWhere(['or',
                        ['between', 'ComplianceStart', $startDate, $endDate],
                        ['between', 'ComplianceEnd', $startDate, $endDate],
                    ]);
                }
				
				//handle filter
				if($filter != null ){
					//url decode filter value
					$filter = urldecode($filter);
					//explode by delimiter to allow for multi search
					$delimiter = ',';
					$filterArray = explode($delimiter, $filter);

					//loop for multi search
					for($i = 0; $i < count($filterArray); $i++){
						//remove leading space from filter string
						$trimmedFilter = trim($filterArray[$i]);
						$filterQueryArray = ([
							'or',
							['like', 'UserFirstName ', $trimmedFilter],
							['like', 'UserLastName  ', $trimmedFilter],
							['like', 'UserName', $trimmedFilter],
							['like', 'MapGrid', $trimmedFilter],
							['like', 'OfficeName', $trimmedFilter],
							['like', 'BillingCode', $trimmedFilter],
							['like', 'InspectionType', $trimmedFilter],
							['like', 'ComplianceStart', $trimmedFilter],
							['like', 'ComplianceEnd', $trimmedFilter],
						]);
						$assetQuery->andFilterWhere($filterQueryArray);
					}
				}
				
				$assetQuery->groupBy([
					'MapGrid',
					'AssignedUser',
					'ComplianceStart',
					'ComplianceEnd',
					'SectionFlag',
					'AssignedCount',
					'[Percent Completed]',
					'Total',
					'Remaining',
					'InspectionType',
					'BillingCode',
					'InProgressFlag',
					'OfficeName',
				]);

				//pass query with pagination data to helper method
				// $paginationResponse = self::countFunctionPaginationProcessor($assetQuery, $page, $listPerPage, $filter);
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//add pagination data to response data
				$responseArray['pages'] = $paginationResponse['pages'];	
				//set asset query to returned value with added pagination clause
				$assetQuery = $paginationResponse['Query'];
			}
			
			//add order by to query and get data
			$data = $assetQuery->orderBy($orderBy)->all(BaseActiveRecord::getDb());
			//add query data to response data
            $responseArray[$envelope] = $data;
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveWebErrorJson('actionGetAssigned', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	//function created to improve performance due to slow view
	//view has since been improved so use has stopped 4/2/19
	//keeping in place for now in case it is needed.
	public function countFunctionPaginationProcessor($assetQuery, $page, $listPerPage, $filter)
    {
        // set pagination
        $countQuery = new Query;
		$countQuery->select('*')
			->from("fnAssignedWorkQueueByMapGridCount(:Filter)")
			->addParams([':Filter' => $filter]);
        $pages = new Pagination(['totalCount' => $countQuery->One(BaseActiveRecord::getDb())['WorkQueueCount']]);
        $pages->pageSizeLimit = [1, 750];
        $offset = $listPerPage * ($page - 1);
        $pages->setPageSize($listPerPage);
        $pages->pageParam = 'userPage';
        $pages->params = ['per-page' => $listPerPage, 'userPage' => $page];

        //append pagination clause to query
        $assetQuery->offset($offset)
            ->limit($listPerPage);

        $asset['pages'] = $pages;
        $asset['Query'] = $assetQuery;

        return $asset;
    }
	
	public function actionGetAssignedAssets($mapGridSelected, $sectionNumberSelected = null, $filter = null, $listPerPage = 10, $page = 1,
		$inspectionType = null, $billingCode = null, $officeName = null)
	{
		try{
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchGetAssignedAssets', $client);
			
			$responseArray = [];
			$orderBy = 'ComplianceEnd';
			$envelope = 'assets';

			//handle null or multiple inspection types
			if($inspectionType != null){
				//handle potential multiple inspection types
				$inspectionTypeFilter = ['or',
				['InspectionType' => $inspectionType]];
				$inspectionTypeArray = explode(',', $inspectionType);
				$inspectionTypeCount = count($inspectionTypeArray);
				for($i = 0; $i < $inspectionTypeCount; $i++){
					$inspectionTypeFilter[] = ['InspectionType' => $inspectionTypeArray[$i]];
				}
			}else{
				//if null just use inspection type
				$inspectionTypeFilter = ['InspectionType' => $inspectionType];
			}
			
			$assetQuery = AssignedWorkQueue::find()
				->where(['MapGrid' => $mapGridSelected])
				->andwhere($inspectionTypeFilter);
				
			if($billingCode != null) $assetQuery->andWhere(['BillingCode' => $billingCode]);			
			if($officeName != null) $assetQuery->andWhere(['OfficeName' => $officeName]);
			if($sectionNumberSelected != null) $assetQuery->andWhere(['SectionNumber' => $sectionNumberSelected]);
			
			if($filter != null){
				$assetQuery->andFilterWhere([
				'or',
				['like', 'InspectionType', $filter],
				['like', 'BillingCode', $filter],
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
                ['like', 'Address', $filter],
                ['like', 'AccountTelephoneNumber', $filter],
                ['like', 'OfficeName', $filter],
				]);
			}
			
			//pass query with pagination data to helper method
			$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
			//use updated query with pagination caluse to get data
			$data = $paginationResponse['Query']->orderBy($orderBy)->all();
			$responseArray['pages'] = $paginationResponse['pages'];
			$responseArray[$envelope] = $data;
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
		}
        catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveWebErrorJson('actionGetAssignedAssets', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionUnassign()
	{
		try
		{
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchUnassign', $client);
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true);
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'Unassign', BaseActiveController::getClientUser($client)->UserName, $client);
			
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
			//may want to try and wrap this in a transaction
			//process unassignMap
			for($i = 0; $i < $mapCount; $i++)
			{
				$results = self::processUnassigned(
					$data['unassignMap'][$i]['MapGrid'],
					null, //section
					null, //wo id
					(array_key_exists('AssignedUserID', $data['unassignMap'][$i]) ? $data['unassignMap'][$i]['AssignedUserID'] : null),
					(array_key_exists('InspectionType', $data['unassignMap'][$i]) ? $data['unassignMap'][$i]['InspectionType'] : null),
					(array_key_exists('BillingCode', $data['unassignMap'][$i]) ? $data['unassignMap'][$i]['BillingCode'] : null),
					(array_key_exists('OfficeName', $data['unassignMap'][$i]) ? $data['unassignMap'][$i]['OfficeName'] : null)
				);
				$responseData['unassignMap'][] = $results;
			}
			
			//process unassignSection
			for($i = 0; $i < $sectionCount; $i++)
			{
				$results = self::processUnassigned(
					$data['unassignSection'][$i]['MapGrid'],
					$data['unassignSection'][$i]['SectionNumber'],
					null, //wo id
					(array_key_exists('AssignedUserID', $data['unassignSection'][$i]) ? $data['unassignSection'][$i]['AssignedUserID'] : null),
					(array_key_exists('InspectionType', $data['unassignSection'][$i]) ? $data['unassignSection'][$i]['InspectionType'] : null),
					(array_key_exists('BillingCode', $data['unassignSection'][$i]) ? $data['unassignSection'][$i]['BillingCode'] : null),
					(array_key_exists('OfficeName', $data['unassignSection'][$i]) ? $data['unassignSection'][$i]['OfficeName'] : null)
				);
				$responseData['unassignSection'][] = $results;
			}
			
			//process unassign unassignAsset
			for($i = 0; $i < $assetCount; $i++)
			{
				$results = self::processUnassigned(
					null, //map grid
					null, //section
					$data['unassignAsset'][$i]['WorkOrderID'],
					//ternary check for optional params
					(array_key_exists('AssignedUserID', $data['unassignAsset'][$i]) ? $data['unassignAsset'][$i]['AssignedUserID'] : null)
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
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
	
	// Select Distinct
	// UserTb.UserID,
	// UserTb.UserFirstName + ' ' + UserTb.UserLastName + ' (' + UserTb.UserName + ')' AS UserFullName 
	// FROM tWorkOrder
	// RIGHT JOIN tWorkQueue ON tWorkOrder.ID = tWorkQueue.WorkOrderID
	// RIGHT JOIN UserTb ON tWorkQueue.AssignedUserID = UserTb.UserID
	// where tWorkOrder.MapGrid = '110-09-5' 
	// and tWorkOrder.InspectionType = 'LS'
	// and tWorkOrder.BillingCode = 'LM'
	// and tWorkQueue.WorkQueueStatus = 100
	//post route, that accepts a json body of objects containing mapgrid, section, inspection type and billing code and returns the associated users for unassigning
	public function actionGetAssignedUser()
	{
		try{
			//set db
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchUnassign', $client);
			
			//get body data
			$body = file_get_contents("php://input");
			$data = json_decode($body, true);
			
			//archive json
			BaseActiveController::archiveWebJson(json_encode($data), 'GetAssignedUser', BaseActiveController::getClientUser($client)->UserName, $client);
			
			//create response array
			$responseData['assignedUserMaps'] = [];
			
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			foreach($data['assignedUserMaps'] as $map){
				$whereClause = ['tWorkOrder.MapGrid' => $map['MapGrid']];
				if(array_key_exists('SectionNumber', $map) && $map['SectionNumber'] != null) $whereClause['tWorkOrder.SectionNumber'] = $map['SectionNumber'];
				if(array_key_exists('InspectionType', $map) && $map['InspectionType'] != null) $whereClause['tWorkOrder.InspectionType'] = $map['InspectionType'];
				if(array_key_exists('BillingCode', $map) && $map['BillingCode'] != null) $whereClause['tWorkOrder.BillingCode'] = $map['BillingCode'];
				if(array_key_exists('OfficeName', $map) && $map['OfficeName'] != null) $whereClause['tWorkOrder.OfficeName'] = $map['OfficeName'];
				$whereClause['tWorkQueue.WorkQueueStatus'] = Constants::WORK_QUEUE_ASSIGNED;
				$user = (new Query())
					->select(['UserTb.UserID', "UserTb.UserFirstName + ' ' + UserTb.UserLastName + ' (' + UserTb.UserName + ')' AS UserFullName"])
					->distinct()
					->from('tWorkOrder')
					->rightJoin('tWorkQueue', 'tWorkOrder.ID = tWorkQueue.WorkOrderID')
					->rightJoin('UserTb', 'tWorkQueue.AssignedUserID = UserTb.UserID')
					->where(['and', $whereClause])
					->all(BaseActiveRecord::getDb());
				$map['Users'] = $user;
				$responseData['assignedUserMaps'][] = $map;
			}
			$transaction->commit();
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}

	/*Helper method that gets all work orders associated with given mapGrid/section.
	**Then checks for existing assigned work queue records and removes any from 
	**results that already exist. Finally creates new records and returns results.
	*/
	private static function processDispatch($userIDs, $createdBy, $isCge, $mapGrid = null, $section = null, $workOrder = null,
		$scheduledDate = null, $inspectionType = null, $billingCode = null, $officeName = null)
	{
		$results = [];
		$workOrders = [];
		//get status code for Assigned work
		//TODO replace with constant
		$assignedCode = self::statusCodeLookup('Assigned');
		$successFlag = 1;
		$isAsset = 0;

		//scheduledDate works as an indicator if the the record is cge
		if($workOrder == null) {
			$workOrders = self::getDispatchWorkOrders($mapGrid, $section, $inspectionType, $billingCode, $officeName, $workOrder, $isCge);
			$workOrdersCount = count($workOrders);
		} else {
			$isAsset = true;
			$workOrdersCount = 1;
		}
		
		$db = BaseActiveRecord::getDb();
		$transaction = $db->beginTransaction();
		try{
			//loop work orders to assign
			for($i = 0; $i < $workOrdersCount; $i++)
			{
				$userCount = count($userIDs);
				for($j = 0; $j < $userCount; $j++){
					$dataArray = [
						'CreatedBy' => $createdBy,
						'CreatedDate' => date(Constants::DATE_FORMAT),
						'AssignedUserID' => $userIDs[$j],
						'WorkQueueStatus' => $assignedCode,
					];
					
					//assign workorder/section based on data available
					if($isAsset){
						$dataArray['SectionNumber'] = $section;
						$dataArray['WorkOrderID'] = $workOrder;
					}else{
						$dataArray['SectionNumber'] = $workOrders[$i]->SectionNumber;
						$dataArray['WorkOrderID'] = $workOrders[$i]->WorkOrderID;
					}
					
					if ($scheduledDate != null)
						$dataArray['ScheduledDispatchDate'] = date(Constants::DATE_FORMAT,strtotime($scheduledDate));
					
					$db->createCommand()->insert('tWorkQueue', $dataArray)->execute();
				}
			} 
			$transaction->commit();
		} catch(\Exception $e)
		{
			$transaction->rollback();				
			$successFlag = 0;
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
		}
		return $results = [
			'MapGrid' => $mapGrid,
			'AssignedUserID' => $userIDs,
			'SectionNumber' => $section,
			'WorkOrderID' => $workOrder,
			'InspectionType' => $inspectionType,
			'BillingCode' => $billingCode,
			'OfficeName' => $officeName,
			'SuccessFlag' => $successFlag,
		];
	}
	
	private static function processUnassigned($mapGrid = null, $section = null, $workOrder = null, $assignedUserID = null,
		$inspectionType = null, $billingCode = null, $officeName = null)
	{
		$successFlag = 0;
		try{
			$connection = BaseActiveRecord::getDb();
			$processJSONCommand = $connection->createCommand("EXECUTE spUnassignWO :MapGrid, :SectionNum , :WorkOrderID, :AssignedUserID,
				:InspectionType, :BillingCode, :OfficeName");
			$processJSONCommand->bindParam(':MapGrid', $mapGrid,  \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':SectionNum', $section,  \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':WorkOrderID', $workOrder,  \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':AssignedUserID', $assignedUserID,  \PDO::PARAM_INT);
			$processJSONCommand->bindParam(':InspectionType', $inspectionType,  \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':BillingCode', $billingCode,  \PDO::PARAM_STR);
			$processJSONCommand->bindParam(':OfficeName', $officeName,  \PDO::PARAM_STR);
			$processJSONCommand->execute();
			$successFlag = 1;
		}
		catch(\Exception $e)
		{
			BaseActiveController::archiveWebErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], [
			'MapGrid' => $mapGrid,
			'SectionNumber' => $section,
			'WorkOrderID' => $workOrder,
			'UserID' => $assignedUserID,
			'InspectionType' => $inspectionType,
			'BillingCode' => $billingCode,
			'OfficeName' => $officeName,
			'SuccessFlag' => $successFlag
			]);
		}
		
		//build response format
		return [
			'MapGrid' => $mapGrid,
			'SectionNumber' => $section,
			'WorkOrderID' => $workOrder,
			'UserID' => $assignedUserID,
			'InspectionType' => $inspectionType,
			'BillingCode' => $billingCode,
			'OfficeName' => $officeName,
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
	private static function getDispatchWorkOrders($mapGrid, $section = null, $inspectionType = null, $billingCode = null, $officeName = null, $workOrder = null, $isCge){
		//build query to get work orders based on map grid
		if($isCge){
			$workOrdersQuery = AvailableWorkOrderCGEByMapGridDetail::find();
		} else {
			$workOrdersQuery = AvailableWorkOrder::find();
		}
		//always filter on map grid
		$workOrdersQuery->where(['MapGrid' => $mapGrid]);
		if ($section != null) {
			$workOrdersQuery->andWhere(['SectionNumber' => $section]);
		}
		if ($inspectionType != null) {
			$workOrdersQuery->andWhere(['InspectionType' => $inspectionType]);
		}
		if ($billingCode != null) {
			$workOrdersQuery->andWhere(['BillingCode' => $billingCode]);
		}
		if ($officeName != null) {
			$workOrdersQuery->andWhere(['OfficeName' => $officeName]);
		}
				
        $workOrders = $workOrdersQuery->all();
		
        return $workOrders;
    }
	
	//helper method returns flag to determine if division column needs to be displayed on the web
	//will return a flag 1/0 based on if any division values for getAvaliableByMapGrid do not equal null
	private static function getDivisionFlag()
	{
		$divisionCount = AvailableWorkOrderByMapGrid::find()
			->where(['not', ['Division'=>null]])
			->count();
		$flag = $divisionCount > 0 ? 1 : 0;
		return $flag;
	}
	
	
	//route to get records for the purpose of Andre's dual dispatch test.
	public function actionGetDualDispatch($locType)
	{
		try
		{
			//set dbl
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('dispatchGetDualDispatch', $client);
			
			$assetQuery = WorkOrder::find()
				->limit(8)
				->select(['ID as WorkOrderID', 'tWorkOrder.MapGrid', 'tWorkOrder.SectionNumber'])
				->innerJoin('vAvailableWorkOrder', 'tWorkOrder.ID = vAvailableWorkOrder.WorkOrderID')
				->where(['tWorkOrder.LocationType' => $locType,
					'tWorkOrder.CompletedFlag' => 0,
					'tWorkOrder.InspectionAttemptCounter' => 0,
					'tWorkOrder.EventIndicator' => null])
				->asArray()
				->all();

			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $assetQuery;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveWebErrorJson('actionGetPipe', $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
}