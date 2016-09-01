<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/29/2016
 * Time: 11:09 AM
 */

namespace app\modules\v1\controllers;

use yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v1\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use app\modules\v1\models\SCUser;

class PermissionsController extends Controller {

    public static function actionCheckPermission($permission) {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        if(self::can($permission)) {
            $response->data = [
                "userHasPermission" => true
            ];
        } else {
            throw new ForbiddenHttpException;
        }
    }

    public static function can($permissionName, $token = null)
    {
		SCUser::setClient(BaseActiveController::urlPrefix());
        if($token === null) {
            $token = Yii::$app->request->getAuthUser();
        }
        $user = SCUser::findIdentityByAccessToken($token);
        $userID = $user->UserID;

        if (($manager = Yii::$app->getAuthManager()) === null) {
            return false;
        }

        $access = $manager->checkAccess($userID, $permissionName);

        return $access;
    }

    public static function requirePermission($permission) {
        if(!self::can($permission)) throw new ForbiddenHttpException;
        else return true;
    }

}