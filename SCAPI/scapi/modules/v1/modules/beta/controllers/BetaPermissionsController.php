<?php

namespace app\modules\v1\modules\beta\controllers;

use yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\BaseUser;
use app\rbac\BetaDbManager;

class BetaPermissionsController extends Controller {

    public static function actionCheckPermission($permission) {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
		$headers = getallheaders();
        if(self::can($permission, $headers['X-Client'])) {
            $response->data = [
                'userHasPermission' => true
            ];
        } else {
            throw new ForbiddenHttpException;
        }
    }

    public static function can($permissionName, $client,  $token = null)
    {
        // if (YII_ENV_DEV && defined('DEV_DISABLE_PERMISSION_CHECK') && DEV_DISABLE_PERMISSION_CHECK) {
            // return true;
        // }
		//get token from base data base
		SCUser::setClient(BaseActiveController::urlPrefix());
        if($token === null) {
            $token = Yii::$app->request->getAuthUser();
        }
        $user = SCUser::findIdentityByAccessToken($token);
        $username = $user->UserName;
		
		BaseActiveRecord::setClient($client);
		$db = BaseActiveRecord::getDb();
		
		//find user in beta database
		$betaUser = BaseUser::find()
			->where(['UserName' => $username])
			->one();
		//get user id
		$userID = $betaUser->UserID;
		
		//create new instance of db manager
        if (($manager = new BetaDbManager($db)) === null) {
            return false;
        }
		
		//check permissions
        $access = $manager->checkAccess($userID, $permissionName);

        return $access;
    }

    public static function requirePermission($permission, $client) {
        if(!self::can($permission, $client)) throw new ForbiddenHttpException;
        else return true;
    }

}