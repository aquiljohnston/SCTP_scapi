<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\v2\models\SCUser;
use app\modules\v2\rbac\ScDbManager;

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
		echo "Generating RBAC settings for " . $client . ".\n";
		SCUser::setClient($client);
		$db = SCUser::getDb();
		$auth = new ScDbManager($db);
		$permissionsArray = array();
		
		try{
			echo "Clearing Auth Tables.\n";
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
			echo "Auth Tables Cleared Successfully.\n";
		} catch (Exception $e) {
			//roll back changes on failure
			$transaction->rollBack();
			echo "Auth Tables Failed to Clear.\n";
		}
			
		echo "Creating Permissions Array.\n";	
		//Activity Code permissions/////////////////////////////////////////////////////////////////
		//add "activityCodeGetDropdown" permission
		$activityCodeGetDropdown = $auth->createPermission('activityCodeGetDropdown');
		$activityCodeGetDropdown->description = 'Get an associative array of Activity Codes';
		$permissionsArray[] = $activityCodeGetDropdown;
		
		//Activity permissions/////////////////////////////////////////////////////////////////
		//add "activityView" permission
		$activityView = $auth->createPermission('activityView');
		$activityView->description = 'View an activity';
		$permissionsArray[] = $activityView;
		
		// "activityCreate" permission
		$activityCreate = $auth->createPermission('activityCreate');
		$activityCreate->description = 'Create an activity';
		$permissionsArray[] = $activityCreate;
		
		//App Role permissions/////////////////////////////////////////////////////////////////
		//add "appRoleGetDropdown" permission
		$appRoleGetDropdown = $auth->createPermission('appRoleGetDropdown');
		$appRoleGetDropdown->description = 'Get an associative array of App Roles';
		$permissionsArray[] = $appRoleGetDropdown;
		
		//Client Accounts permissions/////////////////////////////////////////////////////////////////
		//add "clientAccountsGetDropdown" permission
		$clientAccountsGetDropdown = $auth->createPermission('clientAccountsGetDropdown');
		$clientAccountsGetDropdown->description = 'Get an associative array of Client Accounts';
		$permissionsArray[] = $clientAccountsGetDropdown;
		
		//Client permissions/////////////////////////////////////////////////////////////////
        // add "clientGetAll" permission
        $clientGetAll = $auth->createPermission('clientGetAll');
        $clientGetAll->description = 'Get an array of all clients';
        $permissionsArray[] = $clientGetAll;
		
		// add "clientView" permission
        $clientView = $auth->createPermission('clientView');
        $clientView->description = 'View a client';
        $permissionsArray[] = $clientView;
		
		// add "clientCreate" permission
        $clientCreate = $auth->createPermission('clientCreate');
        $clientCreate->description = 'Create a client';
        $permissionsArray[] = $clientCreate;

        // add "clientUpdate" permission
        $clientUpdate = $auth->createPermission('clientUpdate');
        $clientUpdate->description = 'Update client';
        $permissionsArray[] = $clientUpdate;

        // add "clientDeactivate" permission
        $clientDeactivate = $auth->createPermission('clientDeactivate');
        $clientDeactivate->description = 'Deactivate client';
        $permissionsArray[] = $clientDeactivate;
		
		// add "clientGetDropdown" permission
        $clientGetDropdown = $auth->createPermission('clientGetDropdown');
        $clientGetDropdown->description = 'Get an associative array of client ID/name pairs';
        $permissionsArray[] = $clientGetDropdown;
		
		//Employee Type permissions/////////////////////////////////////////////////////////////////
		//add "employeeTypeGetDropdown" permission
		$employeeTypeGetDropdown = $auth->createPermission('employeeTypeGetDropdown');
		$employeeTypeGetDropdown->description = 'Get an associative array of employee types';
		$permissionsArray[] = $employeeTypeGetDropdown;
		
		//Equipment Calibration permissions/////////////////////////////////////////////////////////////////
		//add "equipmentCalibrationCreate" permission
		$equipmentCalibrationCreate = $auth->createPermission('equipmentCalibrationCreate');
		$equipmentCalibrationCreate->description = 'Creates a new equipment calibration record';
		$permissionsArray[] = $equipmentCalibrationCreate;
		
		//Equipment Condition permissions/////////////////////////////////////////////////////////////////
		//add "equipmentConditionGetDropdown" permission
		$equipmentConditionGetDropdown = $auth->createPermission('equipmentConditionGetDropdown');
		$equipmentConditionGetDropdown->description = 'Get an associative array of equipment conditions';
		$permissionsArray[] = $equipmentConditionGetDropdown;
		
		//Equipment permissions/////////////////////////////////////////////////////////////////
        // add "getOwnEquipment" permission
        $getOwnEquipment = $auth->createPermission('getOwnEquipment');
        $getOwnEquipment->description = 'Get equipment for associated projects';
        $permissionsArray[] = $getOwnEquipment;
		
		// add "getAllEquipment" permission
        $getAllEquipment = $auth->createPermission('getAllEquipment');
        $getAllEquipment->description = 'Get all equipment';
        $permissionsArray[] = $getAllEquipment;
		
		// add "equipmentView" permission
        $equipmentView = $auth->createPermission('equipmentView');
        $equipmentView->description = 'View equipment';
        $permissionsArray[] = $equipmentView;
		
		// add "equipmentCreate" permission
        $equipmentCreate = $auth->createPermission('equipmentCreate');
        $equipmentCreate->description = 'Create equipment';
        $auth->add($equipmentCreate);

        // add "equipmentUpdate" permission
        $equipmentUpdate = $auth->createPermission('equipmentUpdate');
        $equipmentUpdate->description = 'Update equipment';
        $permissionsArray[] = $equipmentUpdate;
		
		// add "equipmentDelete" permission
        $equipmentDelete = $auth->createPermission('equipmentDelete');
        $equipmentDelete->description = 'Delete equipment';
        $permissionsArray[] = $equipmentDelete;
		
		// add "acceptEquipment" permission
        $acceptEquipment = $auth->createPermission('acceptEquipment');
        $acceptEquipment->description = 'Accept equipment';
        $permissionsArray[] = $acceptEquipment;
		
		//Equipment Status permissions/////////////////////////////////////////////////////////////////
		//add "equipmentStatusGetDropdown" permission
		$equipmentStatusGetDropdown = $auth->createPermission('equipmentStatusGetDropdown');
		$equipmentStatusGetDropdown->description = 'Get an associative array of equipment status';
		$permissionsArray[] = $equipmentStatusGetDropdown;
		
		//Equipment Type permissions/////////////////////////////////////////////////////////////////
		//add "equipmentTypeGetDropdown" permission
		$equipmentTypeGetDropdown = $auth->createPermission('equipmentTypeGetDropdown');
		$equipmentTypeGetDropdown->description = 'Get an associative array of equipment types';
		$permissionsArray[] = $equipmentTypeGetDropdown;
		
		//Mileage Card permissions/////////////////////////////////////////////////////////////////
        // add "mileageCardGetOwnCards" permission
        $mileageCardGetOwnCards = $auth->createPermission('mileageCardGetOwnCards');
        $mileageCardGetOwnCards->description = 'Get an array of mileage cards for associated projects';
        $permissionsArray[] = $mileageCardGetOwnCards;
		
		// add "mileageCardGetAllCards" permission
        $mileageCardGetAllCards = $auth->createPermission('mileageCardGetAllCards');
        $mileageCardGetAllCards->description = 'Get an array of all mileage cards';
        $permissionsArray[] = $mileageCardGetAllCards;
		
		// add "mileageCardView" permission
        $mileageCardView = $auth->createPermission('mileageCardView');
        $mileageCardView->description = 'View a mileage card';
        $permissionsArray[] = $mileageCardView;
		
		// add "mileageCardGetCard" permission
        $mileageCardGetCard = $auth->createPermission('mileageCardGetCard');
        $mileageCardGetCard->description = 'Get a mileage card for a user';
        $permissionsArray[] = $mileageCardGetCard;

        // add "mileageCardGetEntries" permission
        $mileageCardGetEntries = $auth->createPermission('mileageCardGetEntries');
        $mileageCardGetEntries->description = 'Get all mileage entries for a mileage card';
        $permissionsArray[] = $mileageCardGetEntries;
		
		// add "mileageCardApprove" permission
        $mileageCardApprove = $auth->createPermission('mileageCardApprove');
        $mileageCardApprove->description = 'Approve a mileage card';
        $permissionsArray[] = $mileageCardApprove;
		
		//Mileage Entry permissions/////////////////////////////////////////////////////////////////
        // add "mileageEntryView" permission
        $mileageEntryView = $auth->createPermission('mileageEntryView');
        $mileageEntryView->description = 'View a mileage entry';
        $permissionsArray[] = $mileageEntryView;
		
		// add "mileageEntryCreate" permission
        $mileageEntryCreate = $auth->createPermission('mileageEntryCreate');
        $mileageEntryCreate->description = 'Create a mileage entry';
        $permissionsArray[] = $mileageEntryCreate;
		
		// add "mileageEntryDeactivate" permission
        $mileageEntryDeactivate = $auth->createPermission('mileageEntryDeactivate');
        $mileageEntryDeactivate->description = 'Deactivate a mileage entry';
        $permissionsArray[] = $mileageEntryDeactivate;
		
		//Notifications permissions/////////////////////////////////////////////////////////////////
        // add "notificationsGet" permission
        $notificationsGet = $auth->createPermission('notificationsGet');
        $notificationsGet->description = 'Get notifications';
        $permissionsArray[] = $notificationsGet;
		
		//Pay Code permissions/////////////////////////////////////////////////////////////////
        // add "payCodeGetDropdown" permission
        $payCodeGetDropdown = $auth->createPermission('payCodeGetDropdown');
        $payCodeGetDropdown->description = 'Get an associative array of pay codes';
        $permissionsArray[] = $payCodeGetDropdown;
		
		//Project permissions/////////////////////////////////////////////////////////////////
        // add "projectGetAll" permission
        $projectGetAll = $auth->createPermission('projectGetAll');
        $projectGetAll->description = 'Get an array of all projects';
        $permissionsArray[] = $projectGetAll;
		
		// add "projectView" permission
        $projectView = $auth->createPermission('projectView');
        $projectView->description = 'View a project';
        $permissionsArray[] = $projectView;
		
		// add "projectCreate" permission
        $projectCreate = $auth->createPermission('projectCreate');
        $projectCreate->description = 'Create a project';
        $permissionsArray[] = $projectCreate;

        // add "projectUpdate" permission
        $projectUpdate = $auth->createPermission('projectUpdate');
        $projectUpdate->description = 'Update project';
        $permissionsArray[] = $projectUpdate;
		
		// add "projectGetOwnProjects" permission
        $projectGetOwnProjects = $auth->createPermission('projectGetOwnProjects');
        $projectGetOwnProjects->description = 'Get all projects that a user is associated with';
        $permissionsArray[] = $projectGetOwnProjects;
		
		// add "projectGetDropdown" permission
        $projectGetDropdown = $auth->createPermission('projectGetDropdown');
        $projectGetDropdown->description = 'Get an associative array of project name/id pairs';
        $permissionsArray[] = $projectGetDropdown;
		
		// add "projectGetUserRelationships" permission
        $projectGetUserRelationships = $auth->createPermission('projectGetUserRelationships');
        $projectGetUserRelationships->description = 'Get two arrays one of users associated with a project and one of all other users';
        $permissionsArray[] = $projectGetUserRelationships;
		
		// add "projectAddRemoveUsers" permission
        $projectAddRemoveUsers = $auth->createPermission('projectAddRemoveUsers');
        $projectAddRemoveUsers->description = 'Add or remove users from a project';
        $permissionsArray[] = $projectAddRemoveUsers;
		
		// add "projectAddRemoveModules" permission
		$projectAddRemoveModules = $auth->createPermission('projectAddRemoveModules');
        $projectAddRemoveModules->description = 'Add or remove modules from a project';
        $permissionsArray[] = $projectAddRemoveModules;
		
		//State Code permissions/////////////////////////////////////////////////////////////////
        // add "stateCodeGetDropdown" permission
        $stateCodeGetDropdown = $auth->createPermission('stateCodeGetDropdown');
        $stateCodeGetDropdown->description = 'Get an associative array of state codes';
        $permissionsArray[] = $stateCodeGetDropdown;
		
		//Time Card permissions/////////////////////////////////////////////////////////////////
        // add "timeCardGetOwnCards" permission
        $timeCardGetOwnCards = $auth->createPermission('timeCardGetOwnCards');
        $timeCardGetOwnCards->description = 'Get an array of multiple time cards for associated projects';
        $permissionsArray[] = $timeCardGetOwnCards;
		
		// add "timeCardGetAllCards" permission
        $timeCardGetAllCards = $auth->createPermission('timeCardGetAllCards');
        $timeCardGetAllCards->description = 'Get an array of all time cards';
        $permissionsArray[] = $timeCardGetAllCards;
		
		// add "timeCardView" permission
        $timeCardView = $auth->createPermission('timeCardView');
        $timeCardView->description = 'View a time card';
        $permissionsArray[] = $timeCardView;
		
		// add "timeCardApproveCards" permission
        $timeCardApproveCards = $auth->createPermission('timeCardApproveCards');
        $timeCardApproveCards->description = 'Approve time cards';
        $permissionsArray[] = $timeCardApproveCards;

        // add "timeCardGetCard" permission
        $timeCardGetCard = $auth->createPermission('timeCardGetCard');
        $timeCardGetCard->description = 'Get a users time card';
        $permissionsArray[] = $timeCardGetCard;
		
		// add "timeCardGetEntries" permission
        $timeCardGetEntries = $auth->createPermission('timeCardGetEntries');
        $timeCardGetEntries->description = 'Get all time entries for a time card';
        $permissionsArray[] = $timeCardGetEntries;
		
		//add "timeCardPmSubmit" permission
		$timeCardPmSubmit = $auth->createPermission('timeCardPmSubmit');
        $timeCardPmSubmit->description = 'Submit time cards to accounting.';
        $permissionsArray[] = $timeCardPmSubmit;
		
		//Time Entry permissions/////////////////////////////////////////////////////////////////
        // add "timeEntryView" permission
        $timeEntryView = $auth->createPermission('timeEntryView');
        $timeEntryView->description = 'View a time entry';
        $permissionsArray[] = $timeEntryView;
		
		// add "timeEntryCreate" permission
        $timeEntryCreate = $auth->createPermission('timeEntryCreate');
        $timeEntryCreate->description = 'Create a time entry';
        $permissionsArray[] = $timeEntryCreate;
		
		// add "timeEntryDeactivate" permission
        $timeEntryDeactivate = $auth->createPermission('timeEntryDeactivate');
        $timeEntryDeactivate->description = 'Deactivate a time entry';
        $permissionsArray[] = $timeEntryDeactivate;
		
		//User permissions/////////////////////////////////////////////////////////////////
        // add "userGetActive" permission
        $userGetActive = $auth->createPermission('userGetActive');
        $userGetActive->description = 'Get all active users';
        $permissionsArray[] = $userGetActive;
		
		// add "userView" permission
        $userView = $auth->createPermission('userView');
        $userView->description = 'View a user';
        $permissionsArray[] = $userView;
		
		//User Create Permissions
		// add "userCreate" permission
        $userCreate = $auth->createPermission('userCreate');
        $userCreate->description = 'Create a user';
        $permissionsArray[] = $userCreate;
		
		// add "userCreateTechnician" permission
        $userCreateTechnician = $auth->createPermission('userCreateTechnician');
        $userCreateTechnician->description = 'Create user of role type technician';
        $permissionsArray[] = $userCreateTechnician;
		
		// add "userCreateSupervisor" permission
        $userCreateSupervisor = $auth->createPermission('userCreateSupervisor');
        $userCreateSupervisor->description = 'Create user of role type supervisor';
        $permissionsArray[] = $userCreateSupervisor;
		
		// add "userCreateProjectManager" permission
        $userCreateProjectManager = $auth->createPermission('userCreateProjectManager');
        $userCreateProjectManager->description = 'Create user of role type project manager';
        $permissionsArray[] = $userCreateProjectManager;
		
		// add "userCreateAnalyst" permission
        $userCreateAnalyst = $auth->createPermission('userCreateAnalyst');
        $userCreateAnalyst->description = 'Create user of role type analyst';
        $permissionsArray[] = $userCreateAnalyst;
		
		// add "userCreateAccountant" permission
        $userCreateAccountant = $auth->createPermission('userCreateAccountant');
        $userCreateAccountant->description = 'Create user of role type accountant';
        $permissionsArray[] = $userCreateAccountant;
		
		// add "userCreateAdmin" permission
        $userCreateAdmin = $auth->createPermission('userCreateAdmin');
        $userCreateAdmin->description = 'Create a user of role type admin';
        $permissionsArray[] = $userCreateAdmin;
		
		//User Update Permissions
        // add "userUpdate" permission
        $userUpdate = $auth->createPermission('userUpdate');
        $userUpdate->description = 'Update user';
        $permissionsArray[] = $userUpdate;
		
		// add "userUpdateTechnician" permission
        $userUpdateTechnician = $auth->createPermission('userUpdateTechnician');
        $userUpdateTechnician->description = 'Update user of role type technician';
        $permissionsArray[] = $userUpdateTechnician;
		
		// add "userUpdateSupervisor" permission
        $userUpdateSupervisor = $auth->createPermission('userUpdateSupervisor');
        $userUpdateSupervisor->description = 'Update user of role type supervisor';
        $permissionsArray[] = $userUpdateSupervisor;
		
		// add "userUpdateProjectManager" permission
        $userUpdateProjectManager = $auth->createPermission('userUpdateProjectManager');
        $userUpdateProjectManager->description = 'Update user of role type project manager';
        $permissionsArray[] = $userUpdateProjectManager;
		
		// add "userUpdateAnalyst" permission
        $userUpdateAnalyst = $auth->createPermission('userUpdateAnalyst');
        $userUpdateAnalyst->description = 'Update user of role type analyst';
        $permissionsArray[] = $userUpdateAnalyst;
		
		// add "userUpdateAccountant" permission
        $userUpdateAccountant = $auth->createPermission('userUpdateAccountant');
        $userUpdateAccountant->description = 'Update user of role type accountant';
        $permissionsArray[] = $userUpdateAccountant;
		
		// add "userUpdateAdmin" permission
        $userUpdateAdmin = $auth->createPermission('userUpdateAdmin');
        $userUpdateAdmin->description = 'Update user of role type admin';
        $permissionsArray[] = $userUpdateAdmin;
		
		// add "userDeactivate" permission
        $userDeactivate = $auth->createPermission('userDeactivate');
        $userDeactivate->description = 'Deactivate user';
        $permissionsArray[] = $userDeactivate;
		
		// add "userGetDropdown" permission
        $userGetDropdown = $auth->createPermission('userGetDropdown');
        $userGetDropdown->description = 'Get an associative array of user id/name pairs';
        $permissionsArray[] = $userGetDropdown;
		
		// add "userGetMe" permission
        $userGetMe = $auth->createPermission('userGetMe');
        $userGetMe->description = 'Get equipment and project data for a user';
        $permissionsArray[] = $userGetMe;

		////// Module Menu Permissions //////
		
		$viewAdministrationMenu = $auth->createPermission('viewAdministrationMenu');
        $viewAdministrationMenu->description = 'View Administration Menu';
        $permissionsArray[] = $viewAdministrationMenu;
		
		$viewDispatchMenu = $auth->createPermission('viewDispatchMenu');
        $viewDispatchMenu->description = 'View Dispatch Menu';
        $permissionsArray[] = $viewDispatchMenu;
		
		$viewReportsMenu = $auth->createPermission('viewReportsMenu');
        $viewReportsMenu->description = 'View Reports Menu';
        $permissionsArray[] = $viewReportsMenu;
		
		$viewHomeMenu = $auth->createPermission('viewHomeMenu');
        $viewHomeMenu->description = 'View Home Menu';
        $permissionsArray[] = $viewHomeMenu;
		
		$viewTrackerMenu = $auth->createPermission('viewTrackerMenu');
        $viewTrackerMenu->description = 'View Tracker Menu';
        $permissionsArray[] = $viewTrackerMenu;
		
		$viewTrainingMenu = $auth->createPermission('viewTrainingMenu');
        $viewTrainingMenu->description = 'View Training Menu';
        $permissionsArray[] = $viewTrainingMenu;
		
        ////// Module Sub Menu Permissions //////


        $viewClientMgmt = $auth->createPermission('viewClientMgmt');
        $viewClientMgmt->description = 'View client management menu item';
        $permissionsArray[] = $viewClientMgmt;

        $viewProjectMgmt = $auth->createPermission('viewProjectMgmt');
        $viewProjectMgmt->description = 'View project management  menu item';
        $permissionsArray[] = $viewProjectMgmt;

        $viewUserMgmt = $auth->createPermission('viewUserMgmt');
        $viewUserMgmt->description = 'View user management menu item';
        $permissionsArray[] = $viewUserMgmt;

        $viewEquipmentMgmt = $auth->createPermission('viewEquipmentMgmt');
        $viewEquipmentMgmt->description = 'View equipment management menu item';
        $permissionsArray[] = $viewEquipmentMgmt;

        $viewTimeCardMgmt = $auth->createPermission('viewTimeCardMgmt');
        $viewTimeCardMgmt->description = 'View time card management menu item';
        $permissionsArray[] = $viewTimeCardMgmt;

        $viewMileageCardMgmt = $auth->createPermission('viewMileageCardMgmt');
        $viewMileageCardMgmt->description = 'View mileage card management menu item';
        $permissionsArray[] = $viewMileageCardMgmt;

        $viewTracker = $auth->createPermission('viewTracker');
        $viewTracker->description = 'View tracker menu item';
        $permissionsArray[] = $viewTracker;
        
        $viewLeakLogMgmt = $auth->createPermission('viewLeakLogMgmt');
        $viewLeakLogMgmt->description = 'View leak log management menu item';
        $permissionsArray[] = $viewLeakLogMgmt;

        $viewLeakLogSearch = $auth->createPermission('viewLeakLogSearch');
        $viewLeakLogSearch->description = 'View leak log search menu item';
        $permissionsArray[] = $viewLeakLogSearch;

        $viewMapStampMgmt = $auth->createPermission('viewMapStampMgmt');
        $viewMapStampMgmt->description = 'View map stamp management menu item';
        $permissionsArray[] = $viewMapStampMgmt;

        $viewMapStampDetail = $auth->createPermission('viewMapStampDetail');
        $viewMapStampDetail->description = 'View map stamp detail menu item';
        $permissionsArray[] = $viewMapStampDetail;

        $viewAOC = $auth->createPermission('viewAOC');
        $viewAOC->description = 'View AOC menu item';
        $permissionsArray[] = $viewAOC;

        $viewDispatch = $auth->createPermission('viewDispatch');
        $viewDispatch->description = 'View dispatch menu item';
        $permissionsArray[] = $viewDispatch;

        $viewAssigned = $auth->createPermission('viewAssigned');
        $viewAssigned->description = 'View assigned menu item';
        $permissionsArray[] = $viewAssigned;
        
		$viewInspections = $auth->createPermission('viewInspections');
        $viewInspections->description = 'View Inspections';
        $permissionsArray[] = $viewInspections;
		echo "Permissions Array Created.\n";
		
		//bulk insert permissions/////////////////////////////////////////////////////////////////
		echo "Inserting Permissions into DB.\n";
		$auth->addItems($permissionsArray);
		echo "Permissions inserted into DB.\n";
		
		// add roles and children/////////////////////////////////////////////////////////////////
		echo "Creating Role Types and Adding Permissions.\n";
		//add "Technician" role and give this role CRUD permissions
		$technician = $auth->createRole('Technician');
		$auth->add($technician);
		//add children
		$auth->addChildren($technician, [
			//add permissions
			$activityCreate,
			$equipmentCalibrationCreate,
			$mileageCardGetCard,
			$timeCardGetCard,
			$userGetMe
		]);
		
		// add "Engineer" role and give this role CRUD permissions
		$engineer = $auth->createRole('Engineer');
		$auth->add($engineer);
		//add children
		$auth->addChildren($engineer,
		[
			//add permissions
			$clientGetDropdown,
			$equipmentConditionGetDropdown,
			$equipmentView,
			$equipmentCreate,
			$equipmentUpdate,
			$equipmentDelete,
			$getAllEquipment,
			$acceptEquipment,
			$equipmentStatusGetDropdown,
			$equipmentTypeGetDropdown,
			$notificationsGet,
			$projectGetDropdown,
			$userGetDropdown,
			$userGetMe,
			//menu permissions
			$viewAdministrationMenu,
			$viewHomeMenu,
			//sub menu permissions
			$viewEquipmentMgmt
		]);

        // add "supervisor" role and give this role CRUD permissions
        $supervisor = $auth->createRole('Supervisor');
        $auth->add($supervisor);
		//add children
		$auth->addChildren($supervisor, [
			//add roles
			$technician,
			//add permissions
			$activityCodeGetDropdown,
			$appRoleGetDropdown,
			$clientGetDropdown,
			$employeeTypeGetDropdown,
			$equipmentConditionGetDropdown,
			$equipmentView,
			$equipmentUpdate,
			$equipmentDelete,
			$getOwnEquipment,
			$acceptEquipment,
			$equipmentStatusGetDropdown,
			$equipmentTypeGetDropdown,
			$mileageCardView,
			$mileageCardApprove,
			$mileageCardGetEntries,
			$mileageCardGetOwnCards,
			$mileageEntryView,
			$mileageEntryCreate,
			$mileageEntryDeactivate,
			$notificationsGet,
			$payCodeGetDropdown,
			$projectView,
			$projectGetOwnProjects,
			$projectGetDropdown,
			$projectGetUserRelationships,
			$projectAddRemoveUsers,
			$timeCardView,
			$timeCardApproveCards,
			$timeCardGetEntries,
			$timeCardGetOwnCards,
			$timeEntryView,
			$timeEntryCreate,
			$timeEntryDeactivate,
			$userCreate,
			$userCreateTechnician,
			$userCreateSupervisor,
			$userUpdate,
			$userUpdateTechnician,
			$userUpdateSupervisor,
			$userView,
			$userDeactivate,
			$userGetDropdown,
			$userGetActive,
			//menu permissions
			$viewAdministrationMenu,
			$viewHomeMenu,
			$viewDispatchMenu,
			$viewReportsMenu,
			$viewTrackerMenu,
			$viewTrainingMenu,
			//sub menu permissions
			$viewUserMgmt,
			$viewEquipmentMgmt,
			$viewTimeCardMgmt,
			$viewMileageCardMgmt,
			$viewInspections
		]);

        // add "projectManager" role and give this role the permissions of the "supervisor"
        $projectManager = $auth->createRole('ProjectManager');
        $auth->add($projectManager);
		//add children
		$auth->addChildren($projectManager, [
			//add child roles
			$supervisor,
			//add permissions
			$userCreateProjectManager,
			$userUpdateProjectManager,
			$timeCardPmSubmit
		]);

		// add "admin" role and give this role the permissions of the "projectManager" and "engineer"
		$admin = $auth->createRole('Admin');
        $auth->add($admin);
		//add children
		$auth->addChildren($admin, [
			//add roles
			$engineer,
			$projectManager,
			//add permissions
			$activityView,
			$clientAccountsGetDropdown,
			$clientView,
			$clientCreate,
			$clientUpdate,
			$clientDeactivate,
			$clientGetAll,
			$projectGetAll,
			$projectCreate,
			$projectUpdate,
			$projectAddRemoveModules,
			$stateCodeGetDropdown,
			$mileageCardGetAllCards,
			$timeCardGetAllCards,
			$userCreateAdmin,
			$userCreateAccountant,
			$userCreateAnalyst,
			$userUpdateAdmin,
			$userUpdateAccountant,
			$userUpdateAnalyst,
			//sub menu permissions
			$viewClientMgmt,
			$viewProjectMgmt,
			$viewDispatch,
			$viewAssigned			
		]);

		// add "accountant" role and give this role CRUD permissions
        $accountant = $auth->createRole('Accountant');
        $auth->add($accountant);
		//add children
		$auth->addChildren($accountant, [
			//add roles
			$technician,
			//add permissions
			$mileageCardView,
			$mileageCardApprove,
			$mileageCardGetEntries,
			$mileageCardGetOwnCards,
			$mileageEntryView,
			$mileageEntryCreate,
			$mileageEntryDeactivate,
			$notificationsGet,
			$payCodeGetDropdown,
			$timeCardView,
			$timeCardApproveCards,
			$timeCardGetEntries,
			$timeCardGetOwnCards,
			$timeEntryView,
			$timeEntryCreate,
			$timeEntryDeactivate,
			$mileageCardGetAllCards,
			$projectGetOwnProjects,
			$projectGetUserRelationships,
			//menu permissions
			$viewAdministrationMenu,
			$viewHomeMenu,
			$viewReportsMenu,
			//sub menu permissions
			$viewTimeCardMgmt,
			$viewMileageCardMgmt
		]);

		// add "analyst" role and give this role CRUD permissions
        $analyst = $auth->createRole('Analyst');
        $auth->add($analyst);
		//add children
		$auth->addChildren($analyst, [
			//add roles
			$technician,
			//add permissions
			$notificationsGet,
			$payCodeGetDropdown,
			$projectGetOwnProjects,
			$projectGetUserRelationships,
			//menu permissions
			$viewHomeMenu,
			$viewReportsMenu
		]);
		echo "Role Types Created and Permissions Assigned.\n";

		
		//assign roles to existing users////////////////////////////////////////
		$users = SCUser::find()
				->where(['UserActiveFlag' => 1])
				->all();
		
		$userSize = count($users);
		//get vals for progress check
		$userSize25 = intval(($userSize*.25));
		$userSize50 = intval(($userSize*.50));
		$userSize75 = intval(($userSize*.75));
		$userRoleTypes = $auth->getRoles();
		$bulkUserInsertArray = array();
		
		echo "Preparing Bulk Assignment of Roles to Users.\n";
		//assign roles to users already in the system
		for($i = 0; $i < $userSize; $i++)
		{
			//switch to log percentage progress. This may not be necessary with new performance enhancements.
			switch($i)
			{
				case $userSize25:
					echo "Preparing Bulk Assignment of Roles to Users 25% complete.\n";
					break;
				case $userSize50:
					echo "Preparing Bulk Assignment of Roles to Users 50% complete.\n";
					break;
				case $userSize75:
					echo "Preparing Bulk Assignment of Roles to Users 75% complete.\n";
					break;
			}
			$userRole = $users[$i]['UserAppRoleType'];
			if(array_key_exists($userRole, $userRoleTypes))
			{
				$bulkUserInsertArray[] = [
					'user_id' => $users[$i]['UserID'],
					'item_name' => $userRole,
					'created_at' => time()
				];
			}
		}
		echo "Execute Bulk Assignment of Roles to Users.\n";
		$auth->bulkAssign($bulkUserInsertArray);
		echo "Users Roles Assigned.\n";
		echo "RBAC Geneartion Completed for " . $client . ".\n";
    }
}
