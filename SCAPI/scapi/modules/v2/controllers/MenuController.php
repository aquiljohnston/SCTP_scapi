<?php

namespace app\modules\v2\controllers;

use yii;
use app\modules\v2\authentication\TokenAuth;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\controllers\PermissionsController;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use app\modules\v2\constants\Constants;
use app\modules\v2\models\Project;
use app\modules\v2\models\Client;
use app\modules\v2\models\MenusProjectModule;
use app\modules\v2\models\MenusModuleMenu;
use app\modules\v2\models\MenusModuleSubMenu;

class MenuController extends Controller {
	
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
                    'get' => ['get']
                ],  
            ];
		return $behaviors;		
	}
	
	//public function actionGet()
	//params
	//$project string the current project of the user_error
	//$permissionsController string path to class of desired permission controller to be used in call_user_func_array defaults to base permissionsController
	//$parmArray array containing any additonal paramaters that an alternative permission controller may require
	public function actionGet($permissionsController = Constants::PERMISSION_CONTROLLER, $permissionCheckParmArray = [])
	{
		try{
			//set db
			$headers = getallheaders();
			//BaseActiveRecord::setClient($headers['X-Client']);
			//set db target
			Project::setClient(BaseActiveController::urlPrefix());

			//build menu array for project Id
			//create data arrays
			$menuArray = [];
			$modules = []; 
			$navigationArray = [];
			$subNavigationArray = [];
			
			//get project data
			$projectObj = Project::find()
				->where(['ProjectUrlPrefix' => $headers['X-Client']])
				->one();

			$projectID = $projectObj->ProjectID;
			//get client data
			if($projectObj != null)
			{
				$client = Client::findOne($projectObj->ProjectClientID);
			}
			else{
				$client = null;
			}
			
			//populate menu array
			if ($client != null)
			{
				$menuArray["ClientName"] = $client->ClientName;
			}
			else{
				$menuArray["ClientName"] = null;
			}
			$menuArray["ProjectID"] = $projectID;
			
			//get active modules for enabled flags
			$relatedModules = MenusProjectModule::find()
					->where("ProjectModulesProjectID = $projectID")
					->all();
			$relatedModulesCount = count($relatedModules);
			
			//get all nav menus to traverse
			$allNavMenus = MenusModuleMenu::find()
					->all();
			$allNavMenusCount = Count($allNavMenus);
			
			//varibles to manage unique modules
			$uniqueModules = [];
			$uniqueModCount = 0;

			
			//traverse nav menus to populate sub menus
			for($i=0; $i < $allNavMenusCount; $i++)
			{
				//current module name
				$moduleName = $allNavMenus[$i]->ModuleMenuName;
				//get unique modules
				if (!in_array($moduleName, $uniqueModules))
				{
					$uniqueModules[] = $moduleName;
					$modules[$moduleName]["enabled"] = 0;
				}				
				
				//check module relationships for flag
				for ($r = 0; $r < $relatedModulesCount; $r++)
				{
					if($relatedModules[$r]->ProjectModulesName == $moduleName)
					{
						$modules[$moduleName]["enabled"] = 1;
						break;
					}
				}
				
				//get nav menu name, url, and enableFlag
				$navigationArray["NavigationName"] = $allNavMenus[$i]->ModuleMenuNavMenuName;
				$navigationArray["Url"] = $allNavMenus[$i]->ModuleMenuUrl;
				$navigationArray["SortSequence"] = $allNavMenus[$i]->SortSequence;
				
				yii::trace("Modules Array: " . json_encode($modules));
				
				//if(PermissionsController::can($allNavMenus[$i]->ModuleMenuPermissionName) && $modules[$moduleName]["enabled"] == 1){
				if(call_user_func_array($permissionsController.'::can', array_merge(array($allNavMenus[$i]->ModuleMenuPermissionName, null,$headers['X-Client']), $permissionCheckParmArray)) 
					&& $modules[$moduleName]["enabled"] == 1)
				{
					$navigationArray["enabled"] = 1;
				} else {
					$navigationArray["enabled"] = 0;
				}
				
				//set db back to ct after permission check
				Project::setClient(BaseActiveController::urlPrefix());
				
				//if navUrl is null populate sub navs
				if ($allNavMenus[$i]->ModuleMenuUrl == null)
				{
					//get sub navs for current menu
					$navID = $allNavMenus[$i]->ModuleMenuID;
					$subNavs = MenusModuleSubMenu::find()
						->where("ModuleSubMenusModuleMenuID_FK = $navID")
						->andwhere ("ModuleSubMenusActiveFlag = 1")
						->orderBy('ModuleSubMenusSortSeq')
						->all();
					$subNavCount = count($subNavs);
					
					//loop sub navs
					for ($s = 0; $s < $subNavCount; $s++)
					{
						$subNavData = [];
						$subNavData["SubNavigationName"] = $subNavs[$s]->ModuleSubMenusNavMenuName;
						$subNavData["Url"] = $subNavs[$s]->ModuleSubMenusURL;
						//check if user can see sub nav option
						//if(PermissionsController::can($subNavs[$s]->ModuleSubMenusPermissionName) && $navigationArray["enabled"] == 1)
						if(call_user_func_array($permissionsController.'::can', array_merge(array($subNavs[$s]->ModuleSubMenusPermissionName, null, $headers['X-Client']), $permissionCheckParmArray)) 
							&& $navigationArray["enabled"] == 1)
						{
							$subNavData["enabled"] = 1;
						}
						else
						{
							$subNavData["enabled"] = 0;
						}
						$subNavigationArray[] = $subNavData;
					}
					//set db back to ct after permission check
					Project::setClient(BaseActiveController::urlPrefix());
				}
				//push sub nav array into nav array
				if (!empty($subNavigationArray))
				{
					$navigationArray["SubNavigation"] = $subNavigationArray;
					$subNavigationArray = [];
				}
				//push navigation array into modules
				$modules[$moduleName]["NavigationMenu"][] = $navigationArray;
				$navigationArray = [];
			}
			
			//push the data into the response array
			$menuArray["Modules"][] = $modules;	
			
			$responseJson = json_encode($menuArray);
			
			Yii::Trace($responseJson);
			
			//send response
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $menuArray;
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