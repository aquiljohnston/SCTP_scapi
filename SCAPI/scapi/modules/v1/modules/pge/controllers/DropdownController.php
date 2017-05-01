<?php

namespace app\modules\v1\modules\pge\controllers;

use app\modules\v1\modules\pge\models\CityCounty;
use Yii;
use app\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\modules\v1\models\EmployeeType;
use yii\web\Response;
use \DateTime;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v1\controllers\PermissionsController;
use app\modules\v1\models\BaseActiveRecord;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\modules\pge\models\WebManagementDropDownReportingGroups;
use app\modules\v1\modules\pge\models\WebManagementUserWorkCenter;
use app\modules\v1\modules\pge\models\WebManagementDropDownEmployeeType;
use app\modules\v1\modules\pge\models\WebManagementDropDownRoles;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchMapPlat;
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatchAssignedDispatchMethod;
use app\modules\v1\modules\pge\models\WebManagementDropDownUserWorkCenter;
use app\modules\v1\modules\pge\models\WebManagementUsers;
use app\modules\v1\modules\pge\models\WebManagementDivisionWorkCenterFLOCWithIR;
use app\modules\v1\modules\pge\models\WebManagementDivisionWorkCenterFLOC;
use app\modules\v1\modules\pge\models\WebManagementMapStampDropDown;
use app\modules\v1\modules\pge\models\WebManagementDropDownAssociatePlanIR;
use app\modules\v1\modules\pge\models\WebManagementTrackerHistoryDropDown;
//assigned
use app\modules\v1\modules\pge\models\WebManagementDropDownAssigned;
//AOC todo combine views
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCDivision;
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCSurveyor;
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCType;
use app\modules\v1\modules\pge\models\WebManagementDropDownAOCWorkCenter;
//dispatch
use app\modules\v1\modules\pge\models\WebManagementDropDownDispatch;
//tablet
//survey
use app\modules\v1\modules\pge\models\DropDowns;
use app\modules\v1\modules\pge\models\TabletMeter;
use app\modules\v1\modules\pge\models\TabletFilter;
use app\modules\v1\modules\pge\models\TabletRegulator;
use app\modules\v1\modules\pge\models\TabletRouteName;

