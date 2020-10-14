<?php
    /**
     * Purpose of this file is to execute the getFTPFile in SCAPI v3 route.
    **/
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
			// 'UserName' => 'jtech', 
			// 'Password' => 'pXrB9EpNPznSDM6UcUVxgQ=='
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
                mail("sccdevteam@southerncrossinc.com",$subject,$msg);
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
                    error_log('ERROR during FTP call action:' . $ftpResponse);
                    // email team
                    $subject = "FTP Action Failed";
                    $msg = "API response: \n" . $ftpResponse; 
                    // use wordwrap() if lines are longer than 70 characters
                    $msg = wordwrap($msg,70);
                    // send email
                    mail("sccdevteam@southerncrossinc.com",$subject,$msg);
                }
            }
        } catch (Exception $e) {
            error_log('ERROR during FTP call action:' . $e); 
            // email team
            $subject = "FTP Action Failed";
            $msg = "API response: \n" . $e->getMessage();
            // use wordwrap() if lines are longer than 70 characters
            $msg = wordwrap($msg,70);
            // send email
            mail("sccdevteam@southerncrossinc.com",$subject,$msg);
        }
    } catch (Exception $e) { 
        error_log('Authentication Error' . $e); 
        // email team
        $subject = "Login Authentication - Failed";
        $msg = "API response: \n" . $e->getMessage();
        // use wordwrap() if lines are longer than 70 characters
        $msg = wordwrap($msg,70);
        // send email
        mail("sccdevteam@southerncrossinc.com",$subject,$msg);
    }
	echo nl2br("TimeTracker FTP process ending.\n");
?>
