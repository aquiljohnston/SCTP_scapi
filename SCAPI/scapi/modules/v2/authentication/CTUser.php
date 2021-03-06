<?php

namespace app\modules\v2\authentication;

use Yii;
use yii\web\User;
use yii\base\ErrorException;
use app\modules\v2\models\Auth;
use app\modules\v2\models\SCUser;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;

class CTUser extends User
{
	
	public function clearTokenByToken($token)
	{
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
		//set token value to empty string
		Auth::updateAll(['AuthToken' => ''], ['AuthToken' => $token]);
	}
	 
	public function logout($destroySession = true, $token = null)
	{
		$this->clearTokenByToken($token);
		parent::logout();
	}
	
	public function checkTimeout($token)
	{
		$this->renewAuthStatusWithToken($token);
	}
		
	protected function renewAuthStatusWithToken($token)
	{
		Auth::setClient(BaseActiveController::urlPrefix());
		if($auth = Auth::find()
				->where(['AuthToken' => $token])
				->one())
		{
			$username = $auth->AuthUserID;
			//get currentTime
			$currentTime = time();//get time
			$timeout = $auth->AuthTimeout;
			//check timeout vs current time
			//commented out rewnewal of auth token
			// if($currentTime < $timeout)
			// {
				// //update timeout to current time + time limit
				// $newTimeout = $currentTime + $this->authTimeout;
				// $auth->AuthTimeout = $newTimeout;
				// // set modified by
				// $auth->AuthModifiedBy = $username;
				// $auth->update();
			// } else {
				// $this->logout(true, $token);
			// }
			if($currentTime > $timeout) {
				//log alert for timeout
				TokenAuth::timeoutAlert($auth);
				$this->logout(true, $token);
			}
		} else {
			//TODO move string to constants when version is created
			throw new \yii\web\UnauthorizedHttpException('You are requesting with invalid credentials.');
		}
	}
}