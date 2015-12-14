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
        $username = $request->getAuthUser();
		
		if ($username !== null) {
			Yii::$app->user->checkTimeout();
			try 
			{
				$identity = SCUser::findIdentityByAccessToken($username);
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
