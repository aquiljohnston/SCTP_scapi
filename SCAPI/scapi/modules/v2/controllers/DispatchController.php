<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\authentication\TokenAuth;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\controllers\BaseActiveController;
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
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGet($division = null, $filter = null, $listPerPage = null, $page = null)
	{
		try
		{
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			//stubdata
			$record1 = [
			'Division' => 'Atlanta',
			'MapGrid' => '163-43-1-C',
			'ComplianceStartDate' => '2017-01-01',
			'ComplianceEndDate' => '2017-11-30'
			];
			$record2 = [
			'Division' => 'Norcross',
			'MapGrid' => '161-40-1-B',
			'ComplianceStartDate' => '2017-01-01',
			'ComplianceEndDate' => '2017-11-30'
			];
			$record3 = [
			'Division' => 'Johns Creek',
			'MapGrid' => '162-45-1-A',
			'ComplianceStartDate' => '2017-01-01',
			'ComplianceEndDate' => '2017-11-30'
			];
			$record4 = [
			'Division' => 'Duluth',
			'MapGrid' => '160-47-1-D',
			'ComplianceStartDate' => '2017-01-01',
			'ComplianceEndDate' => '2017-11-30'
			];
			
			//add stub data to array
			$dataArray = [];
			$dataArray[] = $record1;
			$dataArray[] = $record2;
			$dataArray[] = $record3;
			$dataArray[] = $record4;
			
			//loop stub data for filters
			$dataLength = count($dataArray);
			for($i = 0; $i < $dataLength; $i++)
			{
				if($division != null)
				{
					if($dataArray[$i]['Division'] != $division)
					{
						array_splice($dataArray, $i, 1);
						$dataLength = count($dataArray);
						$i--;
					}
				}
			}
			//create response array
			$responseArray = [];
			//loop for filter
			for($i = 0; $i < $dataLength; $i++)
			{
				if($filter != null)
				{
					if(stripos($dataArray[$i]['Division'], $filter) !== false)
					{
						$responseArray['Maps'][] = $dataArray[$i];
						continue;
					}
					if(stripos($dataArray[$i]['MapGrid'], $filter) !== false)
					{
						$responseArray['Maps'][] = $dataArray[$i];
						continue;
					}
					if(stripos($dataArray[$i]['ComplianceStartDate'], $filter) !== false)
					{
						$responseArray['Maps'][] = $dataArray[$i];
						continue;
					}
					if(stripos($dataArray[$i]['ComplianceEndDate'], $filter) !== false)
					{
						$responseArray['Maps'][] = $dataArray[$i];
						continue;
					}
				}
			}
			$response->data = $responseArray;
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