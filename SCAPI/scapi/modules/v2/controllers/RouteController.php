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
					'route-optimization' => ['post'],
                ],
            ];
		return $behaviors;	
	}
	
	public function actionRouteOptimization()
	{
		try
		{
			//get header and set db path
			$headers = getallheaders();
			if(array_key_exists('X-Client', $headers))
			{
				BaseActiveRecord::setClient($headers['X-Client']);
			}
			
			//get post data
			$post = file_get_contents("php://input");			
			$data = json_decode($post, true);
			
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
			exec("java -jar TSP.jar $postDataTempName", $output);
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