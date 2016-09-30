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
	public function clearToken($userID)
	{
		Auth::setClient(BaseActiveController::urlPrefix());
		$session = Yii::$app->getSession();
		Yii::trace('Id of token is '.$userID);
		$auth = Auth::find()
			->where(['AuthUserID' => $userID])
			->one();
		Yii::trace('Token Found');
		if ($auth !== null)
		{
			$auth->delete();
			Yii::trace('Token Removed');
		}
		Yii::trace('Token has been cleared');
	}
	 
	public function logout($destroySession = true, $userID = null)
	{
		$this->clearToken($userID);
		parent::logout();
	}
	
	public function checkTimeout($token)
	{
		$this->renewAuthStatusWithToken($token);
	}
	
	protected function renewAuthStatus()
	{
		$session = Yii::$app->getSession();
        $id = $session->getHasSessionId() || $session->getIsActive() ? $session->get($this->idParam) : null;
		Yii::trace('The current user id is'.$id);

        if ($id === null) {
            $identity = null;
        } else {
            /* @var $class IdentityInterface */
            $class = $this->identityClass;
            $identity = $class::findIdentity($id);
        }

        $this->setIdentity($identity);

        if ($identity !== null && ($this->authTimeout !== null || $this->absoluteAuthTimeout !== null)) {
            $expire = $this->authTimeout !== null ? $session->get($this->authTimeoutParam) : null;
            $expireAbsolute = $this->absoluteAuthTimeout !== null ? $session->get($this->absoluteAuthTimeoutParam) : null;
            if ($expire !== null && $expire < time() || $expireAbsolute !== null && $expireAbsolute < time()) {
				Yii::trace('AuthTimeout has expired and the user will now be logged out');
				Yii::trace('The current user id now is'.$id);
				$this->logout(true, $id);
            } elseif ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
        }
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
				$this->logout(true, $userID);
			}
		} else {
			throw new \yii\web\HttpException(401, 'You are requesting with an invalid credential.');
		}
	}
}