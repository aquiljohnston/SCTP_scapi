<?php

namespace app\modules\v2\controllers;
set_time_limit(3600);

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Connection;

class RouteController extends Controller 
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
					//don't want to interfere with Dan Rawls if he still uses this
					//'route-optimization-1' => ['post'],
					//'route-optimization-2' => ['post'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionRouteOptimization1()
	{
		try
		{
			//get header and set db path
			$headers = getallheaders();
			if(array_key_exists('X-Client', $headers))
			{
				BaseActiveRecord::setClient($headers['X-Client']);
				//RBAC permissions check
				PermissionsController::requirePermission('routeOptimization1', $headers['X-Client']);
			} else {
				//RBAC permissions check
				PermissionsController::requirePermission('routeOptimization1');
			}

			//get post data
			$post = file_get_contents("php://input");			
			$data = json_decode($post, true);
			
			$response = self::routeOptimization($data, "TwoOpt");
			
			return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public function actionRouteOptimization2()
	{
		try
		{
			//get header and set db path
			$headers = getallheaders();
			if(array_key_exists('X-Client', $headers))
			{
				BaseActiveRecord::setClient($headers['X-Client']);
				//RBAC permissions check
				PermissionsController::requirePermission('routeOptimization2', $headers['X-Client']);
			} else {
				//RBAC permissions check
				PermissionsController::requirePermission('routeOptimization2');
			}

			//get post data
			$post = file_get_contents("php://input");			
			$data = json_decode($post, true);
			
			$response = self::routeOptimization($data, "SimulatedAnnealing");
			
			return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
	}
	
	public static function routeOptimization($data, $algorithm)
	{		
		//create response object
		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		
		//check possible data keys
		if($data['mapGrids'] != null)
		{
			//convert post data to a string for java param
			$paramString = 'mapGrids';
			$paramString = $paramString . '~' . json_encode($data['mapGrids']);	
		}
		elseif($data['routes'] != null)
		{
			//convert post data to a string for java param
			$paramString = 'routes';
			$paramString = $paramString . '~' . json_encode($data['routes']);	
		}
		else
		{
			$response->statusCode = 400;
			$response->data = 'Bad Request - No valid data key present.';
			return $response;
		}
		
		//check the isRoundTrip option if value is not present default to true
		if(array_key_exists('isRoundTrip', $data))
		{
			$isRoundTrip = $data['isRoundTrip'];
		}
		else
		{
			$isRoundTrip = false;
		}
		
		//file path for jar
		$filePath = Yii::$app->basePath . "\web\jar";
		
		//create temp file for data
		$postDataTemp = tempnam($filePath, 'TSP');
		$postDataTempName = basename($postDataTemp);
		$handle = fopen($postDataTemp, 'w');
		fwrite($handle, $paramString);
		fclose($handle);
		
		//$responseData = $postDataTempName;
		
		//set execution path
		chdir($filePath);
		//execute jar file
		//return "java -jar TSP.jar $postDataTempName $algorithm $isRoundTrip";
		exec("java -jar TSP.jar $postDataTempName $algorithm $isRoundTrip", $output);
		$responseData = json_decode($output[0]);
		
		// $mapCount = count($data['mapGrids']);
		// for($i; $i < $mapCount; $i++)
		// {
			// $mapGrid = $data['mapGrids'][$i];
			// //TODO query
			// // $assets = Asset::find()
				// // ->select(AssetUID, RouteOrder)
				// // ->where(['MapGrid' => $mapGrid])
				// // ->orderBy('RouteOrder')
				// // ->all()
			
			// $mapData['MapGrid'] = $mapGrid;
			// $mapData['assetOrderSequence'] = $assets;
			
			// $responseArray['routes'][] = $mapData;
		// }

		//clean up temp file
		unlink($postDataTemp);
		
		//send response
		$response->data = $responseData;
		return $response;
	}
}