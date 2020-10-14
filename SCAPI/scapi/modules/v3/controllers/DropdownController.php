<?php

namespace app\modules\v3\controllers;

use app\modules\v3\models\BaseActiveRecord;
use Yii;
use app\modules\v3\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\modules\v3\models\EmployeeType;
use app\modules\v3\models\DropDown;
use app\modules\v3\models\AppRoles;
use app\modules\v3\models\StateCode;
use app\modules\v3\controllers\BaseActiveController;
use yii\web\Response;
use \DateTime;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;


class DropdownController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //Implements Token Authentication to check for Auth Token in Json Header
        $behaviors['authenticator'] =
            [
                'class' => TokenAuth::className(),
            ];
        $behaviors['verbs'] =
            [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-employee-type-dropdown' => ['get'],
                    'get-tablet-survey-dropdowns' => ['get'],
                    'get-state-codes-dropdown' => ['get'],
                    'get-web-drop-downs' => ['get']
                ],
            ];
        return $behaviors;
    }

    //return a json containing pairs of EquipmentTypes
    public function actionGetStateCodesDropdown()
    {
        try
        {
            //set db target
            StateCode::setClient(BaseActiveController::urlPrefix());

            // RBAC permission check
            PermissionsController::requirePermission('stateCodeGetDropdown');

            $codes = StateCode::find()
                ->all();
            $namePairs = [null => "None"];
            $tempPairs = [];
            $codesSize = count($codes);

            for($i=0; $i < $codesSize; $i++)
            {
                $namePairs[$codes[$i]->StateNames]= $codes[$i]->StateNumber . ": " . $codes[$i]->StateNames ;
            }
            $namePairs = $namePairs + $tempPairs;

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $namePairs;

            return $response;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
    }

    //return a json containing pairs of EmployeeTypes
    public function actionGetEmployeeTypeDropdown()
    {
        try
        {
            //set db target
            $headers = getallheaders();
            EmployeeType::setClient(BaseActiveController::urlPrefix());

			// RBAC permission check
			PermissionsController::requirePermission('employeeTypeGetDropdown');

            $types = EmployeeType::find()
                ->all();
            $namePairs = [];
            $typesSize = count($types);

            for($i=0; $i < $typesSize; $i++)
            {
                $namePairs[$types[$i]->EmployeeTypeType]= $types[$i]->EmployeeTypeType;
            }


            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $namePairs;

            return $response;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
    }
	
	//get dropdowns from rDropDown
	public function actionGetDropdowns($filter)
	{
		try
        {
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$webDropDowns = DropDown::find()
				->select(['FilterName', 'SortSeq', 'FieldDisplay', 'FieldValue'])
				->distinct()
				->where(['DropDownType' => $filter])
				->orderBy([
					  'FilterName' => SORT_ASC,
					  'SortSeq' => SORT_ASC
					])
				->all();
				
			$responseArray['Dropdowns'] = [];
			//loop data to format response
			foreach($webDropDowns as $dropDown)
			{
				$responseArray['Dropdowns'][$dropDown->FilterName][] = $dropDown;
			}
			
            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $responseArray;

            return $response;
		}
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}

    public function actionGetTrackerMapGrids() {
        $headers = getallheaders();
        BaseActiveRecord::setClient($headers['X-Client']);
//        $sql =    "SELECT DISTINCT MapGrid FROM tWorkQueue "
//                . "JOIN tWorkOrder ON tWorkQueue.WorkOrderID = tWorkOrder.ID "
//                . "WHERE tWorkQueue.WorkQueueStatus = 101 OR tWorkQueue.WorkQueueStatus = 102";
        $sql = "SELECT DISTINCT [Mapgrid] FROM [ScctTemplate].[dbo].[vRptCompletedWorkOrders]";
        $connection = BaseActiveRecord::getDb();
        $results = $connection->createCommand($sql)->queryAll();

        //These next four lines convert the data from
        //[{"MapGrid": "XX-YYY"},...] to {"XX-YYY": "XX-YYY",...}
        $processedResults = [];
        foreach($results as $result) {
            $processedResults[$result['Mapgrid']] = $result['Mapgrid'];
        }

        $response = Yii::$app ->response;
        $response -> format = Response::FORMAT_JSON;
        $response -> data = $processedResults;
    }
	
	//return
	/**
	 * Route to get the dropdown
	 * 
	 * The pairing of equal Strings for both key and value is done because the front end expects
	 * an associative array. We use the display name as the key for convenience.
	 *
	 * @return Response A JSON associative array containing pairs of AppRoleNames
	 * @throws \yii\web\HttpException
	 */
	public function actionGetRolesDropdowns($type){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('appRoleGetDropdown');
		
			$roles = AppRoles::find()
				->all();
			$namePairs = [];
			$rolesSize = count($roles);
			
			//get active client db to check create permissions
			$client = getallheaders()['X-Client'];
			
			for($i=0; $i < $rolesSize; $i++){
				if(PermissionsController::can('user' . $type . $roles[$i]->AppRoleName, null, $client))
					$namePairs[$roles[$i]->AppRoleName]= $roles[$i]->AppRoleName;
			}
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $namePairs;
			
			return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
	}
}