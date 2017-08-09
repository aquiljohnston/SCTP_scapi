<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 6/29/2016
 * Time: 11:09 AM
 */

namespace app\modules\v2\controllers;

use yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use app\rbac\ScDbManager;
use app\rbac\ClientDbManager;

class PermissionsController extends Controller {

    public static function actionCheckPermission($permission) {
		//get client
		$client = getallheaders()['X-Client'];
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        if(self::can($permission, null, $client)) {
            $response->data = [
                "userHasPermission" => true
            ];
        } else {
            throw new ForbiddenHttpException;
        }
    }

    public static function can($permissionName, $token = null, $client = null)
    {
		$nullClient = false;
		if($client == null)
		{
			$nullClient = true;
			$client = BaseActiveController::urlPrefix();
		}
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
        if($token === null) {
            $token = Yii::$app->request->getAuthUser();
        }
		$user = BaseActiveController::getClientUser($client);
		
		//handle if user could not be found
		if ($user == null)
		{
			return false;
		}
		
        $userID = $user->UserID;

		BaseActiveRecord::setClient($client);
		$db = BaseActiveRecord::getDb();
		
		if($nullClient || BaseActiveController::isSCCT($client))
		{
			if (($manager = new ScDbManager()) === null) {
				return false;
			}
		}
		else
		{
			if (($manager = new ClientDbManager($db)) === null) {
				return false;
			}
		}

        $access = $manager->checkAccess($userID, $permissionName);

        return $access;
    }

    public static function requirePermission($permission, $client = null) {
        if(!self::can($permission, null, $client)) throw new ForbiddenHttpException;
        else return true;
    }

}