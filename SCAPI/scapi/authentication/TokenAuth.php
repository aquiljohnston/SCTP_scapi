<?php

namespace  app\authentication;

use Yii;
use yii\filters\auth\AuthMethod;
use app\models\SCUser;
use yii\base\ErrorException;

class TokenAuth extends AuthMethod
{
	public $identity;
	 
    public function authenticate($user, $request, $response)
    {
        $token = $request->getAuthUser();
		
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
