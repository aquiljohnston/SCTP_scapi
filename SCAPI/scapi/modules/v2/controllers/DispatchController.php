<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\authentication\TokenAuth;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\AvailableWorkQueue;
use app\modules\v2\models\AssignedWorkQueue;
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
					'unassign'
                ],
            ];
		return $behaviors;	
	}
	
	public function actionGet($filter = null, $listPerPage = 10, $page = 1)
	{
		try
		{
			//set db
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$responseArray = [];
			
			$assetQuery = AvailableWorkQueue::find()->where(['CompletedFlag' => 0]);
			
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

    public function actionGetSurveyors($workCenter = null, $filter = null, $listPerPage = null, $page = null)
    {
        $users = [
            [
                'Name' => 'Patton, Josh',
                'Division' => 'Las Vegas'
            ]
        ];
        $responseArray['users'] = $users;
        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $responseArray;
        return $response;
    }
}