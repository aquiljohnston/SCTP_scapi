<?php

namespace app\authentication;

use Yii;
use yii\web\User;
use yii\base\ErrorException;
use app\models\Auth;
use app\models\SCUser;

class CTUser extends User
{
		 
	public function clearToken()
	{
		Yii::trace('Clearing Token From DB');
		$session = Yii::$app->getSession();
        $userID = $session->getHasSessionId() || $session->getIsActive() ? $session->get($this->idParam) : null;
		Yii::trace('Id of token is '.$userID);
					$auth = Auth::find()
						->where(['UserID' => $userID])
						->one();
					if ($auth !== null)
					{
						$auth->delete();
					}
		Yii::trace('Token has been cleared');
	}
	 
	public function logout($destroySession = true)
	{
		$this->clearToken();
		parent::logout();
	}
	
	public function checkTimeout()
	{
		$this->renewAuthStatus();
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
				$this->logout(false);
            } elseif ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
        }
	}
}