//moved from v1 dropdown controller
use app\modules\v1\modules\pge\models\WebManagementLeakLogDropDown;
use app\modules\v1\modules\pge\models\WebManagementFlocsWithIRDropDown;


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
					'get-default-filter' => ['get'],
                    'get-employee-type-dropdown' => ['get'],
                    'get-reporting-group-dropdown' => ['get'],
                    'get-reporting-group-u-i-d-dropdown' => ['get'],
                    'get-role-dropdown' => ['get'],
                    'get-floc-work-center-dropdown' => ['get'],
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
					'survey-route-name-dropdown' => ['post'],
                    'get-web-mgmt-leak-log-form-dropdowns' =>['get'],
                    'get-adhoc-frequency-dropdown' => ['get'],
                    'get-map-stamp-division-dropdown' => ['get'],
                    'get-map-stamp-work-center-dropdown' => ['get'],
                    'get-map-stamp-equipment-services-pic-dropdowns' => ['get'],
                    'get-tracker-h-division-dropdown' => ['get'],
                    'get-tracker-h-work-center-dropdown' => ['get'],
                    'get-tracker-h-surveyor-dropdown' => ['get'],
					'get-leak-log-floc-dropdown' => ['get'],
					'get-date-dependent-dropdown' => ['get'],
					'get-map-plat-dependent-dropdown' => ['get'],
					'get-leak-log-division-dropdown' => ['get'],
					'get-leak-log-work-center-dropdown' => ['get'],
					'get-leak-log-surveyor-dropdown' => ['get'],
					'get-surveyor-dependent-dropdown' => ['get'],
                ],
            ];
        return $behaviors;
    }
	
	//////////////////////////////Dropdown Defaults Begin/////////////////////////////
	//helper methods//
	//gets a users home workCenter based on uid
	private static function getHomeWorkCenter($userUID)
	{
		$workCenter = WebManagementUsers::find()
			->select('WorkCenter')
			->where(['UserUID' => $userUID])
			->one();
			
		return $workCenter;
	}
	
	//get default for dispatch screen based on work center
	private static function getDefaultDispatch($workCenter)
	{
		$filters = WebManagementDropDownDispatch::find()
			->select('WorkCenter, Division')
			->where(['WorkCenter' => $workCenter])
			->asArray()
			->one();
			
		return $filters;
	}
	
	//get default for assigned screen based on work center
	private static function getDefaultAssigned($workCenter)
	{
		$filters = WebManagementDropDownAssigned::find()
			->select('WorkCenter, Division')
			->where(['WorkCenter' => $workCenter])
			->asArray()
			->one();
			
		return $filters;
	}
	
	//get default filters based on screen param
	public function actionGetDefaultFilter($screen)
	{
		try
		{
			//get UID of user making request
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			$userUID = BaseActiveController::getUserFromToken()->UserUID;
			
			//db target
			$headers = getallheaders();
			WebManagementUsers::setClient($headers['X-Client']);
			
			//create response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//created filter array for response
			$filterResponse = [];
			
			//call hleper method to get home work center
			$homeWorkCenter = DropdownController::getHomeWorkCenter($userUID);
			
			//check if user has a home work center
			if($homeWorkCenter != null)
			{
				$filters = '';
				
				//call helper method to get filters based on screen
				if($screen == 'dispatch')
				{
					$filters = DropdownController::getDefaultDispatch($homeWorkCenter->WorkCenter);
				}
				if($screen == 'assigned')
				{
					$filters = DropdownController::getDefaultAssigned($homeWorkCenter->WorkCenter);
				}
				
				if($filters != null)
				{
					$filterResponse[] = $filters;
				}
				else
				{
					$filterResponse['Error'] = 'Cannont Set Default Filter: Default Filter Not Currently Avaliable.';
				}
			}
			else
			{
				$filterResponse['Error'] = 'Cannont Set Default Filter: User has no assigned Home Work Center.';
			}
			//pass data to response and send it
			$response->data = $filterResponse;
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
	//////////////////////////////Dropdown Defaults End/////////////////////////////
	
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

    public function actionGetAdhocFrequencyDropdown() {
		try{
			//db target
			$headers = getallheaders();

			//todo permission check
            DropDowns::setClient($headers['X-Client']);

            $data =  DropdownController::webDropdownQuery('ddSurveyFrequencyTR');

			$namePairs = [];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                if($data[$i]->SortSeq != 0)
                {
                    $namePairs[$data[$i]->FieldDisplay]= $data[$i]->FieldDisplay;
                }
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

    public function actionGetFlocWorkCenterDropdown($adHoc) {
		try{
			//db target
			$headers = getallheaders();

            if($adHoc == 1)
            {
                WebManagementDivisionWorkCenterFLOC::setClient($headers['X-Client']);
                $data = WebManagementDivisionWorkCenterFLOC::find()
                    ->select('WorkCenter')
                    ->distinct()
                    ->all();
            }
            else
            {
                WebManagementDivisionWorkCenterFLOCWithIR::setClient($headers['X-Client']);
                $data = WebManagementDivisionWorkCenterFLOCWithIR::find()
                    ->select('WorkCenter')
                    ->distinct()
                    ->all();
            }
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

	//TODO: Remove
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
	
	public function actionGetWorkCenterFilterDropdown() {
		try{
			//db target
			$headers = getallheaders();
			WebManagementUserWorkCenter::setClient($headers['X-Client']);

			//todo permission check
			$data = WebManagementUserWorkCenter::find()
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

	public function actionGetRoleDropdown() {
		try{
			//db target
			$headers = getallheaders();

			if(PermissionsController::can('userCreateAdmin'))
			{
				WebManagementDropDownRoles::setClient($headers['X-Client']);
				$data = WebManagementDropDownRoles::find()
					->all();
			}
			else
			{
				WebManagementDropDownRoles::setClient($headers['X-Client']);
				$data = WebManagementDropDownRoles::find()
					->where(['not', ['RoleName' => 'Administrator']])
					->all();
			}

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

			$namePairs = ['All' => 'All'];
			$dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->SurveyType]= $data[$i]->SurveyType;
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

			$namePairs = ['All' => 'All'];
			$dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->FLOC]= $data[$i]->FLOC;
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

			$namePairs = ['All' => 'All'];
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

	//////////////////////DISPATCH DROPDOWNS END/////////////////////

	//////////////////////ASSIGNED DROPDOWNS BEGIN/////////////////////

	public function actionGetAssignedDivisionDropdown()
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAssigned::setClient($headers['X-Client']);

            //todo permission check

			$data = WebManagementDropDownAssigned::find()
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

	public function actionGetAssignedWorkCenterDropdown($division)
    {
        try{
			//set db target
			$headers = getallheaders();
			WebManagementDropDownAssigned::setClient($headers['X-Client']);

            //todo permission check

			$data = WebManagementDropDownAssigned::find()
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

    public function actionGetAssignedSurveyFreqDropdown($division, $workCenter) {
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownAssigned::setClient($headers['X-Client']);

			//todo permission check

			$data = WebManagementDropDownAssigned::find()
				->select('SurveyFreq')
				->distinct()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
				->andWhere(['not', ['SurveyFreq' => null]])
                ->all();
            $namePairs = ['All' => 'All'];
            $dataSize = count($data);

			for($i=0; $i < $dataSize; $i++)
            {
				$namePairs[$data[$i]->SurveyFreq]= $data[$i]->SurveyFreq;
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
			WebManagementDropDownAssigned::setClient($headers['X-Client']);

			//todo permission check
			$dataQuery = WebManagementDropDownAssigned::find()
				->select('FLOC')
				->distinct()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter]);
			if($surveyType != 'All')
			{
				$dataQuery->andWhere(['SurveyFreq'=>$surveyType]);
			}
            $data = $dataQuery->all();
            $namePairs = ['All' => 'All'];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
				$namePairs[$data[$i]->FLOC]= $data[$i]->FLOC;
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

	public function actionGetAssignedComplianceMonthDropdown($division = null, $workCenter = null, $surveyFreq = null, $floc = null) {
		try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownAssigned::setClient($headers['X-Client']);

			//todo permission check
			$dataQuery = WebManagementDropDownAssigned::find()
				->select('ComplianceYearMonth, ComplianceSort')
				->distinct();
			if($division != null)
			{
				$dataQuery->andWhere(['Division'=>$division]);
			}
			if($workCenter != null)
			{
				$dataQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			if(!($surveyFreq == null || $surveyFreq == 'All'))
			{
				$dataQuery->andWhere(['SurveyFreq'=>$surveyFreq]);
			}
			if(!($floc == null || $floc == 'All'))
			{
				$dataQuery->andWhere(['FLOC'=>$floc]);
			}
			$data = $dataQuery->orderBy('ComplianceSort')
                ->all();
            $namePairs = [null => 'All'];
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

	public function actionGetAssignedStatusDropdown($division = null, $workCenter = null, $surveyFreq = null, $floc = null, $complianceYearMonth = null)
	{
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownAssigned::setClient($headers['X-Client']);

			//todo permission check
			$dataQuery = WebManagementDropDownAssigned::find()
				->select('StatusType')
				->distinct();
			if($division != null)
			{
				$dataQuery->andWhere(['Division'=>$division]);
			}
			if($workCenter != null)
			{
				$dataQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			if(!($surveyFreq == null || $surveyFreq == 'All'))
			{
				$dataQuery->andWhere(['SurveyFreq'=>$surveyFreq]);
			}
			if(!($floc == null || $floc == 'All'))
			{
				$dataQuery->andWhere(['FLOC'=>$floc]);
			}
			if($complianceYearMonth != null)
			{
				$dataQuery->andWhere(['ComplianceYearMonth'=>$complianceYearMonth]);
			}
            $data = $dataQuery->all();
            $namePairs = [null => 'All'];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$data[$i]->StatusType]= $data[$i]->StatusType;
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

	public function actionGetAssignedDispatchMethodDropdown($division = null, $workCenter = null, $surveyFreq = null, $floc = null, $complianceYearMonth = null, $surveyStatus = null)
	{
        try{
			//db target
			$headers = getallheaders();
			WebManagementDropDownAssigned::setClient($headers['X-Client']);

			//todo permission check
			$dataQuery = WebManagementDropDownAssigned::find()
                ->select('DispatchMethod')
				->distinct();
			if($division != null)
			{
				$dataQuery->andWhere(['Division'=>$division]);
			}
			if($workCenter != null)
			{
				$dataQuery->andWhere(['WorkCenter'=>$workCenter]);
			}
			if(!($surveyFreq == null || $surveyFreq == 'All'))
			{
				$dataQuery->andWhere(['SurveyFreq'=>$surveyFreq]);
			}
			if(!($floc == null || $floc == 'All'))
			{
				$dataQuery->andWhere(['FLOC'=>$floc]);
			}
			if($complianceYearMonth != null)
			{
				$dataQuery->andWhere(['ComplianceYearMonth'=>$complianceYearMonth]);
			}
			if($surveyStatus != null)
			{
				$dataQuery->andWhere(['statustype'=>$surveyStatus]);
			}
            $data = $dataQuery->all();
            $namePairs = [null => 'All'];
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
	// not in use
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

    // not in use
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
				->orderBy('Surveyor ASC')
                ->all();
            $namePairs = [null => 'All'];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
				$namePairs[$data[$i]->Surveyor]= $data[$i]->Surveyor;
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

			$dataQuery = WebManagementDropDownAOCType::find()
				->where(['Division'=>$division])
				->andWhere(['WorkCenter'=>$workCenter])
				->andWhere(['not', ['AOCType'=> null]]);
			if($surveyor != null)
			{
				$dataQuery->andWhere(['Surveyor'=>$surveyor]);
			}
            $data = $dataQuery->all();
            $namePairs = [null => 'All'];
            $dataSize = count($data);

            for($i=0; $i < $dataSize; $i++)
            {
				$namePairs[$data[$i]->AOCType]= $data[$i]->AOCType;
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

    /**
     * return data for both division and work center dropdown
     * @return mixed
     */
    public function actionGetAocDivisionWorkCenterDropdown()
    {
        try{
            //set db target
            $headers = getallheaders();
            WebManagementDropDownAOCDivision::setClient($headers['X-Client']);

            //todo permission check

            $divisionData = WebManagementDropDownAOCDivision::find()
                ->all();
            $divisionNamePairs = [null => "Select..."];
            $divisionDataSize = count($divisionData);

            foreach ($divisionData as $value) {
                $divisionNamePairs[$value['Division']] = $value['Division'];
            }

            $combinedArray['Division'] = $divisionNamePairs;
            $combinedArray['DivisionWorkCenter'] = array();
            $combinedArray['DivisionWorkCenter'] = [
                null => ['Select...'],
            ];

            for($i=0; $i < $divisionDataSize; $i++)
            {
                $namePairs[$divisionData[$i]->Division]= $divisionData[$i]->Division;
                $workCenterData = WebManagementDropDownAOCWorkCenter::find()
                    ->where(['Division'=>$divisionData[$i]->Division])
                    ->all();
                for ($j = 0; $j < count($workCenterData); $j++){
                    $workCenterNamePairs[] = $workCenterData[$j]->WorkCenter;
                }
                $combinedArray['DivisionWorkCenter'][$divisionData[$i]['Division']] = $workCenterNamePairs;

                // reset temp array
                $workCenterNamePairs = [];
            }

            //send response
            $response = Yii::$app->response;
            $response ->format = Response::FORMAT_JSON;
            $response->data = $combinedArray;
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
		try
		{
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
			$responseData['SurveyDropdowns']['DimpRiserTypes']= DropdownController::tabletSurveyQuery('ddVoyDIMPRiserType');

			//Service Head Adapter Types
			$responseData['SurveyDropdowns']['ServiceHeadAdapterTypes']= DropdownController::tabletSurveyQuery('ddVoyDIMPServiceHeadAdapterType');

			//Facility Type GD Types
			$responseData['SurveyDropdowns']['FacilityTypes']= DropdownController::tabletSurveyQuery('ddFacilityType');

			// Above or Below Types
			$responseData['SurveyDropdowns']['AboveOrBelow']= DropdownController::tabletSurveyQuery('ddAboveBelowType');

			//Initial Leak Source Types
			$responseData['SurveyDropdowns']['InitialLeakSourceTypes']= DropdownController::tabletSurveyQuery('ddInitialLeakSourceType');

			//Reported By Types
			$responseData['SurveyDropdowns']['ReportedBy']= DropdownController::tabletSurveyQuery('ddReportedByType');

			//Surface Over Reading Locations Types
			$responseData['SurveyDropdowns']['SurfaceOverReadingLocation']= DropdownController::tabletSurveyQuery('ddSORLType');

			//Grade By Instrument Types
			$responseData['SurveyDropdowns']['GradeByInstTypes']= DropdownController::tabletSurveyQuery('ddGradeByInstType');

			//Grade types
			$responseData['SurveyDropdowns']['Grade']= DropdownController::tabletSurveyQuery('ddGradeType');

			//Info Code Types
			$responseData['SurveyDropdowns']['InfoCodes']= DropdownController::tabletSurveyQuery('ddInfoCodeType');

			//yes no
			$responseData['SurveyDropdowns']['YesNo']= DropdownController::tabletSurveyQuery('ddYesNo');

			//meter
			$responseData['SurveyDropdowns']['Meter'] = TabletMeter::find()->all();

			//filter
			$responseData['SurveyDropdowns']['Filter'] = TabletFilter::find()->all();

			//regulator
			$responseData['SurveyDropdowns']['Regulator'] = TabletRegulator::find()->all();

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
		return DropDowns::find()
				->select(['FilterName', 'SortSeq', 'FieldDisplay'])
				->where(['FilterName'=>$filter])
				->andWhere(['ActiveFlag'=>1])
				->orderBy('SortSeq')
				->all();
	}

	//get all pipeline route names based on a map grid uid
	public function actionSurveyRouteNameDropdown()
	{
		try
		{
			$post = file_get_contents("php://input");
			$mapGrids = json_decode($post, true);

			$responseData['SurveyRouteNames'] = [];

			//set db target
			$headers = getallheaders();
			TabletRouteName::setClient($headers['X-Client']);

			$mapGridCount = count($mapGrids['MapGridUIDs']);

			for($i = 0; $i < $mapGridCount; $i++)
			{
				$routeNames = TabletRouteName::find()
					->select('RouteName')
					->where(['MapGridUID' => $mapGrids['MapGridUIDs'][$i]])
					->all();

				$routeNameArray = [];
				$routeNameCount = count($routeNames);

				for($j = 0; $j < $routeNameCount; $j++)
				{
					$routeNameArray[] = $routeNames[$j]->RouteName;
				}

				$responseData['SurveyRouteNames'][]=[
				'MapGridUID' => $mapGrids['MapGridUIDs'][$i],
				'RouteNames' => $routeNameArray
				];
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
	/////////////////////TABLET DROPDOWNS END////////////////////////


    /////////// WebManagement LeakLog form modal dropdowns //////////
    //helper method for standard tablet survey query
    public static function webDropdownQuery($filter)
    {
        return DropDowns::find()
            ->select(['FilterName', 'SortSeq', 'FieldDisplay', 'OutValue'])
            ->where(['FilterName'=>$filter])
            ->andWhere(['ActiveFlag'=>1])
            ->orderBy('SortSeq','FieldDisplay')
            ->all();
    }
    public function actionGetServiceMainFormDropdowns() {
        try
        {
            //set db target
            $headers = getallheaders();
            DropDowns::setClient($headers['X-Client']);

            $responseData['dropdowns'] = [];

            //﻿ ddLHSurveyTypeSM
            $responseData['dropdowns']['ddLHSurveyTypeSM']= DropdownController::webDropdownQuery('ddLHSurveyTypeSM');

            //﻿ ddLHSurveyMode
            $responseData['dropdowns']['ddLHSurveyMode']= DropdownController::webDropdownQuery('ddLHSurveyMode');

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

    public function actionGetWebMgmtLeakLogFormDropdowns($mapGridUid) {
        try
        {
            //set db target
            $headers = getallheaders();
            DropDowns::setClient($headers['X-Client']);

            $responseData['dropdowns'] = [];

            //﻿ ddAboveBelowType
            $responseData['dropdowns']['ddAboveBelowType']= DropdownController::webDropdownQuery('ddAboveBelowType');

            //﻿ ddFacilityType
            $responseData['dropdowns']['ddFacilityType']= DropdownController::webDropdownQuery('ddFacilityType');


            //﻿﻿ddGradeByInstType
            $responseData['dropdowns']['ddGradeByInstType']= DropdownController::webDropdownQuery('ddGradeByInstType');

            //﻿﻿ddGradeType
            $responseData['dropdowns']['ddGradeType']= DropdownController::webDropdownQuery('ddGradeType');

            // TODO revise this
            //﻿﻿ddInstGradeByType
            //$responseData['dropdowns']['ddInstGradeByType']= DropdownController::webDropdownQuery('ddInstGradeByType');
            $responseData['dropdowns']['ddInstGradeByType']= [];

            //﻿﻿ddInitialLeakSourceType
            $responseData['dropdowns']['ddInitialLeakSourceType']= DropdownController::webDropdownQuery('ddInitialLeakSourceType');

            //﻿﻿ddInstrumentType
            //$responseData['dropdowns']['ddInstrumentType']= DropdownController::webDropdownQuery('ddInstrumentType');

            //﻿﻿ddPipelineType
            $responseData['dropdowns']['ddPipelineType']= DropdownController::webDropdownQuery('ddPipelineType');

            //﻿﻿ddSORLType
            $responseData['dropdowns']['ddSORLType']= DropdownController::webDropdownQuery('ddSORLType');

            //﻿﻿ddSurveyFrequencyTR
            //$responseData['dropdowns']['ddSurveyFrequencyTR']= DropdownController::webDropdownQuery('ddSurveyFrequencyTR');

            //﻿﻿ddSurveyType
            //$responseData['dropdowns']['ddSurveyType']= DropdownController::webDropdownQuery('ddSurveyType');

            // TODO revise this
            //  ddWithin5FtBuildingType﻿- special
            //$responseData['dropdowns']['ddWithin5FtBuildingType']= DropdownController::webDropdownQuery('ddWithin5FtBuildingType');
            $responseData['dropdowns']['ddWithin5FtBuildingType']= DropdownController::webDropdownQuery('ddYesNo');

            //﻿﻿ddReportedByType
            $responseData['dropdowns']['ddReportedByType']= DropdownController::webDropdownQuery('ddReportedByType');

            // TODO revise this
            //﻿﻿ddSuspectCoperType  - special
            //$responseData['dropdowns']['ddSuspectCoperType']= DropdownController::webDropdownQuery('ddSuspectCoperType');
            $responseData['dropdowns']['ddSuspectCoperType']= DropdownController::webDropdownQuery('ddYesNo');

            //﻿ ddPotentialHCAType - special
            //$responseData['dropdowns']['ddPotentialHCAType']= DropdownController::webDropdownQuery('ddPotentialHCAType');
            $responseData['dropdowns']['ddPotentialHCAType']= DropdownController::webDropdownQuery('ddYesNo');

            //﻿ ﻿ddInfoCodeType
            $responseData['dropdowns']['ddInfoCodeType']= DropdownController::webDropdownQuery('ddInfoCodeType');

            // TODO revise this
            //﻿ ﻿ddPaveW2WType  - special
            // $responseData['dropdowns']['ddPaveW2WType']= DropdownController::webDropdownQuery('ddPaveW2WType');
            $responseData['dropdowns']['ddPaveW2WType']= DropdownController::webDropdownQuery('ddYesNo');

            //﻿﻿ddSORLType
            $responseData['dropdowns']['ddSORLType']= DropdownController::webDropdownQuery('ddSORLType');

            CityCounty::setClient($headers['X-Client']);
            // cityList﻿
            // TODO find a better way that selecting all the cities if there are a lot of cities
            $responseData['dropdowns']['cityList']= CityCounty::find()->select(['City'])->orderBy('City ASC')->all();

//            $mapGridUid = 'MapGrid_852695586_20160824220014_System';
            //﻿routeNames
            $responseData['dropdowns']['routeNames']= [];
            if (!empty($mapGridUid)) {
                $responseData['dropdowns']['routeNames']= TabletRouteName::find()
                    ->select(['RouteName'])
                    ->where(['MapGridUID'=>$mapGridUid])
                    ->orderBy('RouteName')
                    ->all();
            }
//            Yii::trace(PHP_EOL.PHP_EOL.PHP_EOL.'------------'.$mapGridUid.PHP_EOL.PHP_EOL);
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
    /////////// WebManagement LeakLog form modal dropdowns end //////////

    /////////// Start WebManagement MapStamp dropdowns //////////////
    // not in use
    public function actionGetMapStampDivisionDropdown() {
        try{

            $headers = getallheaders();
            WebManagementMapStampDropDown::setClient($headers['X-Client']);
            
            $connection = BaseActiveRecord::getDb();
            $divisionCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownMapStampDivision() Order By Division");
            $values = $divisionCommand->queryAll();

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

        } catch(ForbiddenHttpException $e)  {

            throw new ForbiddenHttpException;

        } catch(\Exception $e) {

            throw new \yii\web\HttpException(400);

        }
    }

    // not in use
    public function actionGetMapStampWorkCenterDropdown($division) {
        try{

            $headers = getallheaders();
            WebManagementMapStampDropDown::setClient($headers['X-Client']);

            $connection = BaseActiveRecord::getDb();
            $divisionCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownMapStampWorkCenter(:division) Order By Workcenter")
                ->bindParam(':division', $division,  \PDO::PARAM_STR);
            $values = $divisionCommand->queryAll();

            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    "id" => $value["Workcenter"],
                    "name" => $value["Workcenter"]
                ];
            }

            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $results;

            return $response;
        } catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * return combined data for both division and work center dropdown
     * @return mixed
     */
    public function actionGetMapStampRefactoredDropdown()
    {
        //TODO RBAC permission check
        try {

            $headers = getallheaders();
            BaseActiveRecord::setClient($headers['X-Client']);

            $connection = BaseActiveRecord::getDb();

            $combinedArray = array();

            $divisionNamePairs = [
                null => 'Select...',
            ];

            $divisionCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownMapStampDivision() Order By Division");
            $values = $divisionCommand->queryAll();

            foreach ($values as $value) {
                $divisionNamePairs[$value['Division']] = $value['Division'];
            }

            $combinedArray['Division'] = $divisionNamePairs;
            $combinedArray['DivisionWorkCenter'] = array();
            $combinedArray['DivisionWorkCenter'] = [
                null => ['Select...'],
            ];

            foreach ($values as $value) {
                $workCenterCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownMapStampWorkCenter(:division) Order By Workcenter")
                    ->bindParam(':division', $value['Division'], \PDO::PARAM_STR);
                $workCenterValues = $workCenterCommand->queryAll();
                foreach ($workCenterValues as $workCenter) {
                    $workCenterNamePairs[] = $workCenter['Workcenter'];
                }
                $combinedArray['DivisionWorkCenter'][$value['Division']] = $workCenterNamePairs;

                // reset temp array
                $workCenterNamePairs = [];
            }

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $combinedArray;

            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
    /////////// End WebManagement MapStamp dropdowns //////////////

    /////////// Start WebManagement MapStamp Equipment Services PIC dropdowns //////////////
    public function actionGetMapStampEquipmentServicesPicDropdowns() {
        try
        {
            //set db target
            $headers = getallheaders();
            DropDowns::setClient($headers['X-Client']);

            $responseData['dropdowns'] = [];

            //﻿ddLHSurveyTypeSM - not needed for PICARO since it will always be PIC
            // $responseData['dropdowns']['ddLHSurveyTypeSM']= DropdownController::webDropdownQuery('ddLHSurveyTypeSM');

            //﻿ ddLHSurveyMode
            $responseData['dropdowns']['ddLHSurveyMode']= DropdownController::webDropdownQuery('ddLHSurveyMode');

            $sql = "SELECT '' as OutValue, 'Please Make Selection' as FieldDisplay
                    UNION
                    SELECT PicSerNo as OutValue, PicSerNo as FieldDisplay FROM vWebManagementAllPicaroSerNo;";
            $command = DropDowns::getDb()->createCommand($sql);
            $values = $command->queryAll();
            $responseData['dropdowns']['EquipmentPicaroSerNumbers'] = $values;

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
    /////////// End WebManagement MapStamp Equipment Services PIC dropdowns //////////////

    /////////// Start WebManagement MapStamp Associate Plan downdown //////////////
    public function actionGetMapStampAssociatePlanWorkCenterDropDown()
    {
        try {
            //set db target
            $headers = getallheaders();
            WebManagementDropDownAssociatePlanIR::setClient($headers['X-Client']);

            $namePairs = [];
            $query = WebManagementDropDownAssociatePlanIR::find()->select(['WorkCenter'])->distinct()->orderBy('WorkCenter')->all();

            $namePairs = [null => "Select..."];
            $dataSize = count($query);

            for($i=0; $i < $dataSize; $i++)
            {
                $namePairs[$query[$i]->WorkCenter]= $query[$i]->WorkCenter;
            }

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $namePairs;
            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetMapStampAssociatePlanFlocDropDown($workcenter){
        try
        {
            //set db target
            $headers = getallheaders();
            WebManagementDropDownAssociatePlanIR::setClient($headers['X-Client']);

            $namePairs = [];
            $query = WebManagementDropDownAssociatePlanIR::find()->select('FLOC')->distinct()->where(['WorkCenter'=>$workcenter])->orderBy('FLOC')->all();

            $dataSize = count($query);

            for ($i = 0; $i < $dataSize; $i++) {
                $namePairs[] = [
                    'id' => $query[$i]->FLOC,
                    'name' => $query[$i]->FLOC];
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

    public function actionGetMapStampAssociatePlanSurveyFreqDropDown($workcenter, $floc){
        try
        {
            //set db target
            $headers = getallheaders();
            WebManagementDropDownAssociatePlanIR::setClient($headers['X-Client']);

            $namePairs = [];
            $query = WebManagementDropDownAssociatePlanIR::find()->select(['SurveyType'])->distinct()->where(['WorkCenter'=>$workcenter])->andWhere(['FLOC'=>$floc])->orderBy('SurveyType')->all();
            $dataSize = count($query);

            for ($i = 0; $i < $dataSize; $i++) {
                $namePairs[] = [
                    'id' => $query[$i]->SurveyType,
                    'name' => $query[$i]->SurveyType];
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

    public function actionGetMapStampAssociatePlanInspectionRequestDropDown($notificationID){
        try
        {
            //set db target
            $headers = getallheaders();
            WebManagementDropDownAssociatePlanIR::setClient($headers['X-Client']);

            $query = WebManagementDropDownAssociatePlanIR::find()->select(['InspectionRequestUID'])->where(['NotificationNumber'=>$notificationID])->one();
            $responseData = $query;

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

    public function actionGetMapStampAssociatePlanNotificationIDDropDown($workcenter, $floc, $surveyfreq){
        try
        {
            //set db target
            $headers = getallheaders();
            WebManagementDropDownAssociatePlanIR::setClient($headers['X-Client']);

            $query = WebManagementDropDownAssociatePlanIR::find()->select(['NotificationNumber'])->where(['WorkCenter'=>$workcenter])->andWhere(['FLOC'=>$floc])->andWhere(['SurveyType'=>$surveyfreq])->all();
            $dataSize = count($query);

            for ($i = 0; $i < $dataSize; $i++) {
                $namePairs[] = [
                    'id' => $query[$i]->NotificationNumber,
                    'name' => $query[$i]->NotificationNumber];
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

    /////////// End WebManagement MapStamp Equipment Services PIC dropdowns //////////////

    /////////// Start WebManagement Tracker History dropdowns //////////////
    public function actionGetTrackerHDivisionDropdown()
    {
        try {

            $headers = getallheaders();
            WebManagementTrackerHistoryDropDown::setClient($headers['X-Client']);

            $values = WebManagementTrackerHistoryDropDown::find()
                ->select(['Division'])
                ->where(['not', ['Division' => null]])
                ->andWhere(['not', ['WorkCenter' => null]])
                ->andWhere(['not', ['Surveyor' => null]])
                ->distinct()
                ->all();

            $namePairs = [
                null => "Select...",
            ];
            foreach ($values as $value) {
                $namePairs[$value["Division"]] = $value["Division"];
            }

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $namePairs;

            return $response;

        } catch (ForbiddenHttpException $e) {

            throw new ForbiddenHttpException;

        } catch (\Exception $e) {

            throw new \yii\web\HttpException(400);

        }
    }

    public function actionGetTrackerHWorkCenterDropdown($division = null, $flatArray = false)
    {
        try {

            $headers = getallheaders();
            WebManagementTrackerHistoryDropDown::setClient($headers['X-Client']);
            if ($division !== null && $division !== "") {
                $values = WebManagementTrackerHistoryDropDown::find()
                    ->select(['WorkCenter'])
                    ->where(['not', ['Division' => null]])
                    ->andWhere(['not', ['WorkCenter' => null]])
                    ->andWhere(['not', ['Surveyor' => null]])
                    ->andwhere(['Division' => $division])
                    ->distinct()->all();
            } else {
                $values = WebManagementTrackerHistoryDropDown::find()
                    ->select(['WorkCenter'])
                    ->where(['not', ['Division' => null]])
                    ->andWhere(['not', ['WorkCenter' => null]])
                    ->andWhere(['not', ['Surveyor' => null]])
                    ->distinct()->all();
            }

            $results = [];
            foreach ($values as $value) {
                if ($flatArray) {
                    $results[$value["WorkCenter"]] = $value["WorkCenter"];
                } else {
                    $results[] = [
                        "id" => $value["WorkCenter"],
                        "name" => $value["WorkCenter"]
                    ];
                }
            }

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $results;

            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetTrackerHWorkCenterDivisionFromSurveyor($surveyor)
    {
        $headers = getallheaders();
        BaseActiveRecord::setClient($headers['X-Client']);
        $connection = BaseActiveRecord::getDb();
        $workQueueCommand = $connection->
        createCommand("SELECT WorkCenter From fnWebManagementDropDownTrackerHistoryWorkcenter(NULL, :Surveyor)")
            ->bindParam(':Surveyor', $surveyor, \PDO::PARAM_STR);
        $divisionCommand = $connection->
        createCommand("SELECT Division From fnWebManagementDropDownTrackerHistoryDivision(:Surveyor)")
            ->bindParam(':Surveyor', $surveyor, \PDO::PARAM_STR);

        $results["workCenter"] = $workQueueCommand->queryOne()["WorkCenter"];
        $results["division"] = $divisionCommand->queryOne()["Division"];
        Yii::trace("Surveyor for aGTHWCDFS: $surveyor");

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = $results;
        return $response;
    }

    public function actionGetTrackerHSurveyorDropdown($division = null, $workCenter = null, $startDate = null, $endDate = null, $flatArray = false)
    {
        try {

            $headers = getallheaders();
            WebManagementTrackerHistoryDropDown::setClient($headers['X-Client']);
            BaseActiveRecord::setClient($headers['X-Client']);
            $connection = BaseActiveRecord::getDb();

            // If workCenter is empty we set it to null so the SQL function works
            if ($workCenter == "") {
                $workCenter = null;
            }
            $workQueueCommand = $connection->
            createCommand("SELECT Surveyor From fnWebManagementDropDownTrackerHistorySurveyor(:Workcenter)")
                ->bindParam(':Workcenter', $workCenter, \PDO::PARAM_STR);
            Yii::trace("Raw SQL from actionGetTrackerHSurveyorDropdown: " . $workQueueCommand->getRawSql());
            $values = $workQueueCommand->queryAll();

            //$results = $values;
            $results = [];
            foreach ($values as $value) {
                if ($flatArray) {
                    $results[strtolower($value["Surveyor"])] = $value["Surveyor"];
                }else {
                    $results[] = [
                        "id" => strtolower($value["Surveyor"]),
                        "name" => $value["Surveyor"]
                    ];
                }
            }

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $results;

            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
    /////////// End WebManagement Tracker History dropdowns //////////////
	
	/////////// Begin Leak Log Management Dropdowns//////////////

    // not in use
	public function actionGetLeakLogDivisionDropdown()
    {
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            BaseActiveRecord::setClient($headers['X-Client']);
			
			$connection = BaseActiveRecord::getDb();
			
			$divisionCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownLeakLogDivision() Order By Division");
			$values = $divisionCommand->queryAll();

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

    // not in use
	public function actionGetLeakLogWorkCenterDropdown($division)
    {
        //TODO RBAC permission check
        try{

            $headers = getallheaders();
            BaseActiveRecord::setClient($headers['X-Client']);

			$connection = BaseActiveRecord::getDb();
			
			$divisionCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownLeakLogWorkCenter(:division) Order By Workcenter")
				->bindParam(':division', $division,  \PDO::PARAM_STR);
			$values = $divisionCommand->queryAll();
			
            $results = [];
            foreach ($values as $value) {
                $results[] = [
                    'id' => $value['Workcenter'],
                    'name' => $value['Workcenter']
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
            BaseActiveRecord::setClient($headers['X-Client']);

			$connection = BaseActiveRecord::getDb();
			
			$divisionCommand = $connection->createCommand("SELECT Surveyor From fnWebManagementDropDownLeakLogSurveyor(:workCenter) Order By Surveyor")
				->bindParam(':workCenter', $workCenter,  \PDO::PARAM_STR);
			$values = $divisionCommand->queryAll();
			
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

    /**
     * return combined data for both division and work center dropdown
     * @return mixed
     */
    public function actionGetLeakLogRefactoredDropdown()
    {
        //TODO RBAC permission check
        try {

            $headers = getallheaders();
            BaseActiveRecord::setClient($headers['X-Client']);

            $connection = BaseActiveRecord::getDb();

            $combinedArray = array();

            $divisionNamePairs = [
                null => 'Select...',
            ];

            $divisionCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownLeakLogDivision() Order By Division");
            $values = $divisionCommand->queryAll();

            foreach ($values as $value) {
                $divisionNamePairs[$value['Division']] = $value['Division'];
            }

            $combinedArray['Division'] = $divisionNamePairs;
            $combinedArray['DivisionWorkCenter'] = array();
            $combinedArray['DivisionWorkCenter'] = [
                null => ['Select...'],
            ];

            foreach ($values as $value) {
                $workCenterCommand = $connection->createCommand("SELECT * From fnWebManagementDropDownLeakLogWorkCenter(:division) Order By Workcenter")
                    ->bindParam(':division', $value['Division'], \PDO::PARAM_STR);
                $workCenterValues = $workCenterCommand->queryAll();
                foreach ($workCenterValues as $workCenter) {
                    $workCenterNamePairs[] = $workCenter['Workcenter'];
                }
                $combinedArray['DivisionWorkCenter'][$value['Division']] = $workCenterNamePairs;

                // reset temp array
                $workCenterNamePairs = [];
            }

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $combinedArray;

            return $response;
        } catch (ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
	/////////// End Leak Log Management Dropdowns//////////////
	
	
}