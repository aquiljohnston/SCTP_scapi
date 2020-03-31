<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\db\Query;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v3\authentication\TokenAuth;
use app\modules\v3\models\ABCTaskOut;
use app\modules\v3\models\BaseActiveRecord;
use yii\web\UploadedFile;
use yii2mod\ftp\FtpClient;

/**
 * This is the model class for table "tAsset".
 * 
 * Created by: Jose Pinott 3/20/2020
 */
class FTPController extends Controller {
    // todo: remove clear text, encrypt/decrypt @ runtime
    private $host = 'scctimagesdev.southerncrosslighthouse.com';
    private $username = 'scctimagesdev.southerncrosslighthouse.com|scbot';
    private $password = 'Pa$$word';
    private static $remoteDir = '/ABCCodes';
    private static $localDir = '/relative/or/absolute/local/path';
	
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
					'actionGetFTPFile' => ['get'],
                ],
            ];
		return $behaviors;
	}
    
    public function actionGetFTPFile() {
		try{
			$responseData = [];
			//create ssh connection
			$ftp = new \yii2mod\ftp\FtpClient();
			//port 22 for sftp
			// $ftp->connect($this->host, true, 22);
			//false ssl, port 21 for ftp
			$ftp->connect($this->host, false, 21);
			$ftp->login($this->username, $this->password);
			$ftp->pasv(true);
			//Change directory
			$ftp->chdir(self::$remoteDir);
			// get file list of current directory
			$file_list = $ftp->nlist();
			//Check if archive directory exist
			if(!in_array('./archive', $file_list)) 
				// Create the archive directory
				$ftp->mkdir(self::$remoteDir . '/archive');
			//loop current directory items
			foreach($file_list as $file){
				try{
					//skip archive directory
					if($file == './archive')
						continue;
					//status flags
					$saveFlag = 0;
					$archiveFlag = 0;
					//get filename/extension for error handling and response
					$fileInfo = pathinfo($file);
					$fileName = $fileInfo['filename'];
					$fileExt = $fileInfo['extension'];
					
					//get handler for php temp stream
					$handler = fopen('php://temp', 'r+');
					//get file data
					$ftp->fget($handler, $file, FTP_BINARY, 0);
					//get file size then reset pointer and read
					$fstats = fstat($handler);
					fseek($handler, 0);
					$csv = fread($handler, $fstats['size']); 
					//close temp stream
					fclose($handler);
					//convert csv string to associative array
					$rows = array_map('str_getcsv', explode("\n", $csv));
					$header = array_shift($rows);
					foreach ($rows as $row) {
						$csvArray[] = array_combine($header, $row);
					}
					
					//save in db
					$saveFlag = self::ftpUpload($csvArray, $fileName);
					//success, move file to archive
					if($saveFlag){
						$newFileName = 'archive/' . $fileName . '_' . date('m-d-Y-His') . '.' . $fileExt;
						if ($ftp->rename($file, $newFileName)) {
						   $archiveFlag = 1;
						}
					}
				}catch(\Exception $e){
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
				}
				
				$responseData[] = [
					'fileName' => $fileName,
					'saveFlag' => $saveFlag,
					'archiveFlag' => $archiveFlag
				];
			}
			
			//create response object
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
    }

    public static function ftpUpload($csvArray, $fileName) {
        try {
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			$successFlag = 0;
			
            foreach($csvArray as $data){
				//create model
                $abcTaskOut = new ABCTaskOut;
                $abcTaskOut->attributes = $data;
                if ($abcTaskOut->save()){
                    $successFlag = 1;
                }else{
					$successFlag = 0;
					$transaction->rollback();
					throw BaseActiveController::modelValidationException($abcTaskOut);
				}
            }
			
			//commit transaction
			$transaction->commit();
            //return response data
            return $successFlag;
        }catch(\Exception $e){
            BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $fileName);
            return $successFlag;
        }
    }
}