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
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchComplianceDate;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchMapPlat;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchWorkCenter;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchDivision;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchStatus;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchSurveyType;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchAssignedDispatchMethod;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchFLOC;
use app\modules\v1\modules\pge\models\WebManagementDropDownUserWorkCenter;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedComplianceDate;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedDivision;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedFLOC;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedSurveyFreq;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssignedWorkCenter;


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
                    'get-work-center-dependent-dropdown' => ['get'],
                    'get-employee-type-dropdown' => ['get'],
                    'get-map-plat-dropdown' => ['get'],
                    'get-surveyor-dropdown' => ['get'],
                    'get-division-dropdown' => ['get'],
                    'get-device-id-dropdown' => ['get'],
                    'get-reporting-group-dropdown' => ['get'],
                    'get-role-dropdown' => ['get'],
                    'get-dispatch-method-dropdown' => ['get'],
                    'get-user-work-center-dropdown' => ['get'],
                    'get-user-home-work-center-dropdown' => ['get'],
                    'get-floc-dropdown' => ['get'],
                ],
            ];
        return $behaviors;
    }
    public function actionGetMapPlatDropdown() {
		
		$data = [null => "Select..."];
        $data["161-30-5-C"] = "161-30-5-C";
        $data["141-31-3-C"] = "141-31-3-C";
        $data["171-40-1-B"] = "171-40-1-B";
        $data["130-15-5-F"] = "130-15-5-F";

        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }

    public function actionGetSurveyorDropdown() {
        $data["Doe, Jane (janedoe)"] = "Doe, Jane";
        $data["Doe, John (johndoe)"] = "Doe, John";
        $data["Milstone, Fred (fred3)"] = "Milstone, Fred";
        $data["Randalt, Bill (bill2)"] ="Randalt, Bill";
        $data["Smith, Bob (bob1)"] = "Smith, Bob";


        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }

    public function actionGetDivisionDropdown()
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownDispatchDivision::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownDispatchDivision::find()
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


    public function actionGetWorkCenterDropdown()
    {
        //TODO RBAC permission check
        try{
            //TODO check headers

            //stub data
            $dropdown = [null => "Select..."];
            $dropdown["Zoltun Kulle"] = "Zoltun Kulle";
            $dropdown["Cydaea"] = "Cydaea";
            $dropdown["Izual"] = "Izual";
            $dropdown["Urzael"] = "Urzael";

            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
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

    //required format for the dynaic dropdowns
    //['id'=>'<sub-cat_id_2>', 'name'=>'<sub-cat-name2>']
    public function actionGetWorkCenterDependentDropdown($division = null)
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownDispatchWorkCenter::setClient($headers['X-Client']);
			
            //todo permission check
			
			$data = WebManagementDropDownDispatchWorkCenter::find()
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

    /*
     * Belongs to LeakLogDetail
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

    /*
     * Belongs to LeakLogDetail
     */
    public function actionGetSurveyorDependentDropdown($division = null, $mapPlat = null, $date = null, $workCenter = null) {
		try{
		
			$data = [];

			$data["161-30-5-C"]["MapPlat"] = "161-30-5-C";
			$data["161-30-5-C"]["Division"] = "Diablo";
			$data["161-30-5-C"]["SurveyorDisplay"] = "Doe, John (johndoe)";
			$data["161-30-5-C"]["Surveyor"] = "johndoe";
			$data["161-30-5-C"]["Date"] = "05/10/2016";
			$data["161-30-5-C"]["WorkCenter"] = "Izual";

			$data["161-30-3-C"]["MapPlat"] = "161-30-3-C";
			$data["161-30-3-C"]["Division"] = "Diablo";
			$data["161-30-3-C"]["SurveyorDisplay"] = "Doe, Jane (janedoe)";
			$data["161-30-3-C"]["Surveyor"] = "janedoe";
			$data["161-30-3-C"]["Date"] = "05/11/2016";
			$data["161-30-3-C"]["WorkCenter"] = "Izual";

			$data["141-31-3-C"]["MapPlat"] = "141-31-3-C";
			$data["141-31-3-C"]["Division"] = "Azmodan";
			$data["141-31-3-C"]["SurveyorDisplay"] = "Smith, Bob (bob1)";
			$data["141-31-3-C"]["Surveyor"] = "bob1";
			$data["141-31-3-C"]["Date"] = "05/12/2016";
			$data["141-31-3-C"]["WorkCenter"] = "Cydaea";

			$data["120-31-6-F"]["MapPlat"] = "120-31-6-F";
			$data["120-31-6-F"]["Division"] = "Malthael";
			$data["120-31-6-F"]["SurveyorDisplay"] = "Randalt, Bill (bill2)";
			$data["120-31-6-F"]["Surveyor"] = "bill2";
			$data["120-31-6-F"]["Date"] = "05/13/2016";
			$data["120-31-6-F"]["WorkCenter"] = "Urzael";

			$data["110-11-3-A"]["MapPlat"] = "110-11-3-A";
			$data["110-11-3-A"]["Division"] = "Malthael";
			$data["110-11-3-A"]["SurveyorDisplay"] = "Milstone, Fred (fred3)";
			$data["110-11-3-A"]["Surveyor"] = "fred3";
			$data["110-11-3-A"]["Date"] = "05/14/2016";
			$data["110-11-3-A"]["WorkCenter"] = "Urzael";

			$filteredData = [];
			
			foreach($data as $datum) {
				if($division == null || $division == $datum["Division"]) {
					if($mapPlat == null || $mapPlat == $datum["MapPlat"]) {
						if($date == null || $date == $datum["Date"]) {
							if($workCenter == null || $workCenter == $datum["WorkCenter"])
							{
								$entry = [];
								$entry["id"] = $datum["Surveyor"];
								$entry["name"] = $datum["SurveyorDisplay"];
								$filteredData[] = $entry;
							}
						}
					}
				}
			}


			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $filteredData;
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
     */
    public function actionGetDateDependentDropdown($division = null, $surveyor = null, $mapPlat = null) {
		try{
		
			$data = [];

			$data["161-30-5-C"]["MapPlat"] = "161-30-5-C";
			$data["161-30-5-C"]["Division"] = "Diablo";
			$data["161-30-5-C"]["Surveyor"] = "johndoe";
			$data["161-30-5-C"]["Date"] = "05/10/2016";

			$data["161-30-3-C"]["MapPlat"] = "161-30-3-C";
			$data["161-30-3-C"]["Division"] = "Diablo";
			$data["161-30-3-C"]["Surveyor"] = "janedoe";
			$data["161-30-3-C"]["Date"] = "05/11/2016";

			$data["141-31-3-C"]["MapPlat"] = "141-31-3-C";
			$data["141-31-3-C"]["Division"] = "Azmodan";
			$data["141-31-3-C"]["Surveyor"] = "bob1";
			$data["141-31-3-C"]["Date"] = "05/12/2016";

			$data["120-31-6-F"]["MapPlat"] = "120-31-6-F";
			$data["120-31-6-F"]["Division"] = "Malthael";
			$data["120-31-6-F"]["Surveyor"] = "bill2";
			$data["120-31-6-F"]["Date"] = "05/13/2016";

			$data["110-11-3-A"]["MapPlat"] = "110-11-3-A";
			$data["110-11-3-A"]["Division"] = "Malthael";
			$data["110-11-3-A"]["Surveyor"] = "fred3";
			$data["110-11-3-A"]["Date"] = "05/14/2016";

			$filteredData = [];
			foreach($data as $datum) {
				if($division == null || $division == $datum["Division"]) {
					if($surveyor == null || $surveyor == $datum["Surveyor"]) {
						if($mapPlat == null || $mapPlat == $datum["MapPlat"]) {
							$entry = [];
							$entry["id"] = $datum["Date"];
							$entry["name"] = $datum["Date"];
							$filteredData[] = $entry;
						}
					}
				}
			}


			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $filteredData;
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

    public function actionGetSurveyTypeDropdown($division, $workCenter) {
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatchSurveyType::setClient($headers['X-Client']);
			
			//todo permission check
			
			$data = WebManagementDropDownDispatchSurveyType::find()
				->select('SurveyType')
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
                ->all();
            $namePairs = [];
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

	//dispatch
    public function actionGetComplianceMonthDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatchComplianceDate::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownDispatchComplianceDate::find()
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
	
	//user?
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
	
	//user?
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
	
	//user
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
	
	//user? dispatch? assigned?
	public function actionGetUserWorkCenterDropdown() {
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
	
	//user?
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
	
	//dispatch
	public function actionGetFlocDropdown($division, $workCenter, $surveyType) {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownDispatchFLOC::setClient($headers['X-Client']);
			
			//todo permission check
			$data = WebManagementDropDownDispatchFLOC::find()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
				->andWhere(['SurveyType'=>$surveyType])
                ->all();

            $namePairs = [];
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
	
	public function actionGetAssignedWorkCenterDropdown($division = null)
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
                ->all();
            $namePairs = [];
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
			$data = WebManagementDropDownAssignedFLOC::find()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
				->andWhere(['SurveyType'=>$surveyType])
                ->all();

            $namePairs = [];
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
}