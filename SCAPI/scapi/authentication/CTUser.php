<?php

namespace app\authentication;

use Yii;
use yii\web\User;
use yii\base\ErrorException;
use app\modules\v1\models\Auth;
use app\modules\v1\models\SCUser;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;

class CTUser extends User
{
	public function clearTokenByUser($username)
	{
		Auth::setClient(BaseActiveController::urlPrefix());
		$session = Yii::$app->getSession();
		$auth = Auth::find()
			->where(['AuthUserID' => $username])
			->one();
		if ($auth !== null)
		{
			$auth->delete();
		}
	}
	
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
	 
	public function logout($destroySession = true, $username = null, $token = null)
	{
		if($token != null)
		{
			$this->clearTokenByToken($token);
		}
		elseif($username != null)
		{
			$this->clearTokenByUser($username);
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
				$this->logout(true, null, $token);
			}
		} else {
			throw new \yii\web\HttpException(401, 'You are requesting with invalid credentials.');
		}
	}
}