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
	public function clearTokensByUser($userID)
	{
		Auth::setClient(BaseActiveController::urlPrefix());
		$session = Yii::$app->getSession();
		Yii::trace('User is '.$userID);
		$auth = Auth::find()
			->where(['AuthUserID' => $userID])
			->one();
		if ($auth !== null)
		{
			Yii::trace('Tokens Found');
			$auth->delete();
			Yii::trace('Tokens Removed');
		}
		Yii::trace('Token has been cleared');
	}
	
	public function clearTokenByToken($token)
	{
		Auth::setClient(BaseActiveController::urlPrefix());
		$session = Yii::$app->getSession();
		Yii::trace('Token is '.$token);
		$auth = Auth::find()
			->where(['AuthToken' => $token])
			->one();
		if ($auth !== null)
		{
			Yii::trace('Token Found');
			$auth->delete();
			Yii::trace('Token Removed');
		}
		Yii::trace('Token has been cleared');
	}
	 
	public function logout($destroySession = true, $userID = null, $token = null)
	{
		yii::trace('CtUserLogout');
		if($token != null)
		{
			$this->clearTokenByToken($token);
		}
		elseif($userID != null)
		{
			$this->clearTokensByUser($userID);
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
			$userID = $auth->AuthUserID;
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
				$auth->AuthModifiedBy = $userID;
				$auth->update();
			} else {
				$this->logout(true, null, $token);
			}
		} else {
			throw new \yii\web\HttpException(401, 'You are requesting with invalid credentials.');
		}
	}
}