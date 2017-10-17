<?php

namespace  app\authentication;

use Yii;
use yii\filters\auth\AuthMethod;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\Auth;
use yii\base\ErrorException;
use yii\web\Response;
use app\modules\v1\controllers\BaseActiveController;

class TokenAuth extends AuthMethod
{
	public $identity;
	 
    public function authenticate($user, $request, $response)
    {
		SCUser::setClient(BaseActiveController::urlPrefix());
		
        $token = $request->getAuthUser();
		
		//check for client header
		$headers = getAllHeaders();
		try
		{
			$headers['X-Client'];
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
					throw new \yii\web\HttpException(401, 'You are requesting with invalid credentials.');
				}
				return $identity;
			}
			catch (\Exception $e) 
			{
				Yii::warning("Valid token not found.");
			}
		}

        return null;
    }

}
