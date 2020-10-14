<?php

namespace app\modules\v2\controllers;

use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Report;
use Yii;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ReportsController extends Controller {
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //Implements Token Authentication to check for Auth Token in Json  Header
        $behaviors['authenticator'] =
            [
                'class' => TokenAuth::className(),
            ];
        $behaviors['verbs'] =
            [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-report-drop-down' => ['get'],
                    'get-report' => ['get'],
					'get-parm-dropdown' => ['get'],
					'get-inspector-dropdown' => ['get'],
                ],
            ];
        return $behaviors;
    }

    public function actionGetReportDropDown() 
	{

        $client = getallheaders()['X-Client'];
        Report::setClient($client);
		
		//RBAC Permissions Check
		PermissionsController::requirePermission('reportGetDropdown', $client);

        $result = Report::find()
            ->select('ReportDisplayName, ReportSPName, ParmDateFlag, ParmDateOverrideFlag, ParmBetweenDateFlag, ExportFlag, ParmInspectorFlag, ParmDropDownFlag, ParmProjectFlag, Parm, ReportType, ActiveFlag, ParmClientFlag')
            ->where(['ActiveFlag' => 1])
            ->orderBy(['ReportDisplayName'=>SORT_ASC])
            ->asArray()
            ->all();

        $data['reports'] = $result;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }
    /*
     * todo:　need to call SP or View to get report data
     * @return Json format
     * {
            "data": [],
            "columns": [{
                "title": "Report Date"
            }, {
                "title": "Report Title"
            }, {
                "title": "COMPANY_NO"
            }, {
                "title": "INSP_TYPE"
            }, {
                "title": "Survey_Period"
            }, {
                "title": "Completed_Asset_Count"
            }]
        }
     */
	 //TODO inspector filter for views
    public function actionGetReport($reportType, $reportName, $reportID = null, $parm = null, $startDate = null, $endDate = null, $ParmInspector = null)
	{
		$client = getallheaders()['X-Client'];
        BaseActiveRecord::setClient($client);
		
		//RBAC Permissions Check
		PermissionsController::requirePermission('reportGet', $client);
		
		$response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
		
		$connection = BaseActiveRecord::getDb();
		
		//handle stored procedure based report
		if ($reportType == 'SP')
		{	
			if($startDate == null && $endDate == null)
			{
				$queryString = "SET NOCOUNT ON; EXEC " . $reportName ;
				
				$queryResults = $connection->createCommand($queryString)
				->queryAll();
			}
			elseif($startDate == null && $endDate != null)
			{
                $queryString = "EXEC " . $reportName . " " . $reportID . "," . "'" . $parm . "'" . ", " . "'" . $endDate . "'";
                 Yii::trace("DB QUERY 0: ".$queryString);
				
				$queryResults = $connection->createCommand($queryString)
				->queryAll();
			}
			elseif ($startDate != null && $endDate != null)
			{
			    if ($ParmInspector == null && $parm == null) {
                    $queryString = "SET NOCOUNT ON; EXEC " . $reportName . " " . "'" . $startDate . "'" . ", " . "'" . $endDate . "'";
                     Yii::trace("DB QUERY 1: ".$queryString);
                    $queryResults = $connection->createCommand($queryString)
                        ->queryAll();
                } else if ($parm != null) {
                    $queryString = "SET NOCOUNT ON; EXEC " . $reportName . " " . "'" . $startDate . "'" . ", " . "'" . $endDate . "'" . ", " . "'" . $parm . "'";
                     Yii::trace("DB QUERY 1: ".$queryString);
                    $queryResults = $connection->createCommand($queryString)
                        ->queryAll();
                }else{
                    $queryString = "SET NOCOUNT ON; EXEC " . $reportName . " " . "'" . $startDate . "'" . ", " . "'" . $endDate . "'" . ", " . "'" . $ParmInspector . "'";
                    Yii::trace("DB QUERY: ".$queryString);

                    $queryResults = $connection->createCommand($queryString)
                        ->queryAll();
                }
			}
		}
		//handle view based report
		elseif ($reportType == 'View')
		{
			if ($startDate == null && $endDate != null)
			{
				$queryString ="SELECT * FROM " . $reportName . " WHERE SurveyDate = " . "'" . $endDate ."'";
				
				$queryResults = $connection->createCommand($queryString)
				->queryAll();
			}
			elseif($startDate != null && $endDate != null)
			{
				$queryString ="SELECT * FROM " . $reportName . " WHERE SurveyDate BETWEEN " . "'" . $startDate ."'" . "AND" . "'" . $endDate . "'";
				
				$queryResults = $connection->createCommand($queryString)
				->queryAll();
			}
			elseif($startDate == null && $endDate == null)
			{
				$queryString ="SELECT * FROM " . $reportName;
				
				$queryResults = $connection->createCommand($queryString)
				->queryAll();
			}
		}
		
		//format response data
		$responseData['data'] = [];
		$responseData['columns'] = [];
		
		$resultCount = count($queryResults);
		
		for($i = 0; $i < $resultCount; $i++)
		{
			$responseData['data'][] = array_values($queryResults[$i]);
		}
		
		if($resultCount > 0)
		{
			$keys = array_keys($queryResults[0]);
			$keyCount = count($keys);
			
			for($k=0; $k < $keyCount; $k++)
			{
				$responseData['columns'][]['title'] = $keys[$k];
			}
		}
		
		$response->data = $responseData;
		return $response;
    }

    /*
     * todo: get Parm Drop Down list
     * @return Json format
     */
    public function actionGetParmDropdown($spName = null,$startDate = null, $endDate = null)
	{
		try
		{
            $client = getallheaders()['X-Client'];
            BaseActiveRecord::setClient($client);
			
			//RBAC Permissions Check
			PermissionsController::requirePermission('reportGetParmDropdown', $client);
			
            $connection = BaseActiveRecord::getDb();
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            if ($spName != null){
                $queryString = "EXEC " . $spName . " " . "'" . $startDate . "'" . ", " . "'" . $endDate . "'";

                $queryResults = $connection->createCommand($queryString)
                    ->queryAll();

                $responseData['options'] = self::formatDropdowns($queryResults);
            }else{
                $responseData['options'] = [];
            }

            $response->data = $responseData;
            return $response;
		}
		catch(ForbiddenHttpException $e)
		{
			throw new ForbiddenHttpException;
		}
		catch(Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
    }

    /**
     * @param $spName
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function actionGetInspectorDropdown($spName = null, $startDate = null, $endDate = null)
	{
		try
		{
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $client = getallheaders()['X-Client'];
            BaseActiveRecord::setClient($client);
			
			//RBAC Permissions Check
			PermissionsController::requirePermission('reportGetInspectorDropdown', $client);
			
            $connection = BaseActiveRecord::getDb();
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            if ($spName != null){
                $queryString = "EXEC " . $spName . " " . "'" . $startDate . "'" . ", " . "'" . $endDate . "'";

                $queryResults = $connection->createCommand($queryString)
                    ->queryAll();

                $responseData['inspectors'] = self::formatDropdowns($queryResults);

                $response->data = $responseData;
                return $response;
            }else{
                $responseData['inspectors'] = [];

                $response->data = $responseData;
                return $response;
            }
		}
		catch(ForbiddenHttpException $e)
		{
			throw new ForbiddenHttpException;
		}
		catch(Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
    }

    /**
     * @param $data
     * @return array
     */
    private static function formatDropdowns($data)
	{
		$formatedData = [];
		
		$dataCount = count($data);
		
		for ($i = 0; $i < $dataCount; $i++)
		{
			$formatedData[] = $data[$i]['Drop_Down'];
		}
		
		return $formatedData;
	}

	private static function formatInspectorDropdowns($data){
	    $formatedData = [];
        $dataCount = count($data);

        for ($i = 0; $i < $dataCount; $i++)
        {
            //$formatedData['DisplayName'] = $data[$i]['UserFirstName'] .", ". $data[$i]['UserLastName'];
            $formatedData[$i]['userNameData'] = $data[$i]['UserName'];
            $formatedData[$i]['displayNameData'] = $data[$i]['UserLastName'] .", ". $data[$i]['UserFirstName'] . " (" . $data[$i]['UserName'] . ")";
        }

        return $formatedData;
    }
}