<?php

namespace app\modules\v3\controllers;

use Yii;
use yii\db\Query;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v3\models\ABCTaskOut;
use yii\web\UploadedFile;
use yii2mod\ftp\FtpClient;

/**
 * This is the model class for table "tAsset".
 * 
 * Created by: Jose Pinott 3/20/2020
 */
class FTPController extends Controller {
    // todo: remove clear text, encrypt/decrypt @ runtime
    private $host = 'localhost';
    private $username = 'username';
    private $password = 'password';
    private static $remoteDir = '/full/folder/path';
    private static $localDir = '/relative/or/absolute/local/path';
    
    public function actionGetFTPFile() {
        // create ssh connection
        $ftp = new \yii2mod\ftp\FtpClient();
        $ftp->pasv(true);
        $ftp->connect($host, true, 22);
        $ftp->login($username, $password);
        // change directory
        $ftp->chdir($remoteDir);
        // directory count
        $total_dir = $ftp->count('.', 'directory');
        if($total_dir == 0)
            // Create the archive directory
            $ftp->mkdir($remoteDir.'/archive');
        
        // tmp testing count, remove
        // count only the "files" in the current directory
        $total_file = $ftp->count('.', 'file');
        // get file list of current directory
        $file_list = $ftp->ftp_nlist($ftp, ".");
        foreach($file_list as $file) {
            // open file for reading and save in db
            $response = actionFTPUpload($ftp->fopen($file,"r"));
            // success, move file to archive
            if($response['SuccessFlag']) {
                $newFileName = $file.new DateTime();
                // try to rename $old_file to $new_file
                if ($ftp->ftp_rename($ftp, $file, $newFileName)) {
                    echo "Successfully renamed $file to $newFileName";
                    // archive file
                    if($ftp->move_uploaded_file($newFileName, $remoteDir.'/archive'))
                    echo "Successfully moved $newFileName";
                }
            }
        }
    }

    public function actionFTPUpload($filecsv) {
        try {
            $model = new ABCTaskOut;
            foreach($filecsv as $data){
                $successFlag = 0;
                $abcTaskOut = new ABCTaskOut;
                // parse file
                $row = explode(",",$data);
                $exampleValue = $row[0];
                $abcTaskOut->SCCEmployeeID = $exampleValue;
                // or
                $abcTaskOut->attributes = $data;
                if ($abcTaskOut->save()){
                    $successFlag = 1;
                }
            }
            $responseData = [
                'CreatedDate' => new DateTime(),
                'SuccessFlag' => $successFlag
            ];
            //return response data
            return $responseData;
        } catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
            BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
    }
}