<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use app\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\modules\v1\models\EmployeeType;
use yii\web\Response;
use \DateTime;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v1\modules\pge\models\WebManagementDropDownReportingGroups;
use app\modules\v1\modules\pge\models\WebManagementDropDownEmployeeType;
use app\modules\v1\modules\pge\models\WebManagementDropDownRoles;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchMapPlat;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchAssignedDispatchMethod;
use app\modules\v1\modules\pge\models\WebManagementDropDownUserWorkCenter;
use app\modules\v1\modules\pge\models\WebManagementUsers;
//assigned todo combine views
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedComplianceDate;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedDivision;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedFLOC;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedSurveyFreq;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedWorkCenter;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchStatus;
//AOC todo combine views
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCDivision;
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCSurveyor;
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCType;
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCWorkCenter;
//dispatch dropdowns
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatch;
//tablet dropdowns
use app\modules\v1\modules\pge\models\DropDowns;

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
                    'get-week-dropdown' => ['get'],
                    'get-work-center-dropdown' => ['get'], 
                    'get-employee-type-dropdown' => ['get'],
                    'get-map-plat-dropdown' => ['get'],
                    'get-surveyor-dropdown' => ['get'],
                    'get-device-id-dropdown' => ['get'],
                    'get-reporting-group-dropdown' => ['get'],
                    'get-role-dropdown' => ['get'],
                    'get-user-work-center-dropdown' => ['get'],
                    'get-user-home-work-center-dropdown' => ['get'],
					'get-dispatch-work-center-dropdown' => ['get'],
					'get-dispatch-division-dropdown' => ['get'],
					'get-dispatch-survey-freq-dropdown' => ['get'],
                    'get-dispatch-floc-dropdown' => ['get'],
                    'get-dispatch-compliance-month-dropdown' => ['get'],
                    'get-aoc-type-dropdown' => ['get'],
                    'get-aoc-surveyor-dropdown' => ['get'],
                    'get-aoc-work-center-dropdown' => ['get'],
                    'get-aoc-division-dropdown' => ['get'],
                    'get-assigned-dispatch-method-dropdown' => ['get'],
                    'get-assigned-status-dropdown' => ['get'],
                    'get-assigned-compliance-month-dropdown' => ['get'],
                    'get-assigned-floc-dropdown' => ['get'],
                    'get-assigned-survey-freq-dropdown' => ['get'],
                    'get-assigned-work-center-dropdown' => ['get'],
                    'get-assigned-division-dropdown' => ['get'],
                    'get-tablet-survey-dropdowns' => ['get'],
                ],
            ];
        return $behaviors;
    }

    public function actionGetWeekDropdown()
    {
        //TODO RBAC permission check
        //try{
        //TODO check headers
		try{
			$dropdown = [];
			$w1sun = new DateTime("07/24/2016");
			$w1sun = $w1sun->format('m/d/Y');
			$w1sat = new DateTime("07/30/2016");
			$w1sat = $w1sat->format('m/d/Y');
			$w2sun = new DateTime("07/17/2016");
			$w2sun = $w2sun->format('m/d/Y');
			$w2sat = new DateTime("07/23/2016");
			$w2sat = $w2sat->format('m/d/Y');
			$w3sun = new DateTime("07/10/2016");
			$w3sun = $w3sun->format('m/d/Y');
			$w3sat = new DateTime("07/16/2016");
			$w3sat = $w3sat->format('m/d/Y');
			//stub data
			$dropdown[$w1sun . " - " . $w1sat] = $w1sun . " - " . $w1sat;
			$dropdown[$w2sun . " - " . $w2sat] = $w2sun . " - " . $w2sat;
			$dropdown[$w3sun . " - " . $w3sat] = $w3sun . " - " . $w3sat;

			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $dropdown;
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

    /*
     * Belongs to LeakLogDetail
	 * This is using the wrong View*
     */
    public function actionGetMapPlatDependentDropdown($workCenter = null, $division = null, $surveyor = null, $date = null) {
		
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatchMapPlat::setClient($headers['X-Client']);
			
			//todo permission check
			
			$data = WebManagementDropDownDispatchMapPlat::find()
				->where(['WorkCenter'=>$workCenter])
                ->all();
            $namePairs = [];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[]=[
				'id'=>$data[$i]->MapPlat, 
				'name'=>$data[$i]->MapPlat];
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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

    public function actionGetReportDropdown() {
		try{
			$data = [];
			
			$data = [null => "Select..."];
			$data["Report 1"] = "Report 1";
			$data["Report 2"] = "Report 2";
			$data["Report 3"] = "Report 3";
			$data["Report 4"] = "Report 4";
			$data["Report 5"] = "Report 5";

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
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
	
	public function actionGetDeviceIdDropdown() {
		try{
			$data = [];
			
			$data = [null => "Select..."];
			$data["12345678"] = "12345678";
			$data["87654321"] = "87654321";
			$data["13572468"] = "13572468";
			$data["24681357"] = "24681357";

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
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
	
	public function actionGetSupervisorDropdown() {
		try{
			$data = [];
			
			$data = [null => "Select..."];
			$data["S1V1"] = "Visor, Super";
			$data["D0B0"] = "Boss, Da";
			$data["OS"] = "13572468";
			$data["24681357"] = "24681357";

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
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
	
	//dispatch, assigned
	public function actionGetUserWorkCenterDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementUsers::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementUsers::find()
				->select('WorkCenter')
				->distinct()
				->where(['not', ['WorkCenter'=> null]])
                ->all();
			$namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->WorkCenter]= $data[$i]->WorkCenter;
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	//////////////////////USER DROPDOWNS BEGIN/////////////////////
	
	//return a json containing pairs of EmployeeTypes
    public function actionGetEmployeeTypeDropdown()
    {
        try
        {
            //set db target
			$headers = getallheaders();
			WebManagementDropDownEmployeeType::setClient($headers['X-Client']);
			
			// RBAC permission check

            $types = WebManagementDropDownEmployeeType::find()
                ->all();
            $namePairs = [null => "Select..."];
            $typesSize = count($types);

            for($i=0; $i < $typesSize; $i++)
            {
                $namePairs[$types[$i]->FieldDescription]= $types[$i]->FieldDescription;
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
	
	public function actionGetReportingGroupUIDDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownReportingGroups::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownReportingGroups::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->ReportingGroupUID]= $data[$i]->GroupName;
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetReportingGroupDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownReportingGroups::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownReportingGroups::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->GroupName]= $data[$i]->GroupName;
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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

	public function actionGetRoleDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownRoles::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownRoles::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->RoleName]= $data[$i]->RoleName;
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetUserHomeWorkCenterDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownUserWorkCenter::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownUserWorkCenter::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->WorkCenterUID]= $data[$i]->WorkCenter;
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	//////////////////////USER DROPDOWNS END/////////////////////
	
	//////////////////////DISPATCH DROPDOWNS BEGIN/////////////////////
	
	public function actionGetDispatchDivisionDropdown()
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownDispatch::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownDispatch::find()
				->select('Division')
				->distinct()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->Division]= $data[$i]->Division;
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	
	//required format for the dynaic dropdowns
    //['id'=>'<sub-cat_id_2>', 'name'=>'<sub-cat-name2>']
    public function actionGetDispatchWorkCenterDropdown($division = null)
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownDispatch::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownDispatch::find()
				->select('WorkCenter')
				->distinct()
				->where(['Division'=>$division])
                ->all();
            $namePairs= [];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[]=[
				'id'=>$data[$i]->WorkCenter, 
				'name'=>$data[$i]->WorkCenter];
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	
	public function actionGetDispatchSurveyFreqDropdown($division, $workCenter) {
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatch::setClient($headers['X-Client']);
			
			//todo permission check
			
			$data = WebManagementDropDownDispatch::find()
				->select('SurveyType')
				->distinct()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
                ->all();
            $namePairs[] = [
				'id'=>'All', 
				'name'=>'All'];
            $dataSize = count($data);

			for($i=0; $i < $dataSize; $i++)
            {		
				$namePairs[]=[
				'id'=>$data[$i]->SurveyType, 
				'name'=>$data[$i]->SurveyType];
            }
			
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetDispatchFlocDropdown($division, $workCenter, $surveyType) {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatch::setClient($headers['X-Client']);
			
			//todo permission check
			$dataQuery = WebManagementDropDownDispatch::find()
				->select('FLOC')
				->distinct()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter]);
			if($surveyType!='All')
			{
				$dataQuery->andWhere(['SurveyType'=>$surveyType]);
			}
            
			$data = $dataQuery->all();

            $namePairs[] = [
				'id'=>'All', 
				'name'=>'All'];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {		
				$namePairs[]=[
				'id'=>$data[$i]->FLOC, 
				'name'=>$data[$i]->FLOC];
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetDispatchComplianceMonthDropdown($division, $workCenter, $surveyType, $floc) {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatch::setClient($headers['X-Client']);
			
			//todo permission check
			$dataQuery = WebManagementDropDownDispatch::find()
				->select('ComplianceYearMonth, ComplianceSort')
				->distinct()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter]);
			if($surveyType != 'All')
			{
				$dataQuery->andWhere(['SurveyType'=>$surveyType]);
			}
			if($floc != 'All')
			{
				$dataQuery->andWhere(['FLOC'=>$floc]);
			}	
			$data = $dataQuery->orderBy('ComplianceSort')
				->all();
            $namePairs[] = [
				'id'=>'All', 
				'name'=>'All'];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[]=[
				'id'=>$data[$i]->ComplianceYearMonth, 
				'name'=>$data[$i]->ComplianceYearMonth];
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	//////////////////////DISPATCH DROPDOWNS END/////////////////////
	
	//////////////////////ASSIGNED DROPDOWNS BEGIN/////////////////////
	
	public function actionGetAssignedDivisionDropdown()
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAssignedDivision::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownAssignedDivision::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->Division]= $data[$i]->Division;
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	
	public function actionGetAssignedWorkCenterDropdown($division)
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAssignedWorkCenter::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownAssignedWorkCenter::find()
				->where(['Division'=>$division])
                ->all();
            $namePairs= [];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[]=[
				'id'=>$data[$i]->WorkCenter, 
				'name'=>$data[$i]->WorkCenter];
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	
	 public function actionGetAssignedSurveyFreqDropdown($division, $workCenter) {
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownAssignedSurveyFreq::setClient($headers['X-Client']);
			
			//todo permission check
			
			$data = WebManagementDropDownAssignedSurveyFreq::find()
				->select('SurveyType')
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
				->andWhere(['not', ['SurveyType' => null]])
                ->all();
            $namePairs[] = [
				'id'=>'All', 
				'name'=>'All'];
            $dataSize = count($data);

			for($i=0; $i < $dataSize; $i++)
            {		
				$namePairs[]=[
				'id'=>$data[$i]->SurveyType, 
				'name'=>$data[$i]->SurveyType];
            }
			
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetAssignedFlocDropdown($division, $workCenter, $surveyType) {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownAssignedFLOC::setClient($headers['X-Client']);
			
			//todo permission check
			$dataQuery = WebManagementDropDownAssignedFLOC::find()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter]);
			if($surveyType != 'All')
			{
				$dataQuery->andWhere(['SurveyType'=>$surveyType]);
			}
            $data = $dataQuery->all();
            $namePairs[] = [
				'id'=>'All', 
				'name'=>'All'];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {		
				$namePairs[]=[
				'id'=>$data[$i]->FLOC, 
				'name'=>$data[$i]->FLOC];
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetAssignedComplianceMonthDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownAssignedComplianceDate::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownAssignedComplianceDate::find()
				->orderBy('ComplianceSort')
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->ComplianceYearMonth]= $data[$i]->ComplianceYearMonth;
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetAssignedStatusDropdown() {
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatchStatus::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownDispatchStatus::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->Status]= $data[$i]->Status;
            }

			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	
	public function actionGetAssignedDispatchMethodDropdown() {
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatchAssignedDispatchMethod::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownDispatchAssignedDispatchMethod::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->DispatchMethod]= $data[$i]->DispatchMethod;
            }
			
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $namePairs;
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
	//////////////////////ASSIGNED DROPDOWNS END/////////////////////
	
	/////////////////////AOC DROPDOWNS BEGIN////////////////////////
	public function actionGetAocDivisionDropdown()
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAOCDivision::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownAOCDivision::find()
                ->all();
            $namePairs = [null => "Select..."];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->Division]= $data[$i]->Division;
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	
	public function actionGetAocWorkCenterDropdown($division)
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAOCWorkCenter::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownAOCWorkCenter::find()
				->where(['Division'=>$division])
                ->all();
            $namePairs= [];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[]=[
				'id'=>$data[$i]->WorkCenter, 
				'name'=>$data[$i]->WorkCenter];
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	
	public function actionGetAocSurveyorDropdown($division, $workCenter)
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAOCSurveyor::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownAOCSurveyor::find()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
                ->all();
            $namePairs= [];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[]=[
				'id'=>$data[$i]->Surveyor, 
				'name'=>$data[$i]->Surveyor];
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	
	public function actionGetAocTypeDropdown($division, $workCenter, $surveyor)
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAOCType::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownAOCType::find()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
				->andWhere(['Surveyor'=>$surveyor])
                ->all();
            $namePairs= [];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[]=[
				'id'=>$data[$i]->AOCType, 
				'name'=>$data[$i]->AOCType];
            }
            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
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
	/////////////////////AOC DROPDOWNS END////////////////////////
	
	/////////////////////TABLET DROPDOWNS BEGIN////////////////////////
	//route to provide data for all survey dropdowns on the tablet
	public function actionGetTabletSurveyDropdowns()
	{
		// try
		// {
			//set db target
			$headers = getallheaders();
			DropDowns::setClient($headers['X-Client']);
			
			$responseData['SurveyDropdowns'] = [];
			
			//pipeline types
			$responseData['SurveyDropdowns']['PipelineTypes']= DropdownController::tabletSurveyQuery('ddPipelineType');
			
			//standby release reasons
			$responseData['SurveyDropdowns']['StandbyReleaseReasons']= DropdownController::tabletSurveyQuery('ddVoyStandbyReason');
			
			//AOC types
			$responseData['SurveyDropdowns']['AOCTypes']= DropdownController::tabletSurveyQuery('ddVoyAOCType');
			
			//CGI reasons
			$responseData['SurveyDropdowns']['CGIReasons']= DropdownController::tabletSurveyQuery('ddVoyCGIReasonType');
			
			//DIMP Riser types
			$responseData['SurveyDropdowns']['dimpRiserTypes']= DropdownController::tabletSurveyQuery('ddVoyDIMPRiserType');
			
			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $responseData;
			return $response;
		// }
        // catch(ForbiddenHttpException $e)
        // {
            // throw new ForbiddenHttpException;
        // }
        // catch(\Exception $e)
        // {
            // throw new \yii\web\HttpException(400);
        // }
	}
	
	//helper method for standard tablet survey query
	public static function tabletSurveyQuery($filter)
	{
		return DropDowns::find()
				->select(['FilterName', 'SortSeq', 'FieldDisplay'])
				->where(['FilterName'=>$filter])
				->andWhere(['ActiveFlag'=>1])
				->orderBy('SortSeq')
				->all();
	}
	/////////////////////TABLET DROPDOWNS END////////////////////////	
}