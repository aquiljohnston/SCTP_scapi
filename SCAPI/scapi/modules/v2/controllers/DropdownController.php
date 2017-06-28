<?php

namespace app\modules\v2\controllers;

use app\modules\v2\models\BaseActiveRecord;
use Yii;
use app\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\modules\v2\models\EmployeeType;
use app\modules\v2\models\DropDown;
use app\modules\v2\controllers\BaseActiveController;
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
                ],
            ];
        return $behaviors;
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
            $processedResults[$result['MapGrid']] = $result['MapGrid'];
        }

        $response = Yii::$app ->response;
        $response -> format = Response::FORMAT_JSON;
        $response -> data = $processedResults;
    }
	
	/////////////////////TABLET DROPDOWNS BEGIN////////////////////////
	//route to provide data for all survey dropdowns on the tablet
	public function actionGetTabletSurveyDropdowns()
	{
		try
		{
			//set db target
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$tabletDropdowns = DropDown::find()
				->select('FilterName')
				->distinct()
				->where(['DropDownType' => 'Tablet'])
				->all();
				
			$responseData['SurveyDropdowns'] = [];
			
			for($i = 0; $i < count($tabletDropdowns); $i++)
			{
				$responseData['SurveyDropdowns'][$tabletDropdowns[$i]['FilterName']]= DropdownController::tabletSurveyQuery($tabletDropdowns[$i]['FilterName']);
			}

			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}

	//helper method for standard tablet survey query
	public static function tabletSurveyQuery($filter)
	{
		return DropDown::find()
				->select(['FilterName', 'SortSeq', 'FieldDisplayValue'])
				->where(['FilterName'=>$filter])
				//no active flag present may be used later.
				//->andWhere(['ActiveFlag'=>1])
				->orderBy('SortSeq')
				->all();
	}
	/////////////////////TABLET DROPDOWNS END////////////////////////
}