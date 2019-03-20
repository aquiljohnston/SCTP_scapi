<?php

namespace  app\modules\v3\authentication;

use Yii;
use yii\filters\auth\AuthMethod;
use app\modules\v3\models\SCUser;
use app\modules\v3\models\Auth;
use app\modules\v3\models\HistoryAuth_Assignment;
use app\modules\v3\models\Alert;
use yii\base\ErrorException;
use yii\web\Response;
use app\modules\v3\controllers\BaseActiveController;

class TokenAuth extends AuthMethod
{
	public $identity;
	const AUTO_LOGOUT_ALERT_TITLE = 'Work Day Complete - Auto Logout';
	 
    public function authenticate($user, $request, $response)
    {
		SCUser::setClient(BaseActiveController::urlPrefix());
		
        $token = $request->getAuthUser();
		
		if ($token !== null && $token !== '') {
			Yii::$app->user->checkTimeout($token);
			try {
				$identity = SCUser::findIdentityByAccessToken($token);
				if ($identity !== null) {
					//check for client header
					try {
						getAllHeaders()['X-Client'];
					} catch(ErrorException $e) {	
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
						throw new \yii\web\HttpException(400, 'Client Header Not Found.');
					}
					return $identity;
				} else { 
					//TODO move string to constants when version is created
					throw new \yii\web\UnauthorizedHttpException('You are requesting with invalid credentials.');
				}
				
			} catch (\Exception $e) {
				Yii::warning("Valid token not found.");
			}
		}
        return null;
    }

	public static function timeoutAlert($auth){
		$username = $auth->AuthCreatedBy;
		$recentAlert = false;
		//check if recent alert exist
		$previousAlert = Alert::find()
			->where(['and',
				['Title' => self::AUTO_LOGOUT_ALERT_TITLE],
				['Username' => $username]
			])
			->orderBy(['SvrDTLT' => SORT_DESC])
			->one();
		//alert exist
		if($previousAlert != null){
			//recency check
			$alertTime = strtotime($previousAlert->CreatedDate);
			$currentTime = time();
			//time is in seconds 60 secs in a minute want to prevent new alerts for 25 minutes
			if(($currentTime-$alertTime)/60.0 < 25.0) $recentAlert = true;
		}
		//create a new alert if no recent alert exist
		if(!$recentAlert){
			//create new alert
			$newAlert = new Alert;
			//set alert values
			$newAlert->Title = self::AUTO_LOGOUT_ALERT_TITLE;
			$newAlert->CreatedDate = BaseActiveController::getDate();
			$newAlert->Username = $username;
			$newAlert->Message = 'Session expired, performed auto logout for ' . $auth->AuthCreatedBy . '.';
			$newAlert->Severity = 'High';
			//save
			$newAlert->save();
		}
	}
}
