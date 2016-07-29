<?php

namespace app\modules\v1\controllers;

use Yii;
use app\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\modules\v1\models\EmployeeType;
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
                    'get-division-dropdown' => ['get']
                ],
            ];
        return $behaviors;
    }
    public function actionMapPlatDropdown() {
        $data["0000/Z00/Special/GD"] = "0000/Z00/Special/GD";
        $data["0001/A01/1 Year/GD (22)"] = "0001/A01/1 Year/GD (22)";
        $data["0001/A01/3 Year/GD (1)"] = "0001/A01/3 Year/GD (1)";
        $data["0001/A01/5 Year/GD (2)"] = "0001/A01/5 Year/GD (2)";

        //send response
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }

    public function actionSurveyorDropdown() {
        $data["Demo, Demo"] = "Demo, Demo";
        $data["Zhang, Tao"] = "Zhang, Tao";
        $data["Vicente, Andre"] = "Vicente, Andre";
        $data["Vicente, Bob"] ="Vicente, Bob";
        $data["Zhang, Rufus"] = "Zhang, Rufus";
        $data["Doe, John"] = "Doe, John";


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

        $dropdown = [];
        $w1sun = new DateTime("07/24/16");
        $w1sun = $w1sun->format('m/d/Y');
        $w1sat = new DateTime("07/30/16");
        $w1sat = $w1sat->format('m/d/Y');
        $w2sun = new DateTime("07/17/16");
        $w2sun = $w2sun->format('m/d/Y');
        $w2sat = new DateTime("07/23/16");
        $w2sat = $w2sat->format('m/d/Y');
        $w3sun = new DateTime("07/10/16");
        $w3sun = $w3sun->format('m/d/Y');
        $w3sat = new DateTime("07/16/16");
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
        //TODO RBAC permission check
        try{
            //TODO check headers

            //stub data
            $dropdown = [];
            $data = [];
            if($division == null)
            {
                $data["id"] = "Zoltun Kulle";
                $data["name"] = "Zoltun Kulle";
                $dropdown[] = $data;
                $data = [];
                $data["id"] = "Cydaea";
                $data["name"] = "Cydaea";
                $dropdown[] = $data;
                $data = [];
                $data["id"] = "Izual";
                $data["name"] = "Izual";
                $dropdown[] = $data;
                $data = [];
                $data["id"] = "Urzael";
                $data["name"] = "Urzael";
                $dropdown[] = $data;
                $data = [];
            }
            elseif ($division == "Belial")
            {
                $data["id"] = "Zoltun Kulle";
                $data["name"] = "Zoltun Kulle";
                $dropdown[] = $data;
                $data = [];
            }
            elseif ($division == "Azmodan")
            {
                $data["id"] = "Cydaea";
                $data["name"] = "Cydaea";
                $dropdown[] = $data;
                $data = [];
            }
            elseif ($division == "Diablo")
            {
                $data["id"] = "Izual";
                $data["name"] = "Izual";
                $dropdown[] = $data;
                $data = [];
            }
            elseif ($division == "Malthael")
            {
                $data["id"] = "Urzael";
                $data["name"] = "Urzael";
                $dropdown[] = $data;
                $data = [];
            }

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

    //return a json containing pairs of EmployeeTypes
    public function actionGetEmployeeTypeDropdown()
    {
        // RBAC permission check
        PermissionsController::requirePermission('employeeTypeGetDropdown');

        try
        {
            //set db target
            $headers = getallheaders();
            EmployeeType::setClient($headers['X-Client']);

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
        } catch (\Exception $e) {
            throw new BadRequestHttpException;
        }
    }
}