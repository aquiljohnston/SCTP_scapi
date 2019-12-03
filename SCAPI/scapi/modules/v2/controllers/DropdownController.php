<?php

namespace app\modules\v2\controllers;

use app\modules\v2\models\BaseActiveRecord;
use Yii;
use app\modules\v2\authentication\TokenAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use app\modules\v2\models\EmployeeType;
use app\modules\v2\models\DropDown;
use app\modules\v2\models\AppRoles;
use app\modules\v2\models\Project;
use app\modules\v3\models\MileageRate;
use app\modules\v2\controllers\BaseActiveController;
use yii\web\Response;
use \DateTime;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v2\models\StateCode;
use yii\db\Query;


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
					'get-state-codes-dropdown' => ['get'],
                    'get-employee-type-dropdown' => ['get'],
					'get-web-drop-downs' => ['get'],
					'get-tracker-map-grids' => ['get'],
					'get-roles-dropdowns'  => ['get'],
					'get-user-projects' => ['get'],
                    'get-tablet-survey-dropdowns' => ['get'],
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
        } catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
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
        } catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }
	
	//gets web dropdowns from rDropDown
	//TODO combine this with actionGetTabletSurveyDropdowns() 
	//by adding param DropDownType to differentiate between web and tablet dropdowns
	public function actionGetWebDropDowns()
	{
		try
        {
			//set db target
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			// RBAC permission check
            PermissionsController::requirePermission('getWebDropDowns', $client);
			
			$webDropDowns = DropDown::find()
				->select(['FilterName', 'SortSeq', 'FieldDisplay', 'FieldValue'])
				->distinct()
				->where(['DropDownType' => 'Web'])
				->orderBy([
					  'FilterName' => SORT_ASC,
					  'SortSeq' => SORT_ASC
					])
				->all();
				
			$responseArray['WebDropDowns'] = [];
			//loop data to format response
			foreach($webDropDowns as $dropDown)
			{
				$responseArray['WebDropDowns'][$dropDown->FilterName][] = $dropDown;
			}
			
            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $responseArray;

            return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
	}

    public function actionGetTrackerMapGrids() {
		try{
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			// RBAC permission check
            PermissionsController::requirePermission('getTrackerMapGridsDropdown', $client);
			
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
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
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
	public function actionGetRolesDropdowns(){
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
				if(PermissionsController::can('userUpdate' . $roles[$i]->AppRoleName, null, $client))
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
	
	public function actionGetUserProjects()
	{
		try{
			//get http headers
            $headers = getallheaders();
			$client  = $headers['X-Client'];
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			if(BaseActiveController::isSCCT($client)){
				$showProjectDropdown = true;
				//RBAC permission check and base query
				if(PermissionsController::can('projectGetAll')){
					$relationQuery = (new Query())->select('ProjUserProjectID')->distinct()->from('Project_User_Tb');
				} else if (PermissionsController::can('projectGetOwnProjects')){
					//get user id from auth token
					$user = BaseActiveController::getUserFromToken();
					$relationQuery = (new Query())->select('ProjUserProjectID')->from('Project_User_Tb')
						->where(['ProjUserUserID' => $user->UserID]);
				} else {
					throw new ForbiddenHttpException;
				}
				//get projects
				$projects = Project::find()->select('*')->where(['in', 'ProjectID', $relationQuery])->orderBy('ProjectName')->all();
			} else {
				PermissionsController::requirePermission('projectGetOwnProjects');
				$showProjectDropdown = false;
				$projects = Project::find()->where(['ProjectUrlPrefix' => $client])->all();
			}
			
			//create dropdown pair array
			if(count($projects) > 1)
			{
				$dropdownPairs = [null => 'All'];
			} else {
				$dropdownPairs = [];
			}

			//populate response array
			foreach($projects as $project)
			{
				$dropdownPairs[$project->ProjectID]= $project->ProjectName;
			}
			
			//format response data
			$responseArray['showProjectDropdown'] = $showProjectDropdown;
			$responseArray['projects'] = $dropdownPairs;
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $responseArray;
			
			return $response;
		} catch(ForbiddenHttpException $e) {
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
	}
	
	/////////////////////TABLET DROPDOWNS BEGIN////////////////////////
	//route to provide data for all survey dropdowns on the tablet
	public function actionGetTabletSurveyDropdowns()
	{
		try{
			//set db target
			$client = getallheaders()['X-Client'];
			BaseActiveRecord::setClient($client);
			//have to set MileageRate because it is a v3 model
			MileageRate::setClient($client);
			// RBAC permission check
            PermissionsController::requirePermission('getTabletSurveyDropdowns', $client);
			
			//create db transaction
			$db = BaseActiveRecord::getDb();
			$transaction = $db->beginTransaction();
			
			$tabletDropDowns = DropDown::find()
				->select(['FilterName', 'SortSeq', 'FieldDisplay', 'FieldValue'])
				->distinct()
				->where(['DropDownType' => 'Tablet'])
				->orderBy([
					  'FilterName' => SORT_ASC,
					  'SortSeq' => SORT_ASC
					])
				->all();
			$responseArray['TabletDropDowns'] = [];
			
			$mileageRate = MileageRate::find()
				->select(['ID as SortSeq', 'MileageType as FieldDisplay', 'Rate as FieldValue'])
				->asArray()
				->all();
			
			$transaction->commit();
			
			//add mileage rate to dropdowns
			$filterName = 'MileageRate';
			$responseArray['TabletDropDowns'][$filterName][] = [
				'SortSeq' => 0,
				'FieldDisplay' => 'Select Rate',
				'FieldValue' => null,
				'FilterName' => $filterName
			];
			foreach($mileageRate as $mileage){
				$mileage['FilterName'] = $filterName;
				//convert data types back after asArray
				$mileage['SortSeq'] = (int)$mileage['SortSeq'];
				$mileage['FieldValue'] = (float)$mileage['FieldValue'];
				$responseArray['TabletDropDowns'][$filterName][] = $mileage;
			}

			//loop data to format response
			foreach($tabletDropDowns as $dropDown){
				$responseArray['TabletDropDowns'][$dropDown->FilterName][] = $dropDown;
			}
			
            $response = Yii::$app ->response;
            $response -> format = Response::FORMAT_JSON;
            $response -> data = $responseArray;

            return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(\Exception $e){
            throw new \yii\web\HttpException(400);
        }
	}
	/////////////////////TABLET DROPDOWNS END////////////////////////
}