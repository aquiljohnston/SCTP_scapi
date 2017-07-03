<?php

namespace app\modules\v2\controllers;

use app\authentication\TokenAuth;
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
                    'get-sp-report' => ['get'],
					'get-view-report' => ['get'],
					'get-Parm-DropDown' => ['get']
                ],
            ];
        return $behaviors;
    }

    public function actionGetReportDropDown() 
	{

        $headers = getallheaders();
        Report::setClient($headers['X-Client']);

        $result = Report::find()
            ->select('ReportDisplayName, ReportSPName, ParmDateFlag, ParmDateOverrideFlag, ParmBetweenDateFlag, ExportFlag, ParmInspectorFlag, ParmDropDownFlag, Parm, ReportType, ActiveFlag')
            ->where(['ActiveFlag' => 1])
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
		$headers = getallheaders();
        BaseActiveRecord::setClient($headers['X-Client']);
		
		$response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
		
		$connection = BaseActiveRecord::getDb();
		
		//handle stored procedure based report
		if ($reportType == 'SP')
		{	
			if($startDate == null && $endDate != null)
			{
                $queryString = "EXEC " . $reportName . " " . $reportID . "," . "'" . $parm . "'" . ", " . "'" . $endDate . "'";
				
				$queryResults = $connection->createCommand($queryString)
				->queryAll();
			}
			elseif ($startDate != null && $endDate != null)
			{
			    if ($ParmInspector != null) {
                    $queryString = "SET NOCOUNT ON; EXEC " . $reportName . " " . "'" . $startDate . "'" . ", " . "'" . $endDate . "'";

                    $queryResults = $connection->createCommand($queryString)
                        ->queryAll();
                }else{
                    $queryString = "SET NOCOUNT ON; EXEC " . $reportName . " " . "'" . $startDate . "'" . ", " . "'" . $endDate . "'" . ", " . "'" . $ParmInspector . "'";

                    $queryResults = $connection->createCommand($queryString)
                        ->queryAll();
                }
			}
			elseif($startDate == null && $endDate == null)
			{
				$queryString = "SET NOCOUNT ON; EXEC " . $reportName ;
				
				$queryResults = $connection->createCommand($queryString)
				->queryAll();
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
    public function actionGetParmDropdown($spName)
	{
		try
		{
			$headers = getallheaders();
			BaseActiveRecord::setClient($headers['X-Client']);
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			$connection = BaseActiveRecord::getDb();
			
			$queryResults = $connection->createCommand("EXEC " . $spName)
			->queryAll();
			
			$responseData['options'] = self::formatDropdowns($queryResults);
			
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
     * @param $viewName
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function actionGetInspectorDropdown($viewName = null, $startDate = null, $endDate = null)
	{
		try
		{
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

		    if ($viewName != null) {
                $headers = getallheaders();
                BaseActiveRecord::setClient($headers['X-Client']);

                $connection = BaseActiveRecord::getDb();

                $queryString = "SELECT * FROM " . $viewName;

                $queryResults = $connection->createCommand($queryString)
                    ->queryAll();

                $responseData['inspectors'] = self::formatInspectorDropdowns($queryResults);

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
            $formatedData[] = $data[$i]['UserName'];
        }

        return $formatedData;
    }
}