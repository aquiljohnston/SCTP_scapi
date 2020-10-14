<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
	
class ProcessFtpController extends Controller
{
    /**
     * Purpose of this file is to execute the getFTPFile in SCAPI v3 route.
    **/
    
	//Cmd: yii process-ftp/init
	public function actionInit()
    {
		// URLs: Login authentication, FTP request
		$urlLoginDev = 'http://apidev.southerncrossinc.com/index.php?r=v2%2Flogin%2Fuser-login';
		$urlLoginStage = 'http://apistage.southerncrossinc.com/index.php?r=v2%2Flogin%2Fuser-login';
		$urlLoginProd = 'http://azureapi.southerncrossinc.com/index.php?r=v2%2Flogin%2Fuser-login';
		$urlFTPActionDev = 'http://apidev.southerncrossinc.com/index.php?r=v3%2Ff-t-p%2Fget-f-t-p-file';
		$urlFTPActionStage = 'http://apistage.southerncrossinc.com/index.php?r=v3%2Ff-t-p%2Fget-f-t-p-file';
		$urlFTPActionProd = 'http://azureapi.southerncrossinc.com/index.php?r=v3%2Ff-t-p%2Fget-f-t-p-file';
		
		try {
			echo nl2br("TimeTracker FTP process starting.\n");
			// authentication body
			$postData = array(
				'UserName' => 'Automation',
				'Password' => '7IgOgUIc8tBGdGzpAAW3sg=='
			);
			$postData = json_encode($postData);
			//set headers
			$headers = array(
				'X-Client:scct',
				'Accept:application/json',
				'Content-Type:application/json',
				'Content-Length: ' . strlen($postData)
			);
			
			
			try {
				// execute login
				$loginCurl = curl_init();
				//set curl options
				curl_setopt($loginCurl, CURLOPT_URL, $urlLoginProd);
				curl_setopt($loginCurl, CURLOPT_POST, 1);
				curl_setopt($loginCurl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($loginCurl, CURLOPT_POSTFIELDS,$postData);
				curl_setopt($loginCurl, CURLOPT_HTTPHEADER, $headers);
				//execute curl
				$response = curl_exec($loginCurl);
				$responseJson = json_decode($response);
				//check response code
				$httpCode = curl_getinfo($loginCurl, CURLINFO_HTTP_CODE);
				curl_close($loginCurl);
				if ($httpCode !== 200) {
					error_log("context json: $response \n");
					// email team
					$subject = "Login Authentication - Failed";
					$msg = "API response: \n" . $response . "\n\n Status code: " . $httpCode; 
					// use wordwrap() if lines are longer than 70 characters
					$msg = wordwrap($msg,70);
					// send email
					Yii::$app->mailer->compose()
						->setFrom('SC_Automated_Reporting@southerncrossinc.com')
						->setTo('sccdevteam@southerncrossinc.com')
						->setSubject($subject)
						->setTextBody($msg)
						->send();
				} else {
					// execute ftp action
					echo nl2br("execute FTP action starting!!!\n");
					
					// prep ftp headers
					$ftpHeaders = array(
						'X-Client:scct',
						'Accept:application/json',
						'Content-Type:application/json',
						'Authorization: Basic '. base64_encode($responseJson->AuthToken . ':')
					);
					
					// execute login
					$ftpCurl = curl_init();
					//set curl options
					curl_setopt($ftpCurl, CURLOPT_URL, $urlFTPActionProd);
					curl_setopt($ftpCurl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ftpCurl, CURLOPT_HTTPHEADER, $ftpHeaders);
					//execute curl
					$ftpResponse = curl_exec($ftpCurl);
					$ftpResponseJson = json_decode($ftpResponse);
					//check response code
					$ftpHttpCode = curl_getinfo($ftpCurl, CURLINFO_HTTP_CODE);
					curl_close($ftpCurl);
					if ($ftpHttpCode !== 200) {
						error_log("ERROR during FTP call action: $ftpResponse \n");
						// email team
						$subject = "FTP Action Failed";
						$msg = "API response: \n" . $ftpResponse . "\n\n Status code: " . $ftpHttpCode; 
						// use wordwrap() if lines are longer than 70 characters
						$msg = wordwrap($msg,70);
						// send email
						Yii::$app->mailer->compose()
							->setFrom('SC_Automated_Reporting@southerncrossinc.com')
							->setTo('sccdevteam@southerncrossinc.com')
							->setSubject($subject)
							->setTextBody($msg)
							->send();
					}
				}
			} catch (\Exception $e) {
				error_log("ERROR during FTP call action: $e \n");
				// email team
				$subject = "FTP Action Failed";
				$msg = "Error Message: \n" . $e->getMessage();
				// use wordwrap() if lines are longer than 70 characters
				$msg = wordwrap($msg,70);
				// send email
				Yii::$app->mailer->compose()
					->setFrom('SC_Automated_Reporting@southerncrossinc.com')
					->setTo('sccdevteam@southerncrossinc.com')
					->setSubject($subject)
					->setTextBody($msg)
					->send();
			}
		} catch (\Exception $e) { 
			error_log("Authentication Error: $e \n"); 
			// email team
			$subject = "Login Authentication - Failed";
			$msg = "Error Message: \n" . $e->getMessage();
			// use wordwrap() if lines are longer than 70 characters
			$msg = wordwrap($msg,70);
			// send email
			Yii::$app->mailer->compose()
				->setFrom('SC_Automated_Reporting@southerncrossinc.com')
				->setTo('sccdevteam@southerncrossinc.com')
				->setSubject($subject)
				->setTextBody($msg)
				->send();
		}
		echo nl2br("TimeTracker FTP process ending.\n");
	}
}
?>
