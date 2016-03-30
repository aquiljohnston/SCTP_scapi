<?php

namespace  app\authentication;

use Yii;
use yii\filters\auth\AuthMethod;
use app\models\SCUser;
use app\models\Auth;
use yii\base\ErrorException;
use yii\web\Response;

class TokenAuth extends AuthMethod
{
	public $identity;
	 
    public function authenticate($user, $request, $response)
    {
        $token = $request->getAuthUser();
		try
		{
			$headers = getAllHeaders();
			SCUser::setClient($headers['X-Client']);
			Auth::setClient($headers['X-Client']);
		}
		catch(ErrorException $e)
		{
			throw new \yii\web\HttpException(400, 'Client Header Not Found.');
		}	
		
		
		if ($token !== null) {
			Yii::$app->user->checkTimeout($token);
			try 
			{
				$identity = SCUser::findIdentityByAccessToken($token);
				if ($identity === null) 
				{
					throw new UnauthorizedHttpException('You are requesting with an invalid credential.');
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
