<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\models\SCUser;
use app\authentication\TokenAuth;
use app\modules\v1\models\TabletDataInsertArchive;
use app\modules\v1\models\TabletDataInsertBreadcrumbArchive;
use app\modules\v1\models\TabletJSONDataInsertError;
use yii\db\ActiveRecord;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\base\ErrorException;
use yii\db\Exception;

class BaseActiveController extends ActiveController
{	
	const DATE_FORMAT = 'Y-m-d H:i:s';
	private static $IV = 'abcdefghijklmnop';
	private static $S_KEY = 'sparusholdings12';
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['create']);
		return $actions;
	}
	
	/**
	* sets authenticator for token authentication
	* sets verb filters for http request
	* @return an array of behaviors
	*/
   public function behaviors()
    {
		$behaviors = parent::behaviors();
		//Implements Token Authentication to check for Auth Token in Json Header
		$behaviors['authenticator'] =
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] =
			[
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['delete'],
					'create' => ['post'],
					'update' => ['put'],
					'get-all' => ['get'],
                ],
            ];
		return $behaviors;
	}

	/**
	* Creates a record for the sub class's model
	* @returns json body of the model data
	* @throws \yii\web\HttpException
	*/
	public function actionCreate()
    {
		try
		{
			//set model class
			$modelClass = $this->modelClass;
			
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient(self::urlPrefix());
			
			$post = file_get_contents("php://input");
			$data = json_decode(utf8_decode($post), true);

			$model = new $this->modelClass();
			$model->attributes = $data;
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $model;
			
			if($model-> save())
			{
				$response->setStatusCode(201);
				return $response;
			}
			else
			{
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
    }
	
	public function getDate()
	{
		return date(BaseActiveController::DATE_FORMAT);
	}	
	
	public static function getUserFromToken($token = null)
	{
		if ($token === null) {
			$token = Yii::$app->request->getAuthUser();
		}
		return SCUser::findIdentityByAccessToken($token);
	}

	
    public static function inDateRange($day, $startDate, $endDate) {
        //$day .= " 12:00:00pm";
        $startDate .= " 12:00:00am";
        $endDate .= " 11:59:59pm";
        $dayTS = strtotime($day);
        return strtotime($startDate) <= $dayTS && $dayTS < strtotime($endDate);
    }
	
	//type: type of data the UID will be associated with such as User, breadcrumb, activty, etc.
	//source: platform that created the data UID is associated with such as web, mobile, etc.
	public static function generateUID($type, $source)
	{
		//generate random number
		$random = rand(10000000, 99999999);
		
		//get current date time in format YmdHis
		$date = date("YmdHis");
		
		//concat values into string and return the resulting UID
		return "{$type}_{$random}_{$date}_{$source}";
	}
	
	public static function decrypt($encryptedString)
	{
		$decodedString = base64_decode($encryptedString);
		$decryptedString = openssl_decrypt($decodedString,  'AES-128-CBC', self::$S_KEY, OPENSSL_RAW_DATA, self::$IV);
		return $decryptedString;
	}
	
	public static function encrypt($string)
	{
		$encryptedString = openssl_encrypt($string,  'AES-128-CBC', self::$S_KEY, OPENSSL_RAW_DATA, self::$IV);
		$encodedString = base64_encode($encryptedString);
		return $encodedString;
	}
	
	public static function urlPrefix()
	{
		$url = explode(".", $_SERVER['SERVER_NAME']);
		$prefix = $url[0];
        if(YII_ENV_DEV && (strpos($_SERVER['SERVER_NAME'],'local')!==false
                ||  $_SERVER['SERVER_NAME'] === '0.0.0.0'
                || strpos($_SERVER['SERVER_NAME'],'192.168.')===0)
        )
        {
            $prefix = 'apidev';
        }
		return $prefix;
	}
	
	// TabletDataInsertArchive;
	// TabletDataInsertBreadcrumbArchive;
	// TabletJSONDataInsertError;
	public static function archiveJson($json, $type, $userUID, $client)
	{
		TabletDataInsertArchive::setClient($client);
		
		$archiveRecord =  new TabletDataInsertArchive;
		$archiveRecord->CreatedUserUID = $userUID;
		$archiveRecord->TransactionType = $type;
		$archiveRecord->InsertedData = $json;
		
		$archiveRecord->save();
	}
	
	public static function archiveBreadcrumbJson($json, $userUID, $client)
	{
		TabletDataInsertBreadcrumbArchive::setClient($client);
		
		$archiveBreadcrumb = new TabletDataInsertBreadcrumbArchive;
		$archiveBreadcrumb->UserUID = $userUID;
		$archiveBreadcrumb->InsertedData = $json;
		$archiveBreadcrumb->TransactionType = 'Breadcrumb';
		
		$archiveBreadcrumb->save();
	}
	
	public static function archiveErrorJson($json, $error, $client)
	{
		TabletJSONDataInsertError::setClient($client);
		
		$archiveError = new TabletJSONDataInsertError;
		$archiveError->InsertedData = $json;
		$archiveError->ErrorNumber = $error->getCode();
		$archiveError->ErrorMessage = $error->getMessage();
		
		$archiveError->save();
	}
}