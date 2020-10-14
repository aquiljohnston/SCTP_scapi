<?php

namespace app\modules\v3\controllers;

use Yii;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\Project;
use app\modules\v3\models\ProjectConfiguration;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends BaseActiveController
{
	public $modelClass = 'app\modules\v3\models\Project'; 
	
	/**
	* sets verb filters for http request
	* @return an array of behaviors
	*/
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'view'  => ['get'],
					'get-all'  => ['get'],
					'get-config'  => ['get'],
					'update-config'  => ['put'],
                ],  
            ];
		return $behaviors;	
	}
	
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}
	
	use DeleteMethodNotAllowed;
	
	/**
	* Gets the data for a project based on a project id
	* @param $id the id of a project record
	* @returns Response json body of the project data
	* @throws \yii\web\HttpException
	*/	
	public function actionView($id){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('projectView');
			
			$project = Project::find()
				->where(['ProjectID' => $id])
				->asArray()
				->one();
			
			$projectConfig = ProjectConfiguration::find()
				->where(['ProjectID' => $id])
				->one();
				
			$project['IsEndOfDayTaskOut'] = $projectConfig != null ? $projectConfig->IsEndOfDayTaskOut : 0;
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $project;
			
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(UnauthorizedHttpException $e) {
            throw new UnauthorizedHttpException;
        }catch(\Exception $e){
            throw new \yii\web\HttpException(400);
        }
	} 

	/**
	* Gets all of the subclass's model's records
	*
	* @return Response The records in a JSON format
	* @throws \yii\web\HttpException 400 if any exceptions are thrown
	* @throws ForbiddenHttpException If permissions are not granted for request
	*/
    public function actionGetAll($listPerPage = null, $page = 1, $filter = null)
	{
		if(PermissionsController::can("projectGetAll")) {
			try
			{
				//set db target
				BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

				$projects = Project::find();
			}
			catch(\Exception $e)
			{
				throw new \yii\web\HttpException(400);
			}
		} else if (PermissionsController::can("projectGetOwnProjects")) {
			$userID = self::getUserFromToken()->UserID;

		    $projects = Project::find()->select('ProjectTb.*')
                ->leftJoin('Project_User_Tb', "[ProjectTb].[ProjectID] = [Project_User_Tb].[ProjUserProjectID]")
                ->where(['[Project_User_Tb].[ProjUserUserID]' => $userID])
                ->with('projectUserTbs');
		} else {
			throw new ForbiddenHttpException;
		}

        //apply filter to query
        if($filter != null)
        {
            $projects->andFilterWhere([
                'or',
                ['like', 'ProjectName', $filter],
                ['like', 'ProjectType', $filter],
                ['like', 'ProjectDescription', $filter],
                ['like', 'ProjectState', $filter],
                ['like', 'ProjectReferenceID', $filter],
            ]);
        }

        //pass query with pagination data to helper method
        $paginationResponse = BaseActiveController::paginationProcessor($projects, $page, $listPerPage);
        //use updated query with pagination
        $projectArr = $paginationResponse['Query']->all();
        $responseArray['pages'] = $paginationResponse['pages'];

        //populate response array
        $responseArray['assets'] = $projectArr;

		$response = Yii::$app->response;
		$response->format = Response::FORMAT_JSON;
		$response->setStatusCode(200);
		$response->data = $responseArray;
		return $response;
    }
	
	//get project config by project id if available
	public function actionGetConfig($projectID){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			// RBAC permission check
			PermissionsController::requirePermission('projectViewConfig');
			
			$projectConfig = ProjectConfiguration::find()
				->where(['ProjectID' => $projectID])
				->one();
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			$responseArray['ProjectConfig'] = $projectConfig;

			//return response data
			$response->data = $responseArray;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(UnauthorizedHttpException $e) {
            throw new UnauthorizedHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
	}
	
	//update project config by project id if available
	//if none exist create a new config
	public function actionUpdateConfig(){
		try{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());

			//get created by token
			$createdBy = BaseActiveController::getUserFromToken()->UserName;
			
			// RBAC permission check
			PermissionsController::requirePermission('projectUpdateConfig');
			
			//capture post body
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);
			
			//archive json
			BaseActiveController::archiveWebJson($data, 'Update Project Config', $createdBy, BaseActiveController::urlPrefix());
			
			//pull data from envelope
			$data = $data['ProjectConfig'];
			$successFlag = 0;
			
			
			$projectConfig = ProjectConfiguration::find()
				->where(['ProjectID' => $data['ProjectID']])
				->one();
			
			//if record exist update else create a new record
			if($projectConfig != null){
				$projectConfig->attributes = $data;
				$projectConfig->ModifiedBy = $createdBy;
				$projectConfig->ModifiedDate = BaseActiveController::getDate();
				if($projectConfig->update()){
					$successFlag = 1;
				}else{
					throw BaseActiveController::modelValidationException($projectConfig);
				}
			}else{
				$newProjectConfig = new ProjectConfiguration;
				$newProjectConfig->attributes = $data;
				$newProjectConfig->CreatedBy = $createdBy;
				$newProjectConfig->CreatedDate = BaseActiveController::getDate();
				if($newProjectConfig->save()){
					$successFlag = 1;
				}else{
					throw BaseActiveController::modelValidationException($projectConfig);
				}
			}			
			
			//create response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			
			$responseArray['ProjectConfig'] = [
				'Project' => $data['ProjectID'],
				'SuccessFlag' => $successFlag
			];

			//return response data
			$response->data = $responseArray;
			return $response;
		}catch(ForbiddenHttpException $e){
            throw new ForbiddenHttpException;
        }catch(UnauthorizedHttpException $e) {
            throw new UnauthorizedHttpException;
        }catch(\Exception $e){
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, BaseActiveController::urlPrefix());
            throw new \yii\web\HttpException(400);
        }
	}
}
