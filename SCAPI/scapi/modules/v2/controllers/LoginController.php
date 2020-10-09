<?php

namespace app\modules\v2\controllers;


use Yii;
use app\modules\v2\models\SCUser;
use app\modules\v2\models\Auth;
use app\modules\v2\models\Project;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\constants\Constants;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class LoginController extends Controller
{
    /**
     * Login user by UserName and Password
     *
     * @return \yii\console\Response|Response
     * @throws \yii\base\Exception
     */
	public function actionUserLogin()
	{
	    try {
            //get client header to find project landing page
            $headers = getallheaders();
            $client = '';
            if(array_key_exists('X-Client', $headers))
            {
                $client = $headers['X-Client'];
            }

            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;

            //read the post input (use this technique if you have no post variable name):
            $post = file_get_contents("php://input");

            //decode json post input as php array:
            $data = json_decode($post, true);

            if($client != null)
            {
                //archive json
                BaseActiveController::archiveWebJson(
                    json_encode($data),
                    'Login',
                    //ternary check if username is in data set to prevent potential error on bad json
                    (array_key_exists('UserName', $data) ? $data['UserName'] : null),
                    $client);
            }

            //set db target
            SCUser::setClient(BaseActiveController::urlPrefix());

            //login is a Yii model:
            $userName = new SCUser();

            //load json data into model:
            $userName->UserName = $data['UserName'];

            if($user = SCUser::findOne(['UserName'=>$userName->UserName, 'UserActiveFlag'=>1]))
            {
                $securedPass = $data["Password"];

                //decrypt password
                $decryptedPass = BaseActiveController::decrypt($securedPass);

                $hash = $user->UserPassword;
                //Check the Hash
                if (password_verify($decryptedPass, $hash))
                {
                    //Pass
                    Yii::$app->user->login($user);
                    //Generate Auth Token
                    $auth = new Auth();
                    $auth->AuthUserID = $user->UserID;
                    $auth->AuthCreatedBy = $user->UserName;
                    if($client == null){
                        $timeout = Yii::$app->user->authTimeout;
                    } else {
                        $project = Project::find()
                            ->where(['ProjectUrlPrefix' => $client])
                            ->one();
                        $timeout = $project->AuthTimeOut;
                    }
                    //review the algorithm for generateRandomString
                    $auth->AuthToken = \Yii::$app->security->generateRandomString();
                    $auth->AuthTimeout = time() + $timeout;
                    //Store Auth Token
                    $auth-> save();

                    // log
                    BaseActiveController::logRoute(null, $userName->UserName,"Login successful.", true);

                }
                else
                {
                    $response->data = "Password is invalid.";
                    $response->setStatusCode(401);

                    // log
                    BaseActiveController::logRoute(null, $userName->UserName, $response->data, true);


                    return $response;
                }
            }
            else
            {
                $response->data = "User not found or inactive.";
                $response->setStatusCode(401);

                // log
                BaseActiveController::logRoute(null, @$data['UserName'], $response->data, true);

                return $response;
            }

            $authArray = ArrayHelper::toArray($auth);
            $authArray['UserFirstName'] = $user->UserFirstName;
            $authArray['UserLastName'] = $user->UserLastName;
            $authArray['UserName'] = $user->UserName;
            $authArray['ProjectLandingPage'] = self::getProjectValues($client)['ProjectLandingPage'];

            $authArray['ProjectID'] = self::getProjectValues($client)['ProjectID'];
            $authArray['UserAppRoleType'] = $user->UserAppRoleType;

            //add auth token to response
            $response->data = $authArray;
            return $response;
        }catch(\Exception $e) {

	        // log
            BaseActiveController::logRoute($e, @$data['UserName'],  'Http Exception',true);

            throw new \yii\web\HttpException(400);
        }
	}
	
	// User logout
	public function actionUserLogout()
	{

		try{

			//get request headers
			$headers = getallheaders();
			$client = $headers['X-Client'];
			if(array_key_exists('Authorization', $headers))
				$token = substr(base64_decode(explode(" ", $headers['Authorization'])[1]), 0, -1);
			else
				throw new UnauthorizedHttpException;

			yii::trace('TOKEN? ' . $token);
			
			//set target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			// username
           $username =  BaseActiveController::getUserFromToken($token)->UserName;

			//archive request
			BaseActiveController::archiveWebJson(null, 'Logout', $username, $client);
			
			//call CTUser\logout()
			Yii::$app->user->logout($destroySession = true, $token);
			
			//create response object
			$response = Yii::$app->response;
			$response->data = 'Logout Successful!';

            // log
            BaseActiveController::logRoute(null, $username, $response->data, true);

            return $response;
		} catch(UnauthorizedHttpException $e) {

            // log
            BaseActiveController::logRoute($e, @$username, 'Unauthorized http exception', false);

            BaseActiveController::logError($e, 'Unauthorized http exception');

            throw new UnauthorizedHttpException;
        } catch(\Exception $e) {

            // log
            BaseActiveController::logRoute($e, @$username, 'Exception', false);

            throw new \yii\web\HttpException(400);
		}
	}
	
	private static function getProjectValues($client)
	{
		$projectQuery = Project::find()
			->select('ProjectLandingPage, ProjectID')
			->where(['ProjectUrlPrefix' => $client]);
		if(BaseActiveController::isSCCT($client))
		{
			$projectQuery->andWhere(['ProjectName' => Constants::SCCT_CONFIG['BASE_PROJECT']]);
		}
		$projectValues = $projectQuery->one();

		return $projectValues;
	}
}
