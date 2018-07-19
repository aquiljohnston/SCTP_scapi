<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\AvailableWorkOrderCGEByMapGrid;
use app\modules\v2\models\AvailableWorkOrderCGEByMapGridDetail;
use app\modules\v2\models\AvailableWorkOrderCGEByWODetails;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class CgeController extends Controller 
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
					'get-map-grids' => ['get'],
					'get-by-map' => ['get'],
					'get-history' => ['get'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGetMapGrids($filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//get headers
			$client = getallheaders()['X-Client'];
			//set db
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('cgeGetMapGrids', $client);
			
			$responseArray = [];
			$orderBy = 'ComplianceEnd';
			$envelope = 'mapGrids';
			$assetQuery = AvailableWorkOrderCGEByMapGrid::find();
			
			if($filter != null)
			{
				$assetQuery->andFilterWhere([
				'or',
				['like', 'MapGrid', $filter],
				['like', 'ComplianceStart', $filter],
				['like', 'ComplianceEnd', $filter],
				['like', 'AvailableWorkOrderCount', $filter],
				]);
			}
			
			if($page != null)
			{
				//pass query with pagination data to helper method
				$paginationResponse = BaseActiveController::paginationProcessor($assetQuery, $page, $listPerPage);
				//pass pagination data to response array
				$responseArray['pages'] = $paginationResponse['pages'];
				//use updated query with pagination caluse to get data
				$responseArray[$envelope] = $paginationResponse['Query']->orderBy($orderBy)
				->all();
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
	
	public function actionGetByMap($mapGrid, $inspectionType, $billingCode)
	{
		try
		{
			//get headers
			$client = getallheaders()['X-Client'];
			//set db
			BaseActiveRecord::setClient($client);
			//RBAC permissions check
			PermissionsController::requirePermission('cgeGetByMap', $client);
			
			$responseArray = [];
			$responseArray['cges'] = AvailableWorkOrderCGEByMapGridDetail::find()
				->where(['MapGrid' => $mapGrid,
					'InspectionType' => $inspectionType,
					'BillingCode' => $billingCode
				])
				->orderBy('Address', 'InspectionDateTime')
				->all();
			
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
	
	public function actionGetHistory($workOrderID)
	{
		try
		{
			//get headers
			$client = getallheaders()['X-Client'];
			//set db
			BaseActiveRecord::setClient($client);
			
			//RBAC permissions check
			PermissionsController::requirePermission('cgeGetHistory', $client);
			
			$responseArray = [];
			$responseArray['cgeHistory'] = AvailableWorkOrderCGEByWODetails::find()
				->where(['ID' => $workOrderID])
				->orderBy('InspectionDateTime')
				->all();
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseArray;
			return $response;
			
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
}