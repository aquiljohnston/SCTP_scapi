<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 7/28/2016
 * Time: 12:54 PM
 */
namespace app\modules\v1\modules\pge\controllers;

use \yii\web\Controller;
use \Yii;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v1\modules\pge\models\WebManagementAOC;

class AocController extends Controller
{

    public function actionGet($division = null, $workCenter = null, $surveyor = null, $type = null, $startDate = null, $endDate = null, $filter = null)
    {
		try
		{
			//set db
			$headers = getallheaders();
			WebManagementAOC::setClient($headers['X-Client']);
			
			$aocQuery = WebManagementAOC::find();
			
			if($division != null)
			{
				$aocQuery->andWhere(['Division'=>$division]);
			}
			
			if($workCenter != null)
			{
				$aocQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			
			if($surveyor != null)
			{
				$aocQuery->andWhere(['Surveyor'=>$surveyor]);
			}
			
			if($type != null)
			{
				$aocQuery->andWhere(['AOCType'=>$type]);
			}
			
			if($startDate != null && $endDate != null)
			{
				$aocQuery->andWhere(['between', 'Date', $startDate, $endDate]);
			}
			
			if($aocQuery != null)
			{
				$aocQuery->andFilterWhere([
				'or',
				['like', 'Date', $filter],
				['like', 'Time', $filter],
				['like', 'Surveyor', $filter],
				['like', 'WorkCenter', $filter],
				['like', 'FLOC', $filter],
				['like', 'SurveyType', $filter],
				['like', 'AOCType', $filter],
				['like', 'MeterNumber', $filter],
				['like', 'HouseNo', $filter],
				['like', 'Street', $filter],
				['like', 'Apt', $filter],
				['like', 'City', $filter],
				['like', 'Comments', $filter],
				]);
			}
			
			$aocs = $aocQuery->all();
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $aocs;
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