<?php
    /**
     * Purpose of this file is to execute the getFTPFile in SCAPI v3 route.
    **/
    // URLs: Login authentication, FTP request
	$urlLoginDev = 'http://apidev.southerncrossinc.com/index.php?r=v2%2Flogin%2Fuser-login';
    $urlLoginStage = 'http://apistage.southerncrossinc.com/index.php?r=v2%2Flogin%2Fuser-login';
    $urlLoginProd = 'http://api.southerncrossinc.com/index.php?r=v2%2Flogin%2Fuser-login';
    $urlFTPActionDev = 'http://apidev.southerncrossinc.com/index.php?r=v3%2Ff-t-p%2Fget-f-t-p-file';
    $urlFTPActionStage = 'http://apistage.southerncrossinc.com/index.php?r=v3%2Ff-t-p%2Fget-f-t-p-file';
    $urlFTPActionProd = 'http://api.southerncrossinc.com/index.php?r=v3%2Ff-t-p%2Fget-f-t-p-file';

    try {
        echo nl2br("TimeTracker FTP process starting.\n");
        // authentication body
        $postData = array(
              'UserName' => 'jtech', 
              'Password' => 'pXrB9EpNPznSDM6UcUVxgQ=='
        );
        // authentication headers
        $params = array(
            'http' => array(
                'method'  => 'POST',
                // 'headers'  => 'Content-type: application/x-www-form-urlencoded',
                // 'headers' => implode("\r\n", $headers),
                'X-Client' => 'scctdev',
                'content' => json_encode($postData),
                "ignore_errors" => true
            )
        );
        // prep ftp headers
        $headers = array(
            'Content-type' => 'application/x-www-form-urlencoded', 
            'Accept' => 'application/json',
            'Authorization' => $responseJson->AuthTokens
        );
        $ftpParams = array(
            'http' => array(
                'method'  => 'GET',
                'headers' => implode("\r\n", $headers),
                'X-Client' => 'scctdev',
                "ignore_errors" => true
            )
        );
        try {
            // execute login
            $context  = stream_context_create($params);
            $result = file_get_contents($urlLoginDev, false, $context);
            $responseJson = json_decode($result);
            
            // array $http_response_header php var -> materializes out of thin air ;-)
            $loginStatus_line = $http_response_header[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $loginStatus_line, $match);
            $loginStatusCode = $match[1];

            if ($loginStatusCode !== "200") {
                error_log("context json: " . var_dump($responseJson) . "\n");
                // email team
                $subject = "Login Authentication - Failed";
                $msg = "API response: \n" . var_dump($responseJson) . "\n\n Status code: " . $loginStatusCode; 
                // use wordwrap() if lines are longer than 70 characters
                $msg = wordwrap($msg,70);
                // send email
                mail("sccdevteam@southerncrossinc.com",$subject,$msg);
            } else {
                // execute ftp action
                echo nl2br("execute FTP action starting!!!\n");
                $ftpContext  = stream_context_create($ftpParams);
                $ftpResult = file_get_contents($urlFTPActionDev, false, $ftpContext);
                
                // array $http_response_header php var -> materializes out of thin air ;-)
                $status_line = $http_response_header[0];
                preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
                $status = $match[1];
                if ($status !== "200") {
                    error_log('ERROR during FTP call action:' . $e);
                    // email team
                    $subject = "FTP Action Failed";
                    $msg = "API response: \n" . $ftpResult; 
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
