<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/29/2016
 * Time: 11:09 AM
 */

namespace app\modules\v1\modules\pge\controllers;

use yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use app\modules\v1\models\SCUser;
use app\rbac\PgeDbManager;

class PgePermissionsController extends Controller {

    public static function actionCheckPermission($permission) {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
		$headers = getallheaders();
        if(self::can($permission, $headers['X-Client'])) {
            $response->data = [
                "userHasPermission" => true
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
		SCUser::setClient(BaseActiveController::urlPrefix());
        if($token === null) {
            $token = Yii::$app->request->getAuthUser();
        }
        $user = SCUser::findIdentityByAccessToken($token);
        $userUID = $user->UserUID;
		
		BaseActiveRecord::setClient($client);
		$db = BaseActiveRecord::getDb();
		
        if (($manager = new PgeDbManager($db)) === null) {
            return false;
        }

        $access = $manager->checkAccess($userUID, $permissionName);

        return $access;
    }

    public static function requirePermission($permission, $client) {
        if(!self::can($permission, $client)) throw new ForbiddenHttpException;
        else return true;
    }

}