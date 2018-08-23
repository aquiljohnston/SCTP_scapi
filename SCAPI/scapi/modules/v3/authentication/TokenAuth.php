<?php

namespace  app\modules\v3\authentication;

use Yii;
use yii\filters\auth\AuthMethod;
use app\modules\v3\models\SCUser;
use app\modules\v3\models\Auth;
use yii\base\ErrorException;
use yii\web\Response;
use app\modules\v3\controllers\BaseActiveController;

class TokenAuth extends AuthMethod
{
	public $identity;
	 
    public function authenticate($user, $request, $response)
    {
		SCUser::setClient(BaseActiveController::urlPrefix());
		
        $token = $request->getAuthUser();
		
		if ($token !== null && $token !== '') {
			Yii::$app->user->checkTimeout($token);
			try {
				$identity = SCUser::findIdentityByAccessToken($token);
				if ($identity !== null) {
					//check for client header
					try {
						getAllHeaders()['X-Client'];
					} catch(ErrorException $e) {	
						BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
						throw new \yii\web\HttpException(400, 'Client Header Not Found.');
					}
					return $identity;
				} else { 
					//TODO move string to constants when version is created
					throw new \yii\web\UnauthorizedHttpException('You are requesting with invalid credentials.');
				}
				
			} catch (\Exception $e) {
				Yii::warning("Valid token not found.");
			}
		}
        return null;
    }

}
