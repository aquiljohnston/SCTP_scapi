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
		
		//check for client header
		$headers = getAllHeaders();
		if($headers['X-Client'] == null)
		{
			throw new \yii\web\HttpException(400, 'Client Header Not Found.');
		}
		SCUser::setClient('CometTracker');
		Auth::setClient('CometTracker');
		
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
