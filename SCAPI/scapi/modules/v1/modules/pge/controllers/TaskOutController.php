<?php
/**
 * Created by PhpStorm.
 * User: jpatton
 * Date: 9/23/2016
 * Time: 9:58 AM
 */

namespace app\modules\v1\modules\pge\controllers;


use app\modules\v1\models\SCUser;
use Yii;
use yii\db\Exception;
use yii\web\Controller;

class TaskOutController extends Controller {

    /**
     * @param $JSON
     * @return bool
     * @throws Exception
     */
    public static function processJSON($JSON) {
        //PermissionsController::requirePermission('taskOut'); //TODO: Update with correct permission

        Yii::trace("JSON string is: " . $JSON);
        $connection = SCUser::getDb();
        $processJSONCommand = $connection->createCommand("EXECUTE spJSON_TaskOut :PARAMETER1");
        $processJSONCommand->bindParam(':PARAMETER1', $JSON,  \PDO::PARAM_STR);
        $processJSONCommand->execute();
        return true;
    }
}