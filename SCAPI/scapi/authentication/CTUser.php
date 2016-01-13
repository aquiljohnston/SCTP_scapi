<?php<?php

namespace app\authentication;

use Yii;
use yii\web\User;
use yii\base\ErrorException;
use app\models\Auth;
use app\models\SCUser;

class CTUser extends User
{
	public function clearToken($userID)
	{
		Yii::trace('Clearing Token From DB');
		$session = Yii::$app->getSession();
		Yii::trace('Id of token is '.$userID);
					$auth = Auth::find()
						->where(['AuthUserID' => $userID])
						->one();
					if ($auth !== null)
					{
						$auth->delete();
					}
		Yii::trace('Token has been cleared');
	}
	 
	public function logout($destroySession = true, $userID)
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
	
	//renew auth with token passed from frontend
	protected function renewAuthStatusWithToken($token)
	{
		$session = Yii::$app->getSession();
        // $id = $session->getHasSessionId() || $session->getIsActive() ? $session->get($this->idParam) : null;
		
		$auth = Auth::find()
						->where(['AuthToken' => $token])
						->one();
					if ($auth !== null)
					{
						$userID = $auth->AuthUserID;
					}
		Yii::trace('The current user id is'.$userID);

        if ($userID === null) {
            $identity = null;
        } else {
            /* @var $class IdentityInterface */
            $class = $this->identityClass;
            $identity = $class::findIdentity($userID);
        }

        $this->setIdentity($identity);

        if ($identity !== null && ($this->authTimeout !== null || $this->absoluteAuthTimeout !== null)) {
            $expire = $this->authTimeout !== null ? $session->get($this->authTimeoutParam) : null;
            $expireAbsolute = $this->absoluteAuthTimeout !== null ? $session->get($this->absoluteAuthTimeoutParam) : null;
            if ($expire !== null && $expire < time() || $expireAbsolute !== null && $expireAbsolute < time()) {
				Yii::trace('AuthTimeout has expired and the user will now be logged out');
				$this->logout(true, $userID);
            } elseif ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
        }
	}
}