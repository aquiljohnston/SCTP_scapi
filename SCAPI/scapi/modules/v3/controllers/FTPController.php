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
use phpseclib\Net\SFTP;

/**
 * This is the model class for table "tAsset".
 * 
 * Created by: Jose Pinott 3/20/2020
 */
class FTPController extends Controller {
    // todo: remove clear text, encrypt/decrypt @ runtime
    private $host = 'sftp.southerncrossinc.com';
    private $username = 'ttsot';
    private $password = 'Pr4ExBTf8MAFGoxaDDc9t6';
    private static $remoteDir = '/ttsot/ABC';
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
			$sftp = new SFTP($this->host);
			if (!$sftp->login($this->username, $this->password)) {
				throw new Exception('Login failed');
			}
			
			//Change directory
			$sftp->chdir(self::$remoteDir);
			// get file list of current directory
			$file_list = $sftp->nlist();

			//Check if archive directory exist
			if(!in_array('./archive', $file_list)) 
				// Create the archive directory
				$sftp->mkdir(self::$remoteDir . '/archive');
			//loop current directory items
			foreach($file_list as $file){
				try{
					//skip archive directory
					if(!strpos($file, '.csv'))
						continue;
					//status flags
					$saveFlag = 0;
					$archiveFlag = 0;
					
					//get filename/extension for error handling and response
					$fileInfo = pathinfo($file);
					$fileName = $fileInfo['filename'];
					$fileExt = $fileInfo['extension'];

					//get file data
					$csv = $sftp->get($file);

					//convert csv string to associative array
					$rowCount = count(explode("\n", $csv));
					$delimiterArray = [];
					while($rowCount  > 0){
						$delimiterArray[] = '|';
						$rowCount--;
					}
					$rows = array_map('str_getcsv', explode("\n", $csv), $delimiterArray);
					$header = array_shift($rows);
					foreach ($rows as $row) {
						if(count($row) == 5)
							$csvArray[] = array_combine($header, $row);
					}
					
					//save in db
					$saveFlag = self::ftpUpload($csvArray, $fileName);
					//success, move file to archive
					if($saveFlag){
						$newFileName = 'archive/' . $fileName . '_Processed_' . date('m-d-Y-His') . '.' . $fileExt;
						if ($sftp->rename($file, $newFileName)) {
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
				$abcTaskOut->RefProjectID = $data['ProjectID'];
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