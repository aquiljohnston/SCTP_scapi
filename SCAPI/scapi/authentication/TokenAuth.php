<?php

namespace  app\authentication;

use Yii;
use yii\filters\auth\AuthMethod;
use app\models\SCUser;
use app\models\Auth;
use yii\base\ErrorException;

class TokenAuth extends AuthMethod
{
	public $identity;
	 
    public function authenticate($user, $request, $response)
    {
        $token = $request->getAuthUser();
		$headers = getAllHeaders();
		SCUser::setClient($headers['X-Client']);
		Auth::setClient($headers['X-Client']);
		
		if ($token !== null) {
			Yii::$app->user->checkTimeout($token);
			try 
			{
				$identity = SCUser::findIdentityByAccessToken($token);
				if ($identity === null) 
				{
					$this->handleFailure($response);
				}
				return $identity;
			}
			catch (ErrorException $e) 
			{
				Yii::warning("Token not found.");
			}
		}

        return null;
    }

}
