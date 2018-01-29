<?php

namespace app\modules\v3\authentication;

use Yii;
use yii\web\User;
use yii\base\ErrorException;
use app\modules\v3\models\Auth;
use app\modules\v3\models\SCUser;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;

class CTUser extends User
{
	
	public function clearTokenByToken($token)
	{
		Auth::setClient(BaseActiveController::urlPrefix());
		$session = Yii::$app->getSession();
		$auth = Auth::find()
			->where(['AuthToken' => $token])
			->one();
		if ($auth !== null)
		{
			$auth->delete();
		}
	}
	 
	public function logout($destroySession = true, $token = null)
	{
		if($token != null)
		{
			$this->clearTokenByToken($token);
		}
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
			if($currentTime < $timeout)
			{
				//update timeout to current time + time limit
				$newTimeout = $currentTime + $this->authTimeout;
				$auth->AuthTimeout = $newTimeout;
				// set modified by
				$auth->AuthModifiedBy = $username;
				$auth->update();
			} else {
				$this->logout(true, $token);
			}
		} else {
			//TODO move string to constants when version is created
			throw new \yii\web\HttpException(401, 'You are requesting with invalid credentials.');
		}
	}
}