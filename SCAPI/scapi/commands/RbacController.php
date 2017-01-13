<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\v1\models\SCUser;
use app\rbac\ScDbManager;

/**
* This Class establishes the rules of the RBAC system for the API
* Permissions are created and assigned and the role hierarchy is established
*/
class RbacController extends Controller
{
	/**
	* Removes all RBAC settings that are currently in place and rebuilds the rule set
	* Creates Permissions for routes
	* Creates Roles
	* Creates Child Heirarcy for Roles and Permissions
	* Loops existing users to get previously assigned roles and reassign them
	*/
    public function actionInit($client)
    {
		SCUser::setClient($client);
		$db = SCUser::getDb();
		$auth = new ScDbManager($db);
		
		try{
			//reset all
			//$auth->removeAll();
			//create connection
			$connection = $db;
			//start transaction
			$transaction = $connection-> beginTransaction();
			//create commands
			$deleteAssignmentCommand = $connection->createCommand("DELETE FROM rbac.auth_assignment");
			$deleteItemChildCommand = $connection->createCommand("DELETE FROM rbac.auth_item_child");
			$deleteItemCommand = $connection->createCommand("DELETE FROM rbac.auth_item");
			$deleteRuleCommand = $connection->createCommand("DELETE FROM rbac.auth_rule");
			//execute commands
			$deleteAssignmentCommand->execute();
			$deleteItemChildCommand->execute();
			$deleteItemCommand->execute();
			$deleteRuleCommand->execute();
			//commit transaction
			$transaction->commit();
		} catch (Exception $e) {
			//roll back changes on failure
			$transaction->rollBack();
		}
			
		//Activity Code permissions/////////////////////////////////////////////////////////////////
		//add "activityCodeGetDropdown" permission
		$activityCodeGetDropdown = $auth->createPermission('activityCodeGetDropdown');
		$activityCodeGetDropdown->description = 'Get an associative array of Activity Codes';
		$auth->add($activityCodeGetDropdown);
		
		//Activity permissions/////////////////////////////////////////////////////////////////
		//add "activityView" permission
		$activityView = $auth->createPermission('activityView');
		$activityView->description = 'View an activity';
		$auth->add($activityView);
		
		// "activityCreate" permission
		$activityCreate = $auth->createPermission('activityCreate');
		$activityCreate->description = 'Create an activity';
		$auth->add($activityCreate);
		
		//App Role permissions/////////////////////////////////////////////////////////////////
		//add "appRoleGetDropdown" permission
		$appRoleGetDropdown = $auth->createPermission('appRoleGetDropdown');
		$appRoleGetDropdown->description = 'Get an associative array of App Roles';
		$auth->add($appRoleGetDropdown);
		
		//Client Accounts permissions/////////////////////////////////////////////////////////////////
		//add "clientAccountsGetDropdown" permission
		$clientAccountsGetDropdown = $auth->createPermission('clientAccountsGetDropdown');
		$clientAccountsGetDropdown->description = 'Get an associative array of Client Accounts';
		$auth->add($clientAccountsGetDropdown);
		
		//Client permissions/////////////////////////////////////////////////////////////////
        // add "clientGetAll" permission
        $clientGetAll = $auth->createPermission('clientGetAll');
        $clientGetAll->description = 'Get an array of all clients';
        $auth->add($clientGetAll);
		
		// add "clientView" permission
        $clientView = $auth->createPermission('clientView');
        $clientView->description = 'View a client';
        $auth->add($clientView);
		
		// add "clientCreate" permission
        $clientCreate = $auth->createPermission('clientCreate');
        $clientCreate->description = 'Create a client';
        $auth->add($clientCreate);

        // add "clientUpdate" permission
        $clientUpdate = $auth->createPermission('clientUpdate');
        $clientUpdate->description = 'Update client';
        $auth->add($clientUpdate);

        // add "clientDeactivate" permission
        $clientDeactivate = $auth->createPermission('clientDeactivate');
        $clientDeactivate->description = 'Deactivate client';
        $auth->add($clientDeactivate);
		
		// add "clientGetDropdown" permission
        $clientGetDropdown = $auth->createPermission('clientGetDropdown');
        $clientGetDropdown->description = 'Get an associative array of client ID/name pairs';
        $auth->add($clientGetDropdown);
		
		//Employee Type permissions/////////////////////////////////////////////////////////////////
		//add "employeeTypeGetDropdown" permission
		$employeeTypeGetDropdown = $auth->createPermission('employeeTypeGetDropdown');
		$employeeTypeGetDropdown->description = 'Get an associative array of employee types';
		$auth->add($employeeTypeGetDropdown);
		
		//Equipment Calibration permissions/////////////////////////////////////////////////////////////////
		//add "equipmentCalibrationCreate" permission
		$equipmentCalibrationCreate = $auth->createPermission('equipmentCalibrationCreate');
		$equipmentCalibrationCreate->description = 'Creates a new equipment calibration record';
		$auth->add($equipmentCalibrationCreate);
		
		//Equipment Condition permissions/////////////////////////////////////////////////////////////////
		//add "equipmentConditionGetDropdown" permission
		$equipmentConditionGetDropdown = $auth->createPermission('equipmentConditionGetDropdown');
		$equipmentConditionGetDropdown->description = 'Get an associative array of equipment conditions';
		$auth->add($equipmentConditionGetDropdown);
		
		//Equipment permissions/////////////////////////////////////////////////////////////////
        // add "getOwnEquipment" permission
        $getOwnEquipment = $auth->createPermission('getOwnEquipment');
        $getOwnEquipment->description = 'Get equipment for associated projects';
        $auth->add($getOwnEquipment);
		
		// add "getAllEquipment" permission
        $getAllEquipment = $auth->createPermission('getAllEquipment');
        $getAllEquipment->description = 'Get all equipment';
        $auth->add($getAllEquipment);
		
		// add "equipmentView" permission
        $equipmentView = $auth->createPermission('equipmentView');
        $equipmentView->description = 'View equipment';
        $auth->add($equipmentView);
		
		// add "equipmentCreate" permission
        $equipmentCreate = $auth->createPermission('equipmentCreate');
        $equipmentCreate->description = 'Create equipment';
        $auth->add($equipmentCreate);

        // add "equipmentUpdate" permission
        $equipmentUpdate = $auth->createPermission('equipmentUpdate');
        $equipmentUpdate->description = 'Update equipment';
        $auth->add($equipmentUpdate);
		
		// add "equipmentDelete" permission
        $equipmentDelete = $auth->createPermission('equipmentDelete');
        $equipmentDelete->description = 'Delete equipment';
        $auth->add($equipmentDelete);
		
		// add "acceptEquipment" permission
        $acceptEquipment = $auth->createPermission('acceptEquipment');
        $acceptEquipment->description = 'Accept equipment';
        $auth->add($acceptEquipment);
		
		//Equipment Status permissions/////////////////////////////////////////////////////////////////
		//add "equipmentStatusGetDropdown" permission
		$equipmentStatusGetDropdown = $auth->createPermission('equipmentStatusGetDropdown');
		$equipmentStatusGetDropdown->description = 'Get an associative array of equipment status';
		$auth->add($equipmentStatusGetDropdown);
		
		//Equipment Type permissions/////////////////////////////////////////////////////////////////
		//add "equipmentTypeGetDropdown" permission
		$equipmentTypeGetDropdown = $auth->createPermission('equipmentTypeGetDropdown');
		$equipmentTypeGetDropdown->description = 'Get an associative array of equipment types';
		$auth->add($equipmentTypeGetDropdown);
		
		//Mileage Card permissions/////////////////////////////////////////////////////////////////
        // add "mileageCardGetOwnCards" permission
        $mileageCardGetOwnCards = $auth->createPermission('mileageCardGetOwnCards');
        $mileageCardGetOwnCards->description = 'Get an array of mileage cards for associated projects';
        $auth->add($mileageCardGetOwnCards);
		
		// add "mileageCardGetAllCards" permission
        $mileageCardGetAllCards = $auth->createPermission('mileageCardGetAllCards');
        $mileageCardGetAllCards->description = 'Get an array of all mileage cards';
        $auth->add($mileageCardGetAllCards);
		
		// add "mileageCardView" permission
        $mileageCardView = $auth->createPermission('mileageCardView');
        $mileageCardView->description = 'View a mileage card';
        $auth->add($mileageCardView);
		
		// add "mileageCardGetCard" permission
        $mileageCardGetCard = $auth->createPermission('mileageCardGetCard');
        $mileageCardGetCard->description = 'Get a mileage card for a user';
        $auth->add($mileageCardGetCard);

        // add "mileageCardGetEntries" permission
        $mileageCardGetEntries = $auth->createPermission('mileageCardGetEntries');
        $mileageCardGetEntries->description = 'Get all mileage entries for a mileage card';
        $auth->add($mileageCardGetEntries);
		
		// add "mileageCardApprove" permission
        $mileageCardApprove = $auth->createPermission('mileageCardApprove');
        $mileageCardApprove->description = 'Approve a mileage card';
        $auth->add($mileageCardApprove);
		
		//Mileage Entry permissions/////////////////////////////////////////////////////////////////
        // add "mileageEntryView" permission
        $mileageEntryView = $auth->createPermission('mileageEntryView');
        $mileageEntryView->description = 'View a mileage entry';
        $auth->add($mileageEntryView);
		
		// add "mileageEntryCreate" permission
        $mileageEntryCreate = $auth->createPermission('mileageEntryCreate');
        $mileageEntryCreate->description = 'Create a mileage entry';
        $auth->add($mileageEntryCreate);
		
		// add "mileageEntryDeactivate" permission
        $mileageEntryDeactivate = $auth->createPermission('mileageEntryDeactivate');
        $mileageEntryDeactivate->description = 'Deactivate a mileage entry';
        $auth->add($mileageEntryDeactivate);
		
		//Notifications permissions/////////////////////////////////////////////////////////////////
        // add "notificationsGet" permission
        $notificationsGet = $auth->createPermission('notificationsGet');
        $notificationsGet->description = 'Get notifications';
        $auth->add($notificationsGet);
		
		//Pay Code permissions/////////////////////////////////////////////////////////////////
        // add "payCodeGetDropdown" permission
        $payCodeGetDropdown = $auth->createPermission('payCodeGetDropdown');
        $payCodeGetDropdown->description = 'Get an associative array of pay codes';
        $auth->add($payCodeGetDropdown);
		
		//Project permissions/////////////////////////////////////////////////////////////////
        // add "projectGetAll" permission
        $projectGetAll = $auth->createPermission('projectGetAll');
        $projectGetAll->description = 'Get an array of all projects';
        $auth->add($projectGetAll);
		
		// add "projectView" permission
        $projectView = $auth->createPermission('projectView');
        $projectView->description = 'View a project';
        $auth->add($projectView);
		
		// add "projectCreate" permission
        $projectCreate = $auth->createPermission('projectCreate');
        $projectCreate->description = 'Create a project';
        $auth->add($projectCreate);

        // add "projectUpdate" permission
        $projectUpdate = $auth->createPermission('projectUpdate');
        $projectUpdate->description = 'Update project';
        $auth->add($projectUpdate);
		
		// add "projectGetOwnProjects" permission
        $projectGetOwnProjects = $auth->createPermission('projectGetOwnProjects');
        $projectGetOwnProjects->description = 'Get all projects that a user is associated with';
        $auth->add($projectGetOwnProjects);
		
		// add "projectGetDropdown" permission
        $projectGetDropdown = $auth->createPermission('projectGetDropdown');
        $projectGetDropdown->description = 'Get an associative array of project name/id pairs';
        $auth->add($projectGetDropdown);
		
		// add "projectGetUserRelationships" permission
        $projectGetUserRelationships = $auth->createPermission('projectGetUserRelationships');
        $projectGetUserRelationships->description = 'Get two arrays one of users associated with a project and one of all other users';
        $auth->add($projectGetUserRelationships);
		
		// add "projectAddRemoveUsers" permission
        $projectAddRemoveUsers = $auth->createPermission('projectAddRemoveUsers');
        $projectAddRemoveUsers->description = 'Add or remove users from a project';
        $auth->add($projectAddRemoveUsers);
		
		// add "projectAddRemoveModules" permission
		$projectAddRemoveModules = $auth->createPermission('projectAddRemoveModules');
        $projectAddRemoveModules->description = 'Add or remove modules from a project';
        $auth->add($projectAddRemoveModules);
		
		//State Code permissions/////////////////////////////////////////////////////////////////
        // add "stateCodeGetDropdown" permission
        $stateCodeGetDropdown = $auth->createPermission('stateCodeGetDropdown');
        $stateCodeGetDropdown->description = 'Get an associative array of state codes';
        $auth->add($stateCodeGetDropdown);
		
		//Time Card permissions/////////////////////////////////////////////////////////////////
        // add "timeCardGetOwnCards" permission
        $timeCardGetOwnCards = $auth->createPermission('timeCardGetOwnCards');
        $timeCardGetOwnCards->description = 'Get an array of multiple time cards for associated projects';
        $auth->add($timeCardGetOwnCards);
		
		// add "timeCardGetAllCards" permission
        $timeCardGetAllCards = $auth->createPermission('timeCardGetAllCards');
        $timeCardGetAllCards->description = 'Get an array of all time cards';
        $auth->add($timeCardGetAllCards);
		
		// add "timeCardView" permission
        $timeCardView = $auth->createPermission('timeCardView');
        $timeCardView->description = 'View a time card';
        $auth->add($timeCardView);
		
		// add "timeCardApproveCards" permission
        $timeCardApproveCards = $auth->createPermission('timeCardApproveCards');
        $timeCardApproveCards->description = 'Approve time cards';
        $auth->add($timeCardApproveCards);

        // add "timeCardGetCard" permission
        $timeCardGetCard = $auth->createPermission('timeCardGetCard');
        $timeCardGetCard->description = 'Get a users time card';
        $auth->add($timeCardGetCard);
		
		// add "timeCardGetEntries" permission
        $timeCardGetEntries = $auth->createPermission('timeCardGetEntries');
        $timeCardGetEntries->description = 'Get all time entries for a time card';
        $auth->add($timeCardGetEntries);
		
		//Time Entry permissions/////////////////////////////////////////////////////////////////
        // add "timeEntryView" permission
        $timeEntryView = $auth->createPermission('timeEntryView');
        $timeEntryView->description = 'View a time entry';
        $auth->add($timeEntryView);
		
		// add "timeEntryCreate" permission
        $timeEntryCreate = $auth->createPermission('timeEntryCreate');
        $timeEntryCreate->description = 'Create a time entry';
        $auth->add($timeEntryCreate);
		
		// add "timeEntryDeactivate" permission
        $timeEntryDeactivate = $auth->createPermission('timeEntryDeactivate');
        $timeEntryDeactivate->description = 'Deactivate a time entry';
        $auth->add($timeEntryDeactivate);
		
		//User permissions/////////////////////////////////////////////////////////////////
        // add "userGetActive" permission
        $userGetActive = $auth->createPermission('userGetActive');
        $userGetActive->description = 'Get all active users';
        $auth->add($userGetActive);
		
		// add "userView" permission
        $userView = $auth->createPermission('userView');
        $userView->description = 'View a user';
        $auth->add($userView);
		
		// add "userCreate" permission
        $userCreate = $auth->createPermission('userCreate');
        $userCreate->description = 'Create a user';
        $auth->add($userCreate);
		
		// add "userCreateAdmin" permission
        $userCreateAdmin = $auth->createPermission('userCreateAdmin');
        $userCreateAdmin->description = 'Create a user of role type admin';
        $auth->add($userCreateAdmin);

        // add "userUpdate" permission
        $userUpdate = $auth->createPermission('userUpdate');
        $userUpdate->description = 'Update user';
        $auth->add($userUpdate);
		
		// add "userUpdateTechnician" permission
        $userUpdateTechnician = $auth->createPermission('userUpdateTechnician');
        $userUpdateTechnician->description = 'Update user of role type technician';
        $auth->add($userUpdateTechnician);
		
		// add "userUpdateEngineer" permission
        $userUpdateEngineer = $auth->createPermission('userUpdateEngineer');
        $userUpdateEngineer->description = 'Update user of role type engineer';
        $auth->add($userUpdateEngineer);
		
		// add "userUpdateSupervisor" permission
        $userUpdateSupervisor = $auth->createPermission('userUpdateSupervisor');
        $userUpdateSupervisor->description = 'Update user of role type supervisor';
        $auth->add($userUpdateSupervisor);
		
		// add "userUpdateProjectManager" permission
        $userUpdateProjectManager = $auth->createPermission('userUpdateProjectManager');
        $userUpdateProjectManager->description = 'Update user of role type project manager';
        $auth->add($userUpdateProjectManager);
		
		// add "userUpdateAdmin" permission
        $userUpdateAdmin = $auth->createPermission('userUpdateAdmin');
        $userUpdateAdmin->description = 'Update user of role type admin';
        $auth->add($userUpdateAdmin);
		
		// add "userDeactivate" permission
        $userDeactivate = $auth->createPermission('userDeactivate');
        $userDeactivate->description = 'Deactivate user';
        $auth->add($userDeactivate);
		
		// add "userGetDropdown" permission
        $userGetDropdown = $auth->createPermission('userGetDropdown');
        $userGetDropdown->description = 'Get an associative array of user id/name pairs';
        $auth->add($userGetDropdown);
		
		// add "userGetMe" permission
        $userGetMe = $auth->createPermission('userGetMe');
        $userGetMe->description = 'Get equipment and project data for a user';
        $auth->add($userGetMe);

		////// Module Menu Premissions //////
		
		$viewAdministrationMenu = $auth->createPermission('viewAdministrationMenu');
        $viewAdministrationMenu->description = 'View Administration Menu';
        $auth->add($viewAdministrationMenu);
		
		$viewDashboardMenu = $auth->createPermission('viewDashboardMenu');
        $viewDashboardMenu->description = 'View Dashboard Menu';
        $auth->add($viewDashboardMenu);
		
		$viewDispatchMenu = $auth->createPermission('viewDispatchMenu');
        $viewDispatchMenu->description = 'View Dispatch Menu';
        $auth->add($viewDispatchMenu);
		
		$viewReportsMenu = $auth->createPermission('viewReportsMenu');
        $viewReportsMenu->description = 'View Reports Menu';
        $auth->add($viewReportsMenu);
		
		$viewHomeMenu = $auth->createPermission('viewHomeMenu');
        $viewHomeMenu->description = 'View Home Menu';
        $auth->add($viewHomeMenu);
		
        ////// Module Sub Menu Permissions //////


        $viewClientMgmt = $auth->createPermission('viewClientMgmt');
        $viewClientMgmt->description = 'View client management menu item';
        $auth->add($viewClientMgmt);

        $viewProjectMgmt = $auth->createPermission('viewProjectMgmt');
        $viewProjectMgmt->description = 'View project management  menu item';
        $auth->add($viewProjectMgmt);

        $viewUserMgmt = $auth->createPermission('viewUserMgmt');
        $viewUserMgmt->description = 'View user management menu item';
        $auth->add($viewUserMgmt);

        $viewEquipmentMgmt = $auth->createPermission('viewEquipmentMgmt');
        $viewEquipmentMgmt->description = 'View equipment management menu item';
        $auth->add($viewEquipmentMgmt);

        $viewTimeCardMgmt = $auth->createPermission('viewTimeCardMgmt');
        $viewTimeCardMgmt->description = 'View time card management menu item';
        $auth->add($viewTimeCardMgmt);

        $viewMileageCardMgmt = $auth->createPermission('viewMileageCardMgmt');
        $viewMileageCardMgmt->description = 'View mileage card management menu item';
        $auth->add($viewMileageCardMgmt);

        $viewTracker = $auth->createPermission('viewTracker');
        $viewTracker->description = 'View tracker menu item';
        $auth->add($viewTracker);
        
        $viewLeakLogMgmt = $auth->createPermission('viewLeakLogMgmt');
        $viewLeakLogMgmt->description = 'View leak log management menu item';
        $auth->add($viewLeakLogMgmt);

        $viewLeakLogSearch = $auth->createPermission('viewLeakLogSearch');
        $viewLeakLogSearch->description = 'View leak log search menu item';
        $auth->add($viewLeakLogSearch);

        $viewMapStampMgmt = $auth->createPermission('viewMapStampMgmt');
        $viewMapStampMgmt->description = 'View map stamp management menu item';
        $auth->add($viewMapStampMgmt);

        $viewMapStampDetail = $auth->createPermission('viewMapStampDetail');
        $viewMapStampDetail->description = 'View map stamp detail menu item';
        $auth->add($viewMapStampDetail);

        $viewAOC = $auth->createPermission('viewAOC');
        $viewAOC->description = 'View AOC menu item';
        $auth->add($viewAOC);

        $viewDispatch = $auth->createPermission('viewDispatch');
        $viewDispatch->description = 'View dispatch menu item';
        $auth->add($viewDispatch);

        $viewAssigned = $auth->createPermission('viewAssigned');
        $viewAssigned->description = 'View assigned menu item';
        $auth->add($viewAssigned);
        

		
		// add roles and children/////////////////////////////////////////////////////////////////
		// add "Technician" role and give this role CRUD permissions
		$technician = $auth->createRole('Technician');
		$auth->add($technician);
		//add permissions
		$auth->addChild($technician, $activityCreate);
		$auth->addChild($technician, $equipmentCalibrationCreate);
		$auth->addChild($technician, $mileageCardGetCard);
		$auth->addChild($technician, $timeCardGetCard);
		$auth->addChild($technician, $userGetMe);
		
		// add "Engineer" role and give this role CRUD permissions
		$engineer = $auth->createRole('Engineer');
		$auth->add($engineer);
		//add permissions
		$auth->addChild($engineer, $clientGetDropdown);
		$auth->addChild($engineer, $equipmentConditionGetDropdown);
		$auth->addChild($engineer, $equipmentView);
		$auth->addChild($engineer, $equipmentCreate);
		$auth->addChild($engineer, $equipmentUpdate);
		$auth->addChild($engineer, $equipmentDelete);
		$auth->addChild($engineer, $getAllEquipment);
		$auth->addChild($engineer, $acceptEquipment);
		$auth->addChild($engineer, $equipmentStatusGetDropdown);
		$auth->addChild($engineer, $equipmentTypeGetDropdown);
		$auth->addChild($engineer, $notificationsGet);
		$auth->addChild($engineer, $projectGetDropdown);
		$auth->addChild($engineer, $userGetDropdown);
		$auth->addChild($engineer, $userGetMe);
		//menu permissions
		$auth->addChild($engineer, $viewAdministrationMenu);
		$auth->addChild($engineer, $viewHomeMenu);
		// sub menu permissions
		$auth->addChild($engineer, $viewEquipmentMgmt);

        // add "supervisor" role and give this role CRUD permissions
        $supervisor = $auth->createRole('Supervisor');
        $auth->add($supervisor);
		//add child roles
		$auth->addChild($supervisor, $technician);
		//add permissions
		$auth->addChild($supervisor, $activityCodeGetDropdown);
		$auth->addChild($supervisor, $appRoleGetDropdown);
		$auth->addChild($supervisor, $clientGetDropdown);
		$auth->addChild($supervisor, $employeeTypeGetDropdown);
		$auth->addChild($supervisor, $equipmentConditionGetDropdown);
		$auth->addChild($supervisor, $equipmentView);
		$auth->addChild($supervisor, $equipmentUpdate);
		$auth->addChild($supervisor, $equipmentDelete);
		$auth->addChild($supervisor, $getOwnEquipment);
		$auth->addChild($supervisor, $acceptEquipment);
		$auth->addChild($supervisor, $equipmentStatusGetDropdown);
		$auth->addChild($supervisor, $equipmentTypeGetDropdown);
		$auth->addChild($supervisor, $mileageCardView);
		$auth->addChild($supervisor, $mileageCardApprove);
		$auth->addChild($supervisor, $mileageCardGetEntries);
		$auth->addChild($supervisor, $mileageCardGetOwnCards);
		$auth->addChild($supervisor, $mileageEntryView);
		$auth->addChild($supervisor, $mileageEntryCreate);
		$auth->addChild($supervisor, $mileageEntryDeactivate);
		$auth->addChild($supervisor, $notificationsGet);
		$auth->addChild($supervisor, $payCodeGetDropdown);
		$auth->addChild($supervisor, $projectView);
		$auth->addChild($supervisor, $projectGetOwnProjects);
		$auth->addChild($supervisor, $projectGetDropdown);
		$auth->addChild($supervisor, $projectGetUserRelationships);
		$auth->addChild($supervisor, $projectAddRemoveUsers);
		$auth->addChild($supervisor, $timeCardView);
		$auth->addChild($supervisor, $timeCardApproveCards);
		$auth->addChild($supervisor, $timeCardGetEntries);
		$auth->addChild($supervisor, $timeCardGetOwnCards);
		$auth->addChild($supervisor, $timeEntryView);
		$auth->addChild($supervisor, $timeEntryCreate);
		$auth->addChild($supervisor, $timeEntryDeactivate);
		$auth->addChild($supervisor, $userCreate);
		$auth->addChild($supervisor, $userUpdate);
		$auth->addChild($supervisor, $userUpdateTechnician);
		$auth->addChild($supervisor, $userUpdateSupervisor);
		$auth->addChild($supervisor, $userUpdateEngineer);
		$auth->addChild($supervisor, $userView);
		$auth->addChild($supervisor, $userDeactivate);
		$auth->addChild($supervisor, $userGetDropdown);
		$auth->addChild($supervisor, $userGetActive);
		// menu permissions
		$auth->addChild($supervisor, $viewAdministrationMenu);
		$auth->addChild($supervisor, $viewDashboardMenu);
		$auth->addChild($supervisor, $viewHomeMenu);
		// sub menu permissions
		$auth->addChild($supervisor, $viewUserMgmt);
		$auth->addChild($supervisor, $viewEquipmentMgmt);
		$auth->addChild($supervisor, $viewTimeCardMgmt);
		$auth->addChild($supervisor, $viewMileageCardMgmt);
		$auth->addChild($supervisor, $viewTracker);

        // add "projectManager" role and give this role the permissions of the "supervisor"
        $projectManager = $auth->createRole('ProjectManager');
        $auth->add($projectManager);
		//add child roles
        $auth->addChild($projectManager, $supervisor);
		$auth->addChild($projectManager, $userUpdateProjectManager);
		
		// add "admin" role and give this role the permissions of the "projectManager" and "engineer"
		$admin = $auth->createRole('Admin');
        $auth->add($admin);
		// add child roles
		$auth->addChild($admin, $engineer);
        $auth->addChild($admin, $projectManager);
		//add permissions
		$auth->addChild($admin, $activityView);
		$auth->addChild($admin, $clientAccountsGetDropdown);
		$auth->addChild($admin, $clientView);
		$auth->addChild($admin, $clientCreate);
		$auth->addChild($admin, $clientUpdate);
		$auth->addChild($admin, $clientDeactivate);
		$auth->addChild($admin, $clientGetAll);
		$auth->addChild($admin, $projectGetAll);
		$auth->addChild($admin, $projectCreate);
		$auth->addChild($admin, $projectUpdate);
		$auth->addChild($admin, $projectAddRemoveModules);
		$auth->addChild($admin, $stateCodeGetDropdown);
		$auth->addChild($admin, $mileageCardGetAllCards);
		$auth->addChild($admin, $timeCardGetAllCards);
		$auth->addChild($admin, $userCreateAdmin);
		$auth->addChild($admin, $userUpdateAdmin);
		// menu permissions
		//$auth->addChild($admin, $viewDispatchMenu);
		//$auth->addChild($admin, $viewReportsMenu);
		// sub menu permissions
		$auth->addChild($admin, $viewClientMgmt);
		$auth->addChild($admin, $viewProjectMgmt);
		//$auth->addChild($supervisor, $viewLeakLogMgmt);
		//$auth->addChild($supervisor, $viewLeakLogDetail);
		//$auth->addChild($supervisor, $viewMapStampMgmt);
		//$auth->addChild($supervisor, $viewMapStampDetail);
		//$auth->addChild($supervisor, $viewAOC);
		//$auth->addChild($supervisor, $viewDispatch);
		//$auth->addChild($supervisor, $viewAssigned);
		
		//assign roles to existing users////////////////////////////////////////
		$users = SCUser::find()
				->all();
		
		$userSize = count($users);
		
		//assign roles to users already in the system
		for($i = 0; $i < $userSize; $i++)
		{
			if($userRole = $auth->getRole($users[$i]["UserAppRoleType"]))
			{
				$auth->assign($userRole, $users[$i]["UserID"]);
			}
		}
    }
}
