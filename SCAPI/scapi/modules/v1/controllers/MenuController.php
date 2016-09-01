<?php

namespace app\modules\v1\controllers;

use yii;
use app\authentication\TokenAuth;
// use app\modules\v1\authentication\TokenAuth;
use yii\web\Controller;
use yii\web\Response;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\controllers\PermissionsController;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use app\modules\v1\models\Project;
use app\modules\v1\models\Client;
use app\modules\v1\models\MenusProjectModule;
use app\modules\v1\models\MenusModuleMenu;
use app\modules\v1\models\MenusModuleSubMenu;

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
	public function actionGet($project)
	{
		// try{
			//set db target
			$headers = getallheaders();
			Project::setClient($headers['X-Client']);
			Client::setClient($headers['X-Client']);
			MenusProjectModule::setClient($headers['X-Client']);
			MenusModuleMenu::setClient($headers['X-Client']);
			MenusModuleSubMenu::setClient($headers['X-Client']);
			
			//build menu array for project Id
			//create data arrays
			$menuArray = [];
			$modules = [];
			$moduleArray = [];
			$navigationArray = [];
			$subNavigationArray = [];
			$subNavigation = [];
			
			//get project data
			$project = Project::find()
				->where("ProjectUrlPrefix = '$project'")
				->one();
			$projectID = $project->ProjectID;
			//get client data
			if($project != null)
			{
				$client = Client::findOne($project->ProjectClientID);
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
			
			//get active modules for enaabled flags
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
				//get unique modules
				if (!in_array($allNavMenus[$i]->ModuleMenuName, $uniqueModules))
				{
					$uniqueModules[] = $allNavMenus[$i]->ModuleMenuName;
					$moduleName = $allNavMenus[$i]->ModuleMenuName;
				}				
				
				//flag for module
				$relationFlag  = false;
				$userMgmtFlag = false;
				
				//check module relationships for flag
				for ($r = 0; $r < $relatedModulesCount; $r++)
				{
					if($relatedModules[$r]->ProjectModulesName == $allNavMenus[$i]->ModuleMenuName)
					{
						$relationFlag = true;
					}
				}
				
				//get nav menu name and url
				$navigationArray["NavigationName"] = $allNavMenus[$i]->ModuleMenuNavMenuName;
				$navigationArray["Url"] = $allNavMenus[$i]->ModuleMenuUrl;
				
				//if navUrl is null populate sub navs
				if ($allNavMenus[$i]->ModuleMenuUrl == null)
				{
					//get sub navs for current menu
					$navID = $allNavMenus[$i]->ModuleMenuID;
					$subNavs = MenusModuleSubMenu::find()
						->where("ModuleSubMenusModuleMenuID_FK = $navID")
						->all();
					$subNavCount = count($subNavs);
					
					//loop sub navs
					for ($s = 0; $s < $subNavCount; $s++)
					{
						$subNavData = [];
						$subNavData["SubNavigationName"] = $subNavs[$s]->ModuleSubMenusNavMenuName;
						$subNavData["Url"] = $subNavs[$s]->ModuleSubMenusURL;
						$permissionName = $subNavs[$s]->ModuleSubMenusPermissionName;
						if ($permissionName == "viewUserMgmt" && PermissionsController::can("Supervisor"))
						{
							$subNavData["enabled"] = 1;
							$userMgmtFlag = true;
						}
						else if((PermissionsController::can($permissionName) && $relationFlag) || PermissionsController::can("Admin"))
						{
							$subNavData["enabled"] = 1;
						}
						else
						{
							$subNavData["enabled"] = 0;
						}
						$subNavigationArray[] = $subNavData;
					}
				}
				//push sub nav array into nav array
				if (!empty($subNavigationArray))
				{
					$navigationArray["SubNavigation"] = $subNavigationArray;
					$subNavigationArray = [];
				}
				
				//if enableFlag is true set module enabled to 1
				if($relationFlag || $userMgmtFlag || PermissionsController::can("Admin"))
				{
					$modules[$allNavMenus[$i]->ModuleMenuName]["enabled"] = 1;
				}
				else{
					$modules[$allNavMenus[$i]->ModuleMenuName]["enabled"] = 0;
				}
				//push navigation array into modules
				$modules[$allNavMenus[$i]->ModuleMenuName]["NavigationMenu"][] = $navigationArray;
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
		// }
		// catch(\Exception $e) 
		// {
			// throw new \yii\web\HttpException(400);
		// }
	}
	
}