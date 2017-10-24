<?php

namespace app\modules\v2\controllers;

use Yii;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\BaseUser;
use app\modules\v2\models\TabletDataInsertArchive;
use app\modules\v2\models\TabletDataInsertBreadcrumbArchive;
use app\modules\v2\models\TabletJSONDataInsertError;
use app\authentication\TokenAuth;
use yii\rest\ActiveController;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\base\ErrorException;
use yii\data\Pagination;

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
	
	//function gets user from client table based on token and client header
	public static function getClientUser($client)
	{
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		$ctUser = self::getUserFromToken();
		
		BaseActiveRecord::setClient($client);
		$clientUser = BaseUser::find()
			->where(['UserName' => $ctUser->UserName])
			->one();
		return $clientUser;
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
	
	//Archives incoming json records for logging and data recovery
	public static function archiveJson($json, $type, $userUID, $client)
	{
		TabletDataInsertArchive::setClient($client);
		
		$archiveRecord =  new TabletDataInsertArchive;
		$archiveRecord->CreatedUserUID = (string)$userUID;
		$archiveRecord->TransactionType = $type;
		$archiveRecord->InsertedData = $json;
		
		$archiveRecord->save();
	}
	
	//Archives incoming breadcrumb jsons for logging and data recovery
	public static function archiveBreadcrumbJson($json, $userUID, $client)
	{
		TabletDataInsertBreadcrumbArchive::setClient($client);
		
		$archiveBreadcrumb = new TabletDataInsertBreadcrumbArchive;
		$archiveBreadcrumb->UserUID = $userUID;
		$archiveBreadcrumb->InsertedData = $json;
		$archiveBreadcrumb->TransactionType = 'Breadcrumb';
		
		$archiveBreadcrumb->save();
	}
	
	//inserts a new error record into tTabletJSONDataInsertError table for given $client
	public static function archiveErrorJson($data, $error, $client, $data2 = null, $data3 = null, $data4 = null, $data5 = null)
	{
		TabletJSONDataInsertError::setClient($client);
		
		$archiveError = new TabletJSONDataInsertError;
		$archiveError->InsertedData = $data;
		$archiveError->InsertedData2 = json_encode($data2);
		$archiveError->InsertedData3 = json_encode($data3);
		$archiveError->InsertedData4 = json_encode($data4);
		$archiveError->InsertedData5 = json_encode($data5);
		$archiveError->ErrorNumber = $error->getCode();
		$archiveError->ErrorMessage = $error->getMessage();
		
		$archiveError->save();
	}
	
	//creates a new validation error exception for the given model name for logging purposes.
	public static function modelValidationException($model)
	{
		$e = new ErrorException(get_class($model) . ' Validation Exception: ' . json_encode($model->errors), 42, 2);
		return $e;
	}

    public function paginationProcessor($assetQuery, $page, $listPerPage)
    {
        // set pagination
        $countAssetQuery = clone $assetQuery;
        $pages = new Pagination(['totalCount' => $countAssetQuery->count('*', BaseActiveRecord::getDb())]);
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
	
	public static function isSCCT($client)
	{
		return ($client == BaseActiveRecord::SCCT_DEV ||
		$client == BaseActiveRecord::SCCT_STAGE ||
		$client == BaseActiveRecord::SCCT_PROD);
	}
}