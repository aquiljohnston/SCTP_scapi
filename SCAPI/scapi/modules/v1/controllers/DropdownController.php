<?php

namespace app\modules\v1\controllers;

use app\modules\v1\modules\pge\models\WebManagementLeakLogDropDown;
use app\modules\v1\modules\pge\models\WebManagementFlocsWithIRDropDown;
use app\modules\v1\modules\pge\models\WebManagementDivisionWorkCenterFLOCWithIR;
use app\modules\v1\modules\pge\models\WebManagementDivisionWorkCenterFLOC;
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
                    'get-floc-dropdown' => ['get'],
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
                'id' => $value['Surveyor'],
                'name' => $value['Surveyor']
            ];
        }

        $response = Yii::$app ->response;
        $response -> format = Response::FORMAT_JSON;
        $response -> data = $results;

        return $response;
    }
	
	//deprecated, moved to pge module
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
                null => 'Select...',
            ];
            foreach ($values as $value) {
                $namePairs[$value['Division']] = $value['Division'];
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


    public function actionGetLeakLogFlocDropdown($workcenter, $isAdhoc)
    {
        //TODO RBAC permission check
        try{

            $headers = getallheaders();

            if($isAdhoc == 0)
            {
                WebManagementDivisionWorkCenterFLOCWithIR::setClient($headers['X-Client']);
                $values = WebManagementDivisionWorkCenterFLOCWithIR::find()
                    ->select(['FLOC', 'SurveyFreq'])
                    ->where(['WorkCenter' => $workcenter])
                    ->andWhere(['not' ,['SurveyFreq' => '']])
                    ->distinct()
                    ->all();
            }
            else
            {
                WebManagementDivisionWorkCenterFLOC::setClient($headers['X-Client']);
                $values = WebManagementDivisionWorkCenterFLOC::find()
                   ->select(['FLOC'])
                   ->where(['WorkCenter' => $workcenter])
                   ->distinct()
                   ->all();
            }
            $results = [];
            foreach ($values as $value) {
                $surveyType = 'Unknown';
                if($isAdhoc == 0 && $value['SurveyFreq'] != '')
                {
                    $surveyType = $value['SurveyFreq'];
                }
                $results[] = [
                    'id' => $surveyType,
                    'name' => $value['FLOC']
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
	
	//deprecated, moved to pge module
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
                    'id' => $value['WorkCenter'],
                    'name' => $value['WorkCenter']
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
	
	//deprecated, moved to pge module
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
				->orderBy('Surveyor ASC')
                ->all();

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    'id' => $value['Surveyor'],
                    'name' => $value['Surveyor']
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
						->orderBy(['Map/Plat' => SORT_ASC])
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
						->orderBy(['Map/Plat' => SORT_ASC])
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
						->orderBy(['Map/Plat' => SORT_ASC])
                        ->all();
            }

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    'id' => $value['Map/Plat'],
                    'name' => $value['Map/Plat']
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
					->orderBy(['Surveyor' => SORT_ASC])
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
					->orderBy(['Surveyor' => SORT_ASC])
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
					->orderBy(['Surveyor' => SORT_ASC])
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
					->orderBy(['Surveyor' => SORT_ASC])
                    ->all();
            }

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    'id' => $value['Surveyor'],
                    'name' => $value['Surveyor']
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

			//$dateCastingMagic = new \yii\db\Expression('(created_at::text)');
			
            if($division != null && $workCenter != null && ($surveyor == null || $mapPlat == null))
            {
                // just by division and workCenter
                $values = WebManagementLeakLogDropDown::find()
                    ->select(['Date', 'OrderByDate'])
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
					->andWhere(['not' ,['Date' => null]])
                    ->andWhere(['not' ,['Surveyor' => null]])
                    ->andWhere(['not' ,['Map/Plat' => null]])
                    ->distinct()
					->orderBy(['OrderByDate' => SORT_DESC])
                    ->all();
            }
            else if ($division != null && $workCenter != null && $surveyor != null && $mapPlat != null)
            {
                // by division and workcenter and surveyor and mapplat
                $values = WebManagementLeakLogDropDown::find()
                     ->select(['Date', 'OrderByDate'])
                     ->where(['Division' => $division])
                     ->andWhere(['WorkCenter' => $workCenter])
                     ->andWhere(['Surveyor' => $surveyor])
                     ->andWhere(['Map/Plat' => $mapPlat])
					 ->andWhere(['not' ,['Date' => null]])
                     ->distinct()
					 ->orderBy(['OrderByDate' => SORT_DESC])
                     ->all();
            }


            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    'id' => $value['Date'],
                    'name' => $value['Date']
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
}