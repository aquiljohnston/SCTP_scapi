<?php

namespace app\modules\v1\controllers;

use app\modules\v1\modules\pge\models\WebManagementLeakLogDropDown;
use Yii;
use app\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\modules\v1\models\EmployeeType;
use app\modules\v1\Controllers\BaseActiveController;
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
                    'get-week-dropdown' => ['get'],
                    'get-work-center-dropdown' => ['get'],
                    'get-work-center-dependent-dropdown' => ['get'],
                    'get-employee-type-dropdown' => ['get'],
                    'get-pge-employee-type-dropdown' => ['get'],
                    'get-map-plat-dropdown' => ['get'],
                    'get-surveyor-dropdown' => ['get'],
                    'get-division-dropdown' => ['get'],
                    'get-device-id-dropdown' => ['get']
                ],
            ];
        return $behaviors;
    }
    public function actionGetMapPlatDropdown($division, $workcenter, $surveyor, $date) {

        if($division != null && $workcenter != null)
        {
            // by division and workcenter
            $values = WebManagementLeakLogDropDown::find()
                    ->select(['Map/Plat'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workcenter])
                    ->distinct()
                    ->all();
        }
        else if($division != null && $workcenter != null && $surveyor != null)
        {
            // by division and workcenter and surveyor
            $values = WebManagementLeakLogDropDown::find()
                    ->select(['Map/Plat'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workcenter])
                    ->andWhere(['Surveyor' => $surveyor])
                    ->distinct()
                    ->all();
        }
        else if($division != null && $workcenter != null && $date != null)
        {
            // by division and workcenter and date
            $values = WebManagementLeakLogDropDown::find()
                    ->select(['Map/Plat'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workcenter])
                    ->andWhere(['Date' => $date])
                    ->distinct()
                    ->all();
        }

        $results = [];
        foreach ($values as $value) {
            $results[] = [
                "id" => $value["Surveyor"],
                "name" => $value["Surveyor"]
            ];
        }

        $response = Yii::$app ->response;
        $response -> format = Response::FORMAT_JSON;
        $response -> data = $results;

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
        //TODO RBAC permission check
        try{
            //TODO check headers

            //stub data
            $dropdown = [null => "Select..."];
            $dropdown["Belial"] = "Belial";
            $dropdown["Azmodan"] = "Azmodan";
            $dropdown["Diablo"] = "Diablo";
            $dropdown["Malthael"] = "Malthael";

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


    public function actionGetLeakLogDivisionDropdown()
    {
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementLeakLogDropDown::setClient($headers['X-Client']);

            $values = WebManagementLeakLogDropDown::find()
                ->select(['Division'])
                ->where(['not', ['Division' => null]])
                ->distinct()
                ->all();

            $namePairs = [
                null => "Select...",
            ];
            foreach ($values as $value) {
                $namePairs[$value["Division"]] = $value["Division"];
            }

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $namePairs;

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

    public function actionGetLeakLogWorkCenterDropdown($division)
    {
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementLeakLogDropDown::setClient($headers['X-Client']);

            $values = WebManagementLeakLogDropDown::find()
                ->select(['WorkCenter'])
                ->where(['Division' => $division])
                ->andWhere(['not' ,['Surveyor' => null]])
                ->andWhere(['not' ,['Date' => null]])
                ->andWhere(['not' ,['Map/Plat' => null]])
                ->distinct()
                ->all();

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    "id" => $value["WorkCenter"],
                    "name" => $value["WorkCenter"]
                ];
            }

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $results;

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

    public function actionGetLeakLogSurveyorDropdown($workCenter)
    {
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementLeakLogDropDown::setClient($headers['X-Client']);

            $values = WebManagementLeakLogDropDown::find()
                ->select(['Surveyor'])
                ->where(['WorkCenter' => $workCenter])
                ->distinct()
                ->all();

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    "id" => $value["Surveyor"],
                    "name" => $value["Surveyor"]
                ];
            }

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $results;

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

    public function actionGetPgeEmployeeTypeDropdown() {

        // TODO: Permissions check
        try {
            //TODO: headers and X-Client

            //TODO: Find EmployeeTypes
            $data = [
                null => "Select...",
                "Employee" => "Employee",
                "Contractor" => "Contractor",
                "Intern" => "Intern"
            ];

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        }
        catch (\Exception $e) {
            throw new BadRequestHttpException;
        }
    }



    /*
     * Belongs to LeakLogDetail
     */
    public function actionGetMapPlatDependentDropdown($division = null, $workCenter = null, $surveyor = null, $date = null) {

		try{
            $headers = getallheaders();
            WebManagementLeakLogDropDown::setClient($headers['X-Client']);


            if($division != null && $workCenter != null && $date != null)
            {
                // by division and workcenter and date
                $values = WebManagementLeakLogDropDown::find()
                        ->select(['Map/Plat'])
                        ->where(['Division' => $division])
                        ->andWhere(['WorkCenter' => $workCenter])
                        ->andWhere(['Date' => $date])
                        ->andWhere(['not' ,['Surveyor' => null]])
                        ->distinct()
                        ->all();
            }
            else if($division != null && $workCenter != null && $surveyor != null)
            {
                // by division and workcenter and surveyor
                $values = WebManagementLeakLogDropDown::find()
                        ->select(['Map/Plat'])
                        ->where(['Division' => $division])
                        ->andWhere(['WorkCenter' => $workCenter])
                        ->andWhere(['Surveyor' => $surveyor])
                        ->andWhere(['not' ,['Date' => null]])
                        ->distinct()
                        ->all();
            }
            else if($division != null && $workCenter != null)
            {
                // by division and workcenter
                $values = WebManagementLeakLogDropDown::find()
                        ->select(['Map/Plat'])
                        ->where(['Division' => $division])
                        ->andWhere(['WorkCenter' => $workCenter])
                        ->andWhere(['not' ,['Date' => null]])
                        ->andWhere(['not' ,['Map/Plat' => null]])
                        ->andWhere(['not' ,['Surveyor' => null]])
                        ->distinct()
                        ->all();
            }

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    "id" => $value["Map/Plat"],
                    "name" => $value["Map/Plat"]
                ];
            }

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $results;

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
    public function actionGetSurveyorDependentDropdown($division = null, $workCenter = null, $mapPlat = null, $date = null) {
		//TODO RBAC permission check
        try{

            $headers = getallheaders();
            WebManagementLeakLogDropDown::setClient($headers['X-Client']);

            if($workCenter == null)
            {
                // just by division
                $values = WebManagementLeakLogDropDown::find()
                    ->select(['Surveyor'])
                    ->where(['Division' => $division])
                    ->andWhere(['not' ,['Date' => null]])
                    ->andWhere(['not' ,['Surveyor' => null]])
                    ->andWhere(['not' ,['Map/Plat' => null]])
                    ->distinct()
                    ->all();
            }
            else if ($mapPlat == null)
            {
                // by division and workcenter
                $values = WebManagementLeakLogDropDown::find()
                    ->select(['Surveyor'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
                    ->andWhere(['not' ,['Date' => null]])
                    ->andWhere(['not' ,['Map/Plat' => null]])
                    ->distinct()
                    ->all();
            }
            else if($date == null)
            {
                /// by division and workcenter and mapplat
                $values = WebManagementLeakLogDropDown::find()
                    ->select(['Surveyor'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
                    ->andWhere(['Map/Plat' => $mapPlat])
                    ->andWhere(['not' ,['Date' => null]])
                    ->distinct()
                    ->all();
            }
            else
            {
                /// by division and workcenter and mapplat and date
                $values = WebManagementLeakLogDropDown::find()
                    ->select(['Surveyor'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
                    ->andWhere(['Map/Plat' => $mapPlat])
                    ->andWhere(['Date' => $date])
                    ->distinct()
                    ->all();
            }

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    "id" => $value["Surveyor"],
                    "name" => $value["Surveyor"]
                ];
            }

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $results;

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
    public function actionGetDateDependentDropdown($division = null, $workCenter = null, $surveyor = null, $mapPlat = null) {
		try{

            $headers = getallheaders();
            WebManagementLeakLogDropDown::setClient($headers['X-Client']);

            if($division != null && $workCenter != null && ($surveyor == null || $mapPlat == null))
            {
                // just by division and workCenter
                $values = WebManagementLeakLogDropDown::find()
                    ->select(['Date'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
					->andWhere(['not' ,['Date' => null]])
                    ->andWhere(['not' ,['Surveyor' => null]])
                    ->andWhere(['not' ,['Map/Plat' => null]])
                    ->distinct()
                    ->all();
            }
            else if ($division != null && $workCenter != null && $surveyor != null && $mapPlat != null)
            {
                // by division and workcenter and surveyor and mapplat
                $values = WebManagementLeakLogDropDown::find()
                     ->select(['Date'])
                     ->where(['Division' => $division])
                     ->andWhere(['WorkCenter' => $workCenter])
                     ->andWhere(['Surveyor' => $surveyor])
                     ->andWhere(['Map/Plat' => $mapPlat])
					 ->andWhere(['not' ,['Date' => null]])
                     ->distinct()
                     ->all();
            }


            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    "id" => $value["Date"],
                    "name" => $value["Date"]
                ];
            }

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $results;

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

    public function actionGetSurveyTypeDropdown() {
        try{
			$data = [];

			$data = [null => "Select..."];
			$data["1 YR"] = "1 YR";
			$data["3 YR"] = "3 YR";
			$data["5 YR"] = "5 YR";

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

    public function actionGetComplianceMonthDropdown() {
		try{
			$data = [];

			$data = [null => "Select..."];
			$data["01"] = "January";
			$data["02"] = "February";
			$data["03"] = "March";
			$data["04"] = "April";
			$data["05"] = "May";
			$data["06"] = "June";
			$data["07"] = "July";
			$data["08"] = "August";
			$data["09"] = "September";
			$data["10"] = "October";
			$data["11"] = "November";
			$data["12"] = "December";

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

    public function actionGetStatusDropdown() {
        try{
			$data = [];

			$data = [null => "Select..."];
			$data["Accepted"] = "Accepted";
			$data["Dispatched"] = "Dispatched";
			$data["In Progress"] = "In Progress";

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

    public function actionGetDispatchMethodDropdown() {
        try{
			$data = [];

			$data = [null => "Select..."];
			$data["Dispatched"] = "Dispatched";
			$data["Self Dispatched"] = "Self Dispatched";
			$data["Ad Hoc"] = "Ad Hoc";

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
}