<?php

namespace app\modules\v3\controllers;

use yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

class PermissionsController extends Controller
{
    /**
     * @param $permission
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public static function actionCheckPermission($permission) {
		//get client
        try{
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
        } catch (ForbiddenHttpException $e) {
            BaseActiveController::logError($e, 'Forbidden http exception!');
            throw new ForbiddenHttpException;
        } catch (UnauthorizedHttpException $e) {
            BaseActiveController::logError($e, 'Unauthorized http exception!');
            throw new UnauthorizedHttpException;
        }
    }

    /**
     * @param $permissionName
     * @param null $token
     * @param null $client
     * @return bool
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public static function can($permissionName, $token = null, $client = null)
    {
		BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
        if($token === null) {
            $token = Yii::$app->request->getAuthUser();
        }
		if($client == null)
		{
			$client = BaseActiveController::urlPrefix();
		}
		$user = BaseActiveController::getClientUser($client);
		//handle if user could not be found
		if ($user == null)
		{
			throw new ForbiddenHttpException;
		}
		
        $userID = $user->UserID;

		BaseActiveRecord::setClient($client);
		$db = BaseActiveRecord::getDb();
		
		$authClass = BaseActiveRecord::getAuthManager($client);
		if(($manager = new $authClass($db)) === null) {
			return false;
		}
		
        $access = $manager->checkAccess($userID, $permissionName);

        return $access;
    }

    /**
     * @param $permission
     * @param null $client
     * @return bool
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public static function requirePermission($permission, $client = null) {
        if(!self::can($permission, null, $client))
            throw new ForbiddenHttpException;
        else
            return true;
    }

}