<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\v2\constants\Constants;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;

/**
* This Class establishes the rules of the RBAC system for the API
* Permissions are created and assigned and the role hierarchy is established
*/
class RbacController extends Controller
{
	//db connection based on active client
	private $db;
	//auth manager based on input param
	private $auth;
	//client based on input param
	private $client;
	//array of all permissions to be inserted
	private $permissionArray = [];
	//associative array of all permission associations to roles
	private $permissionAssociationArray = [];
	
	/**
	* Removes all RBAC settings that are currently in place and rebuilds the rule set
	* Creates Permissions for routes
	* Creates Roles
	* Creates Child Hierarchy for Roles and Permissions
	* Loops existing users to get previously assigned roles and reassign them
	*/
    public function actionInit($input)
    {
		$this->client = $input;
		echo "Generating RBAC settings for " . $this->client . ".\n";
		BaseActiveRecord::setClient($this->client);
		$this->db = BaseActiveRecord::getDb();
		$authController = BaseActiveRecord::getAuthManager($this->client);
		$this->auth = new $authController($this->db);
		
		//CALL functions instantiated below
		//clear permission tables
		$this->clearPermissions();
		//generate array of all permissions to add
		$this->createBasePermissions();
		//if client is not scct add survey permissions, may be able to alter this based on project type in the future
		if(!BaseActiveController::isSCCT($this->client)){
			$this->createSurveyPermissions();
		}
		//bulk insert permissions from permissionArray
		echo "Inserting Permissions Into DB.\n";
		$this->auth->addItems($this->permissionArray);
		echo "Permissions Inserted Into DB.\n";
		//create roles, and add permissions to them based on permissionAssociationArray
		$this->assignPermissions();
		//reassign user roles based on user records
		$this->reAssignUserRoles();
		
		echo "RBAC Geneartion Completed for " . $this->client . ".\n";
    }
	
	private function clearPermissions()
	{
		//get shcema prefix for base comet tracker tables
		$schemaPrefix = '';
		if(BaseActiveController::isSCCT($this->client))
		{
			$schemaPrefix = 'rbac.';
		}
		try{
			echo "Clearing Auth Tables.\n";
			//reset all
			//create connection
			$connection = $this->db;
			//start transaction
			$transaction = $connection-> beginTransaction();
			//create commands
			$deleteAssignmentCommand = $connection->createCommand('DELETE FROM ' . $schemaPrefix . 'auth_assignment');
			$deleteItemChildCommand = $connection->createCommand('DELETE FROM ' . $schemaPrefix . 'auth_item_child');
			$deleteItemCommand = $connection->createCommand('DELETE FROM ' . $schemaPrefix . 'auth_item');
			$deleteRuleCommand = $connection->createCommand('DELETE FROM ' . $schemaPrefix . 'auth_rule');
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
	}
	
	private function createBasePermissions()
	{
		echo "Creating Base Permissions Array.\n";		
		//Activity permissions/////////////////////////////////////////////////////////////////

		$activityView = $this->auth->createPermission('activityView');
		$activityView->description = 'View an activity';
		$this->permissionAssociationArray['Admin'][] = $activityView;
		$this->permissionArray[] = $activityView;
		
		$activityCreate = $this->auth->createPermission('activityCreate');
		$activityCreate->description = 'Create an activity';
		$this->permissionAssociationArray['Technician'][] = $activityCreate;
		$this->permissionArray[] = $activityCreate;
		
		//App Role permissions/////////////////////////////////////////////////////////////////

		$appRoleGetDropdown = $this->auth->createPermission('appRoleGetDropdown');
		$appRoleGetDropdown->description = 'Get an associative array of App Roles';
		$this->permissionAssociationArray['Supervisor'][] = $appRoleGetDropdown;
		$this->permissionArray[] = $appRoleGetDropdown;
		
		//Client Accounts permissions/////////////////////////////////////////////////////////////////

		$clientAccountsGetDropdown = $this->auth->createPermission('clientAccountsGetDropdown');
		$clientAccountsGetDropdown->description = 'Get an associative array of Client Accounts';
		$this->permissionAssociationArray['Admin'][] = $clientAccountsGetDropdown;
		$this->permissionArray[] = $clientAccountsGetDropdown;
		
		//Client permissions/////////////////////////////////////////////////////////////////

        $clientGetAll = $this->auth->createPermission('clientGetAll');
        $clientGetAll->description = 'Get an array of all clients';
        $this->permissionAssociationArray['Admin'][] = $clientGetAll;
        $this->permissionArray[] = $clientGetAll;
		
        $clientView = $this->auth->createPermission('clientView');
        $clientView->description = 'View a client';
        $this->permissionAssociationArray['Admin'][] = $clientView;
        $this->permissionArray[] = $clientView;
		
        $clientCreate = $this->auth->createPermission('clientCreate');
        $clientCreate->description = 'Create a client';
        $this->permissionAssociationArray['Admin'][] = $clientCreate;
        $this->permissionArray[] = $clientCreate;

        $clientUpdate = $this->auth->createPermission('clientUpdate');
        $clientUpdate->description = 'Update client';
        $this->permissionAssociationArray['Admin'][] = $clientUpdate;
        $this->permissionArray[] = $clientUpdate;

        $clientDeactivate = $this->auth->createPermission('clientDeactivate');
        $clientDeactivate->description = 'Deactivate client';
        $this->permissionAssociationArray['Admin'][] = $clientDeactivate;
        $this->permissionArray[] = $clientDeactivate;
		
        $clientGetDropdown = $this->auth->createPermission('clientGetDropdown');
        $clientGetDropdown->description = 'Get an associative array of client ID/name pairs';
        $this->permissionAssociationArray['Engineer'][] = $clientGetDropdown;
        $this->permissionAssociationArray['Supervisor'][] = $clientGetDropdown;
        $this->permissionArray[] = $clientGetDropdown;
		
		//Employee Type permissions/////////////////////////////////////////////////////////////////

		$employeeTypeGetDropdown = $this->auth->createPermission('employeeTypeGetDropdown');
		$employeeTypeGetDropdown->description = 'Get an associative array of employee types';
		$this->permissionAssociationArray['Supervisor'][] = $employeeTypeGetDropdown;
		$this->permissionArray[] = $employeeTypeGetDropdown;
		
		//Equipment Calibration permissions/////////////////////////////////////////////////////////////////

		$equipmentCalibrationCreate = $this->auth->createPermission('equipmentCalibrationCreate');
		$equipmentCalibrationCreate->description = 'Creates a new equipment calibration record';
		$this->permissionAssociationArray['Technician'][] = $equipmentCalibrationCreate;
		$this->permissionArray[] = $equipmentCalibrationCreate;
		
		$equipmentCalibrationDelete = $this->auth->createPermission('equipmentCalibrationDelete');
		$equipmentCalibrationDelete->description = 'Deletes an existing equipment calibration record';
		$this->permissionAssociationArray['Technician'][] = $equipmentCalibrationDelete;
		$this->permissionArray[] = $equipmentCalibrationDelete;
		
		//Equipment Condition permissions/////////////////////////////////////////////////////////////////

		$equipmentConditionGetDropdown = $this->auth->createPermission('equipmentConditionGetDropdown');
		$equipmentConditionGetDropdown->description = 'Get an associative array of equipment conditions';
		$this->permissionAssociationArray['Engineer'][] = $equipmentConditionGetDropdown;
		$this->permissionAssociationArray['Supervisor'][] = $equipmentConditionGetDropdown;
		$this->permissionArray[] = $equipmentConditionGetDropdown;
		
		//Equipment permissions/////////////////////////////////////////////////////////////////

        $getOwnEquipment = $this->auth->createPermission('getOwnEquipment');
        $getOwnEquipment->description = 'Get equipment for associated projects';
        $this->permissionAssociationArray['Supervisor'][] = $getOwnEquipment;
        $this->permissionArray[] = $getOwnEquipment;
		
        $getAllEquipment = $this->auth->createPermission('getAllEquipment');
        $getAllEquipment->description = 'Get all equipment';
        $this->permissionAssociationArray['Engineer'][] = $getAllEquipment;
        $this->permissionArray[] = $getAllEquipment;
		
        $equipmentView = $this->auth->createPermission('equipmentView');
        $equipmentView->description = 'View equipment';
        $this->permissionAssociationArray['Supervisor'][] = $equipmentView;
        $this->permissionAssociationArray['Engineer'][] = $equipmentView;
        $this->permissionArray[] = $equipmentView;
		
        $equipmentCreate = $this->auth->createPermission('equipmentCreate');
        $equipmentCreate->description = 'Create equipment';
        $this->permissionAssociationArray['Technician'][] = $equipmentCreate;
        $this->permissionAssociationArray['Engineer'][] = $equipmentCreate;
        $this->permissionArray[] = $equipmentCreate;

        $equipmentUpdate = $this->auth->createPermission('equipmentUpdate');
        $equipmentUpdate->description = 'Update equipment';
        $this->permissionAssociationArray['Engineer'][] = $equipmentUpdate;
        $this->permissionAssociationArray['Supervisor'][] = $equipmentUpdate;
        $this->permissionArray[] = $equipmentUpdate;
		
        $equipmentDelete = $this->auth->createPermission('equipmentDelete');
        $equipmentDelete->description = 'Delete equipment';
        $this->permissionAssociationArray['Engineer'][] = $equipmentDelete;
        $this->permissionAssociationArray['Supervisor'][] = $equipmentDelete;
        $this->permissionArray[] = $equipmentDelete;
		
        $acceptEquipment = $this->auth->createPermission('acceptEquipment');
        $acceptEquipment->description = 'Accept equipment';
        $this->permissionAssociationArray['Engineer'][] = $acceptEquipment;
        $this->permissionAssociationArray['Supervisor'][] = $acceptEquipment;
        $this->permissionArray[] = $acceptEquipment;
		
		//Equipment Status permissions/////////////////////////////////////////////////////////////////

		$equipmentStatusGetDropdown = $this->auth->createPermission('equipmentStatusGetDropdown');
		$equipmentStatusGetDropdown->description = 'Get an associative array of equipment status';
		$this->permissionAssociationArray['Engineer'][] = $equipmentStatusGetDropdown;
		$this->permissionAssociationArray['Supervisor'][] = $equipmentStatusGetDropdown;
		$this->permissionArray[] = $equipmentStatusGetDropdown;
		
		//Equipment Type permissions/////////////////////////////////////////////////////////////////

		$equipmentTypeGetDropdown = $this->auth->createPermission('equipmentTypeGetDropdown');
		$equipmentTypeGetDropdown->description = 'Get an associative array of equipment types';
		$this->permissionAssociationArray['Engineer'][] = $equipmentTypeGetDropdown;
		$this->permissionAssociationArray['Supervisor'][] = $equipmentTypeGetDropdown;
		$this->permissionArray[] = $equipmentTypeGetDropdown;
		
		//Mileage Card permissions/////////////////////////////////////////////////////////////////

        $mileageCardGetOwnCards = $this->auth->createPermission('mileageCardGetOwnCards');
        $mileageCardGetOwnCards->description = 'Get an array of mileage cards for associated projects';
        $this->permissionAssociationArray['Dispatcher'][] = $mileageCardGetOwnCards;
        $this->permissionAssociationArray['Accountant'][] = $mileageCardGetOwnCards;
        $this->permissionArray[] = $mileageCardGetOwnCards;
		
        $mileageCardGetAllCards = $this->auth->createPermission('mileageCardGetAllCards');
        $mileageCardGetAllCards->description = 'Get an array of all mileage cards';
        $this->permissionAssociationArray['Accountant'][] = $mileageCardGetAllCards;
        $this->permissionAssociationArray['Admin'][] = $mileageCardGetAllCards;
        $this->permissionArray[] = $mileageCardGetAllCards;
		
		$mileageCardGetAccountantView = $this->auth->createPermission('mileageCardGetAccountantView');
        $mileageCardGetAccountantView->description = 'View the Accountant flavor of the mileage card screen';
        $this->permissionAssociationArray['Accountant'][] = $mileageCardGetAccountantView;
        $this->permissionArray[] = $mileageCardGetAccountantView;
		
		$mileageCardGetAccountantDetails = $this->auth->createPermission('mileageCardGetAccountantDetails');
        $mileageCardGetAccountantDetails->description = 'View expanded section details on the Accountant flavor of the mileage card screen';
        $this->permissionAssociationArray['Accountant'][] = $mileageCardGetAccountantDetails;
        $this->permissionArray[] = $mileageCardGetAccountantDetails;
		
        $mileageCardView = $this->auth->createPermission('mileageCardView');
        $mileageCardView->description = 'View a mileage card';
        $this->permissionAssociationArray['Dispatcher'][] = $mileageCardView;
        $this->permissionArray[] = $mileageCardView;
		
        $mileageCardGetCard = $this->auth->createPermission('mileageCardGetCard');
        $mileageCardGetCard->description = 'Get a mileage card for a user';
        $this->permissionAssociationArray['Technician'][] = $mileageCardGetCard;
        $this->permissionArray[] = $mileageCardGetCard;

        $mileageCardGetEntries = $this->auth->createPermission('mileageCardGetEntries');
        $mileageCardGetEntries->description = 'Get all mileage entries for a mileage card';
        $this->permissionAssociationArray['Dispatcher'][] = $mileageCardGetEntries;
        $this->permissionAssociationArray['Accountant'][] = $mileageCardGetEntries;
        $this->permissionArray[] = $mileageCardGetEntries;
		
        $mileageCardApprove = $this->auth->createPermission('mileageCardApprove');
        $mileageCardApprove->description = 'Approve a mileage card';
        $this->permissionAssociationArray['Supervisor'][] = $mileageCardApprove;
        $this->permissionArray[] = $mileageCardApprove;
		
		$mileageCardPmSubmit = $this->auth->createPermission('mileageCardPmSubmit');
        $mileageCardPmSubmit->description = 'Submit mileage cards to accounting.';
        $this->permissionAssociationArray['ProjectManager'][] = $mileageCardPmSubmit;
        $this->permissionArray[] = $mileageCardPmSubmit;
	
        $mileageCardSubmit = $this->auth->createPermission('mileageCardSubmit');
        $mileageCardSubmit->description = 'Submit Mileage Cards and generate output files';
        $this->permissionAssociationArray['Accountant'][] = $mileageCardSubmit;
        $this->permissionArray[] = $mileageCardSubmit;
		
		//Mileage Entry permissions/////////////////////////////////////////////////////////////////
		
        $mileageEntryView = $this->auth->createPermission('mileageEntryView');
        $mileageEntryView->description = 'View a mileage entry';
        $this->permissionAssociationArray['Dispatcher'][] = $mileageEntryView;
        $this->permissionAssociationArray['Accountant'][] = $mileageEntryView;
        $this->permissionArray[] = $mileageEntryView;
		
        $mileageEntryCreate = $this->auth->createPermission('mileageEntryCreate');
        $mileageEntryCreate->description = 'Create a mileage entry';
        $this->permissionAssociationArray['Dispatcher'][] = $mileageEntryCreate;
        $this->permissionAssociationArray['Accountant'][] = $mileageEntryCreate;
        $this->permissionArray[] = $mileageEntryCreate;
		
		$mileageEntryUpdate = $this->auth->createPermission('mileageEntryUpdate');
        $mileageEntryUpdate->description = 'Update a mileage entry';
        $this->permissionAssociationArray['Dispatcher'][] = $mileageEntryUpdate;
        $this->permissionAssociationArray['Accountant'][] = $mileageEntryUpdate;
        $this->permissionArray[] = $mileageEntryUpdate;
		
        $mileageEntryDeactivate = $this->auth->createPermission('mileageEntryDeactivate');
        $mileageEntryDeactivate->description = 'Deactivate a mileage entry';
        $this->permissionAssociationArray['Dispatcher'][] = $mileageEntryDeactivate;
        $this->permissionAssociationArray['Accountant'][] = $mileageEntryDeactivate;
        $this->permissionArray[] = $mileageEntryDeactivate;
		
		//Notifications permissions/////////////////////////////////////////////////////////////////

        $notificationsGet = $this->auth->createPermission('notificationsGet');
        $notificationsGet->description = 'Get notifications';
        $this->permissionAssociationArray['Engineer'][] = $notificationsGet;
        $this->permissionAssociationArray['Analyst'][] = $notificationsGet;
        $this->permissionAssociationArray['Dispatcher'][] = $notificationsGet;
        $this->permissionAssociationArray['Accountant'][] = $notificationsGet;
        $this->permissionArray[] = $notificationsGet;
		
		//Project permissions/////////////////////////////////////////////////////////////////

        $projectGetAll = $this->auth->createPermission('projectGetAll');
        $projectGetAll->description = 'Get an array of all projects';
        $this->permissionAssociationArray['Admin'][] = $projectGetAll;
        $this->permissionArray[] = $projectGetAll;
		
        $projectView = $this->auth->createPermission('projectView');
        $projectView->description = 'View a project';
        $this->permissionAssociationArray['Supervisor'][] = $projectView;
        $this->permissionArray[] = $projectView;
		
        $projectCreate = $this->auth->createPermission('projectCreate');
        $projectCreate->description = 'Create a project';
        $this->permissionAssociationArray['Admin'][] = $projectCreate;
        $this->permissionArray[] = $projectCreate;

        $projectUpdate = $this->auth->createPermission('projectUpdate');
        $projectUpdate->description = 'Update project';
        $this->permissionAssociationArray['Admin'][] = $projectUpdate;
        $this->permissionArray[] = $projectUpdate;
		
        $projectGetOwnProjects = $this->auth->createPermission('projectGetOwnProjects');
        $projectGetOwnProjects->description = 'Get all projects that a user is associated with';
        $this->permissionAssociationArray['Analyst'][] = $projectGetOwnProjects;
        $this->permissionAssociationArray['Supervisor'][] = $projectGetOwnProjects;
        $this->permissionArray[] = $projectGetOwnProjects;
		
        $projectGetDropdown = $this->auth->createPermission('projectGetDropdown');
        $projectGetDropdown->description = 'Get an associative array of project name/id pairs';
        $this->permissionAssociationArray['Engineer'][] = $projectGetDropdown;
        $this->permissionAssociationArray['Analyst'][] = $projectGetDropdown;
        $this->permissionAssociationArray['Supervisor'][] = $projectGetDropdown;
        $this->permissionArray[] = $projectGetDropdown;
		
        $projectGetUserRelationships = $this->auth->createPermission('projectGetUserRelationships');
        $projectGetUserRelationships->description = 'Get two arrays one of users associated with a project and one of all other users';
        $this->permissionAssociationArray['Supervisor'][] = $projectGetUserRelationships;
        $this->permissionArray[] = $projectGetUserRelationships;
		
        $projectAddRemoveUsers = $this->auth->createPermission('projectAddRemoveUsers');
        $projectAddRemoveUsers->description = 'Add or remove users from a project';
        $this->permissionAssociationArray['Supervisor'][] = $projectAddRemoveUsers;
        $this->permissionArray[] = $projectAddRemoveUsers;
		
		$projectGetProjectModules = $this->auth->createPermission('projectGetProjectModules');
        $projectGetProjectModules->description = 'Get modules available for projects.';
        $this->permissionAssociationArray['Admin'][] = $projectGetProjectModules;
        $this->permissionArray[] = $projectGetProjectModules;
		
		$projectAddRemoveModules = $this->auth->createPermission('projectAddRemoveModules');
        $projectAddRemoveModules->description = 'Add or remove modules from a project';
        $this->permissionAssociationArray['Admin'][] = $projectAddRemoveModules;
        $this->permissionArray[] = $projectAddRemoveModules;
		
		$projectViewConfig = $this->auth->createPermission('projectViewConfig');
        $projectViewConfig->description = 'View a project configuration';
        $this->permissionAssociationArray['ProjectManager'][] = $projectViewConfig;
        $this->permissionArray[] = $projectViewConfig;
		
		$projectUpdateConfig = $this->auth->createPermission('projectUpdateConfig');
        $projectUpdateConfig->description = 'Update a project configuration';
        $this->permissionAssociationArray['ProjectManager'][] = $projectUpdateConfig;
        $this->permissionArray[] = $projectUpdateConfig;
		
		//State Code permissions/////////////////////////////////////////////////////////////////

        $stateCodeGetDropdown = $this->auth->createPermission('stateCodeGetDropdown');
        $stateCodeGetDropdown->description = 'Get an associative array of state codes';
        $this->permissionAssociationArray['Admin'][] = $stateCodeGetDropdown;
        $this->permissionArray[] = $stateCodeGetDropdown;
		
		//Time Card permissions/////////////////////////////////////////////////////////////////

        $timeCardGetOwnCards = $this->auth->createPermission('timeCardGetOwnCards');
        $timeCardGetOwnCards->description = 'Get an array of multiple time cards for associated projects';
        $this->permissionAssociationArray['Dispatcher'][] = $timeCardGetOwnCards;
        $this->permissionAssociationArray['Accountant'][] = $timeCardGetOwnCards;
        $this->permissionArray[] = $timeCardGetOwnCards;
		
        $timeCardGetAllCards = $this->auth->createPermission('timeCardGetAllCards');
        $timeCardGetAllCards->description = 'Get an array of all time cards';
        $this->permissionAssociationArray['Accountant'][] = $timeCardGetAllCards;
        $this->permissionAssociationArray['Admin'][] = $timeCardGetAllCards;
        $this->permissionArray[] = $timeCardGetAllCards;
		
		$timeCardGetAccountantView = $this->auth->createPermission('timeCardGetAccountantView');
        $timeCardGetAccountantView->description = 'View the Accountant flavor of the time card screen';
        $this->permissionAssociationArray['Accountant'][] = $timeCardGetAccountantView;
        $this->permissionArray[] = $timeCardGetAccountantView;
		
		$timeCardGetAccountantDetails = $this->auth->createPermission('timeCardGetAccountantDetails');
        $timeCardGetAccountantDetails->description = 'View expanded section details on the Accountant flavor of the time card screen';
        $this->permissionAssociationArray['Accountant'][] = $timeCardGetAccountantDetails;
        $this->permissionArray[] = $timeCardGetAccountantDetails;
		
        $timeCardView = $this->auth->createPermission('timeCardView');
        $timeCardView->description = 'View a time card';
        $this->permissionAssociationArray['Dispatcher'][] = $timeCardView;
        $this->permissionAssociationArray['Accountant'][] = $timeCardView;
        $this->permissionArray[] = $timeCardView;
		
        $timeCardApproveCards = $this->auth->createPermission('timeCardApproveCards');
        $timeCardApproveCards->description = 'Approve time cards';
        $this->permissionAssociationArray['Supervisor'][] = $timeCardApproveCards;
        $this->permissionArray[] = $timeCardApproveCards;

        $timeCardGetCard = $this->auth->createPermission('timeCardGetCard');
        $timeCardGetCard->description = 'Get a users time card';
        $this->permissionAssociationArray['Technician'][] = $timeCardGetCard;
        $this->permissionArray[] = $timeCardGetCard;
		
        $timeCardGetEntries = $this->auth->createPermission('timeCardGetEntries');
        $timeCardGetEntries->description = 'Get all time entries for a time card';
        $this->permissionAssociationArray['Dispatcher'][] = $timeCardGetEntries;
        $this->permissionAssociationArray['Accountant'][] = $timeCardGetEntries;
        $this->permissionArray[] = $timeCardGetEntries;
		
		$timeCardPmSubmit = $this->auth->createPermission('timeCardPmSubmit');
        $timeCardPmSubmit->description = 'Submit time cards to accounting.';
        $this->permissionAssociationArray['ProjectManager'][] = $timeCardPmSubmit;
        $this->permissionArray[] = $timeCardPmSubmit;
	
        $timeCardSubmit = $this->auth->createPermission('timeCardSubmit');
        $timeCardSubmit->description = 'Submit Time Cards and generate output files';
        $this->permissionAssociationArray['Accountant'][] = $timeCardSubmit;
        $this->permissionArray[] = $timeCardSubmit;
		
        $checkSubmitButtonStatus = $this->auth->createPermission('checkSubmitButtonStatus');
        $checkSubmitButtonStatus->description = 'Check if given time cards are allowed to be submitted.';
        $this->permissionAssociationArray['Supervisor'][] = $checkSubmitButtonStatus;
        $this->permissionAssociationArray['Accountant'][] = $checkSubmitButtonStatus;
        $this->permissionArray[] = $checkSubmitButtonStatus;
		
		//Task Entry permissions/////////////////////////////////////////////////////////////////

        $getAllTask = $this->auth->createPermission('getAllTask');
        $getAllTask->description = 'Get all Task Types';
        $this->permissionAssociationArray['Dispatcher'][] = $getAllTask;
        $this->permissionAssociationArray['Accountant'][] = $getAllTask;
        $this->permissionArray[] = $getAllTask;
		
        $getChargeOfAccount = $this->auth->createPermission('getChargeOfAccount');
        $getChargeOfAccount->description = 'Get charge of account types';
        $this->permissionAssociationArray['Dispatcher'][] = $getChargeOfAccount;
        $this->permissionAssociationArray['Accountant'][] = $getChargeOfAccount;
        $this->permissionArray[] = $getChargeOfAccount;
		
        $createTaskEntry = $this->auth->createPermission('createTaskEntry');
        $createTaskEntry->description = 'Create a Task Entry';
        $this->permissionAssociationArray['Dispatcher'][] = $createTaskEntry;
        $this->permissionAssociationArray['Accountant'][] = $createTaskEntry;
        $this->permissionArray[] = $createTaskEntry;
		
		$taskGetByProject = $this->auth->createPermission('taskGetByProject');
        $taskGetByProject->description = 'Get task types for given project';
		$this->permissionAssociationArray['Dispatcher'][] = $taskGetByProject;
        $this->permissionAssociationArray['Technician'][] = $taskGetByProject;
        $this->permissionArray[] = $taskGetByProject;
		
		$taskGetHoursOverview = $this->auth->createPermission('taskGetHoursOverview');
        $taskGetHoursOverview->description = 'Get all existing task for given week';
        $this->permissionAssociationArray['Dispatcher'][] = $taskGetHoursOverview;
        $this->permissionAssociationArray['Accountant'][] = $taskGetHoursOverview;
        $this->permissionArray[] = $taskGetHoursOverview;
		
		//Time Entry permissions/////////////////////////////////////////////////////////////////

        $timeEntryView = $this->auth->createPermission('timeEntryView');
        $timeEntryView->description = 'View a time entry';
        $this->permissionAssociationArray['Dispatcher'][] = $timeEntryView;
        $this->permissionAssociationArray['Accountant'][] = $timeEntryView;
        $this->permissionArray[] = $timeEntryView;
		
        $timeEntryCreate = $this->auth->createPermission('timeEntryCreate');
        $timeEntryCreate->description = 'Create a time entry';
        $this->permissionAssociationArray['Dispatcher'][] = $timeEntryCreate;
        $this->permissionAssociationArray['Accountant'][] = $timeEntryCreate;
        $this->permissionArray[] = $timeEntryCreate;
		
        $timeEntryDeactivate = $this->auth->createPermission('timeEntryDeactivate');
        $timeEntryDeactivate->description = 'Deactivate a time entry';
        $this->permissionAssociationArray['Dispatcher'][] = $timeEntryDeactivate;
        $this->permissionAssociationArray['Accountant'][] = $timeEntryDeactivate;
        $this->permissionArray[] = $timeEntryDeactivate;
		
		//Expense Permissions//////////////////////////////////////////////////////////////
		
		$expenseGetOwn = $this->auth->createPermission('expenseGetOwn');
        $expenseGetOwn->description = 'View expenses for associated projects';
        $this->permissionAssociationArray['Dispatcher'][] = $expenseGetOwn;
        $this->permissionAssociationArray['Accountant'][] = $expenseGetOwn;
        $this->permissionArray[] = $expenseGetOwn;
		
		$expenseGetAll = $this->auth->createPermission('expenseGetAll');
        $expenseGetAll->description = 'View all expenses';
        $this->permissionAssociationArray['Accountant'][] = $expenseGetAll;
        $this->permissionAssociationArray['Admin'][] = $expenseGetAll;
        $this->permissionArray[] = $expenseGetAll;
		
		$expenseGetEntries = $this->auth->createPermission('expenseGetEntries');
        $expenseGetEntries->description = 'Get expense entries for a user, project, and date range';
        $this->permissionAssociationArray['Dispatcher'][] = $expenseGetEntries;
        $this->permissionAssociationArray['Accountant'][] = $expenseGetEntries;
        $this->permissionArray[] = $expenseGetEntries;
		
		$expenseCreate = $this->auth->createPermission('expenseCreate');
        $expenseCreate->description = 'Create an expense entry';
        $this->permissionAssociationArray['Dispatcher'][] = $expenseCreate;
        $this->permissionAssociationArray['Accountant'][] = $expenseCreate;
        $this->permissionArray[] = $expenseCreate;
		
		$expenseDeactivate = $this->auth->createPermission('expenseDeactivate');
        $expenseDeactivate->description = 'Deactiavte expense entries';
        $this->permissionAssociationArray['Dispatcher'][] = $expenseDeactivate;
        $this->permissionAssociationArray['Accountant'][] = $expenseDeactivate;
        $this->permissionArray[] = $expenseDeactivate;
		
		$expenseApprove = $this->auth->createPermission('expenseApprove');
        $expenseApprove->description = 'Approve expense records';
        $this->permissionAssociationArray['Supervisor'][] = $expenseApprove;
        $this->permissionArray[] = $expenseApprove;
		
		$expenseSubmit = $this->auth->createPermission('expenseSubmit');
        $expenseSubmit->description = 'Submit expenses and generate output file';
        $this->permissionAssociationArray['Accountant'][] = $expenseSubmit;
        $this->permissionArray[] = $expenseSubmit;
		
		$expenseGetAccountantView = $this->auth->createPermission('expenseGetAccountantView');
        $expenseGetAccountantView->description = 'View the Accountant flavor of the expense screen';
        $this->permissionAssociationArray['Accountant'][] = $expenseGetAccountantView;
        $this->permissionArray[] = $expenseGetAccountantView;
		
		$expenseGetAccountantDetails = $this->auth->createPermission('expenseGetAccountantDetails');
        $expenseGetAccountantDetails->description = 'View expanded section details on the Accountant flavor of the expense screen';
        $this->permissionAssociationArray['Accountant'][] = $expenseGetAccountantDetails;
        $this->permissionArray[] = $expenseGetAccountantDetails;
		
		//User permissions/////////////////////////////////////////////////////////////////

        $userGetActive = $this->auth->createPermission('userGetActive');
        $userGetActive->description = 'Get all active users';
        $this->permissionAssociationArray['Supervisor'][] = $userGetActive;
        $this->permissionArray[] = $userGetActive;
		
        $userGetInactive = $this->auth->createPermission('userGetInactive');
        $userGetInactive->description = 'Get all inactive users';
        $this->permissionAssociationArray['Supervisor'][] = $userGetInactive;
        $this->permissionArray[] = $userGetInactive;
		
        $userView = $this->auth->createPermission('userView');
        $userView->description = 'View a user';
        $this->permissionAssociationArray['Supervisor'][] = $userView;
        $this->permissionArray[] = $userView;
		
		//User Create Permissions
		
		//temp change user create permissions for admin only in scct
		if(BaseActiveController::isSCCT($this->client)){
			$userCreate = $this->auth->createPermission('userCreate');
			$userCreate->description = 'Create a user';
			$this->permissionAssociationArray['Admin'][] = $userCreate;
			$this->permissionArray[] = $userCreate;
			
			$userCreateTechnician = $this->auth->createPermission('userCreateTechnician');
			$userCreateTechnician->description = 'Create user of role type technician';
			$this->permissionAssociationArray['Admin'][] = $userCreateTechnician;
			$this->permissionArray[] = $userCreateTechnician;
			
			$userCreateDispatcher = $this->auth->createPermission('userCreateDispatcher');
			$userCreateDispatcher->description = 'Create user of role type dispatcher';
			$this->permissionAssociationArray['Admin'][] = $userCreateDispatcher;
			$this->permissionArray[] = $userCreateDispatcher;
			
			$userCreateSupervisor = $this->auth->createPermission('userCreateSupervisor');
			$userCreateSupervisor->description = 'Create user of role type supervisor';
			$this->permissionAssociationArray['Admin'][] = $userCreateSupervisor;
			$this->permissionArray[] = $userCreateSupervisor;
			
			$userCreateProjectManager = $this->auth->createPermission('userCreateProjectManager');
			$userCreateProjectManager->description = 'Create user of role type project manager';
			$this->permissionAssociationArray['Admin'][] = $userCreateProjectManager;
			$this->permissionArray[] = $userCreateProjectManager;
		}else{
			$userCreate = $this->auth->createPermission('userCreate');
			$userCreate->description = 'Create a user';
			$this->permissionAssociationArray['Supervisor'][] = $userCreate;
			$this->permissionArray[] = $userCreate;
			
			$userCreateTechnician = $this->auth->createPermission('userCreateTechnician');
			$userCreateTechnician->description = 'Create user of role type technician';
			$this->permissionAssociationArray['Supervisor'][] = $userCreateTechnician;
			$this->permissionArray[] = $userCreateTechnician;
			
			$userCreateDispatcher = $this->auth->createPermission('userCreateDispatcher');
			$userCreateDispatcher->description = 'Create user of role type dispatcher';
			$this->permissionAssociationArray['Supervisor'][] = $userCreateDispatcher;
			$this->permissionArray[] = $userCreateDispatcher;
			
			$userCreateSupervisor = $this->auth->createPermission('userCreateSupervisor');
			$userCreateSupervisor->description = 'Create user of role type supervisor';
			$this->permissionAssociationArray['Supervisor'][] = $userCreateSupervisor;
			$this->permissionArray[] = $userCreateSupervisor;
			
			$userCreateProjectManager = $this->auth->createPermission('userCreateProjectManager');
			$userCreateProjectManager->description = 'Create user of role type project manager';
			$this->permissionAssociationArray['ProjectManager'][] = $userCreateProjectManager;
			$this->permissionArray[] = $userCreateProjectManager;
		}
		
        $userCreateAnalyst = $this->auth->createPermission('userCreateAnalyst');
        $userCreateAnalyst->description = 'Create user of role type analyst';
        $this->permissionAssociationArray['Admin'][] = $userCreateAnalyst;
        $this->permissionArray[] = $userCreateAnalyst;
		
        $userCreateAccountant = $this->auth->createPermission('userCreateAccountant');
        $userCreateAccountant->description = 'Create user of role type accountant';
        $this->permissionAssociationArray['Admin'][] = $userCreateAccountant;
        $this->permissionArray[] = $userCreateAccountant;
		
        $userCreateAdmin = $this->auth->createPermission('userCreateAdmin');
        $userCreateAdmin->description = 'Create a user of role type admin';
        $this->permissionAssociationArray['Admin'][] = $userCreateAdmin;
        $this->permissionArray[] = $userCreateAdmin;
		
		//User Update Permissions

        $userUpdate = $this->auth->createPermission('userUpdate');
        $userUpdate->description = 'Update user';
        $this->permissionAssociationArray['Supervisor'][] = $userUpdate;
        $this->permissionArray[] = $userUpdate;
		
        $userUpdateTechnician = $this->auth->createPermission('userUpdateTechnician');
        $userUpdateTechnician->description = 'Update user of role type technician';
        $this->permissionAssociationArray['Supervisor'][] = $userUpdateTechnician;
        $this->permissionArray[] = $userUpdateTechnician;
		
		$userUpdateDispatcher = $this->auth->createPermission('userUpdateDispatcher');
        $userUpdateDispatcher->description = 'Update user of role type dispatcher';
        $this->permissionAssociationArray['Supervisor'][] = $userUpdateDispatcher;
        $this->permissionArray[] = $userUpdateDispatcher;
		
        $userUpdateSupervisor = $this->auth->createPermission('userUpdateSupervisor');
        $userUpdateSupervisor->description = 'Update user of role type supervisor';
        $this->permissionAssociationArray['Supervisor'][] = $userUpdateSupervisor;
        $this->permissionArray[] = $userUpdateSupervisor;
		
        $userUpdateProjectManager = $this->auth->createPermission('userUpdateProjectManager');
        $userUpdateProjectManager->description = 'Update user of role type project manager';
        $this->permissionAssociationArray['ProjectManager'][] = $userUpdateProjectManager;
        $this->permissionArray[] = $userUpdateProjectManager;
		
        $userUpdateAnalyst = $this->auth->createPermission('userUpdateAnalyst');
        $userUpdateAnalyst->description = 'Update user of role type analyst';
        $this->permissionAssociationArray['Admin'][] = $userUpdateAnalyst;
        $this->permissionArray[] = $userUpdateAnalyst;
		
        $userUpdateAccountant = $this->auth->createPermission('userUpdateAccountant');
        $userUpdateAccountant->description = 'Update user of role type accountant';
        $this->permissionAssociationArray['Admin'][] = $userUpdateAccountant;
        $this->permissionArray[] = $userUpdateAccountant;
		
        $userUpdateAdmin = $this->auth->createPermission('userUpdateAdmin');
        $userUpdateAdmin->description = 'Update user of role type admin';
        $this->permissionAssociationArray['Admin'][] = $userUpdateAdmin;
        $this->permissionArray[] = $userUpdateAdmin;
		
        $userDeactivate = $this->auth->createPermission('userDeactivate');
        $userDeactivate->description = 'Deactivate user';
        $this->permissionAssociationArray['Admin'][] = $userDeactivate;
        $this->permissionArray[] = $userDeactivate;
		
        $userReactivate = $this->auth->createPermission('userReactivate');
        $userReactivate->description = 'Reactivate user';
		$this->permissionAssociationArray['Supervisor'][] = $userReactivate;
		$this->permissionArray[] = $userReactivate;
		
        $userGetMe = $this->auth->createPermission('userGetMe');
        $userGetMe->description = 'Get equipment and project data for a user';
		$this->permissionAssociationArray['Technician'][] = $userGetMe;
		$this->permissionAssociationArray['Engineer'][] = $userGetMe;
		$this->permissionAssociationArray['Dispatcher'][] = $userGetMe;
		$this->permissionArray[] = $userGetMe;
		
		//Breadcrumb permissions/////////////////////////////////////////////////////////////////

        $breadcrumbCreate = $this->auth->createPermission('breadcrumbCreate');
        $breadcrumbCreate->description = 'Create Breadcrumbs';
        $this->permissionAssociationArray['Technician'][] = $breadcrumbCreate;
        $this->permissionArray[] = $breadcrumbCreate;
		
		////// Dropdown Controller Permissions ///////
		
        $getWebDropDowns = $this->auth->createPermission('getWebDropDowns');
        $getWebDropDowns->description = 'Route to get Web Dropdowns for multiple screens.';
        $this->permissionAssociationArray['Supervisor'][] = $getWebDropDowns;
        $this->permissionAssociationArray['Accountant'][] = $getWebDropDowns;
        $this->permissionArray[] = $getWebDropDowns;
	
        $getTrackerMapGridsDropdown = $this->auth->createPermission('getTrackerMapGridsDropdown');
        $getTrackerMapGridsDropdown->description = 'Get Map Grid list for Tracker dropdown.';
        $this->permissionAssociationArray['Supervisor'][] = $getTrackerMapGridsDropdown;
        $this->permissionArray[] = $getTrackerMapGridsDropdown;
		
        $getTabletSurveyDropdowns = $this->auth->createPermission('getTabletSurveyDropdowns');
        $getTabletSurveyDropdowns->description = 'Get route for all tablet dropdowns.';
        $this->permissionAssociationArray['Technician'][] = $getTabletSurveyDropdowns;
        $this->permissionArray[] = $getTabletSurveyDropdowns;
		
		////// Map Controller Permissions //////
		
        $mapGet = $this->auth->createPermission('mapGet');
        $mapGet->description = 'Get map data for web maps.';
        $this->permissionAssociationArray['Supervisor'][] = $mapGet;
        $this->permissionArray[] = $mapGet;
		
		////// Reports Controller Permissions //////
		
		$reportGetDropdown = $this->auth->createPermission('reportGetDropdown');
        $reportGetDropdown->description = 'Get dropdown for available reports.';
        $this->permissionAssociationArray['Analyst'][] = $reportGetDropdown;
        $this->permissionAssociationArray['Dispatcher'][] = $reportGetDropdown;
        $this->permissionAssociationArray['Accountant'][] = $reportGetDropdown;
        $this->permissionArray[] = $reportGetDropdown;
		
		$reportGet = $this->auth->createPermission('reportGet');
        $reportGet->description = 'Get report data.';
        $this->permissionAssociationArray['Analyst'][] = $reportGet;
        $this->permissionAssociationArray['Dispatcher'][] = $reportGet;
        $this->permissionAssociationArray['Accountant'][] = $reportGet;
        $this->permissionArray[] = $reportGet;
		
		$reportGetParmDropdown = $this->auth->createPermission('reportGetParmDropdown');
        $reportGetParmDropdown->description = 'Get dropdown for current report paramaters.';
        $this->permissionAssociationArray['Analyst'][] = $reportGetParmDropdown;
        $this->permissionAssociationArray['Dispatcher'][] = $reportGetParmDropdown;
        $this->permissionAssociationArray['Accountant'][] = $reportGetParmDropdown;
        $this->permissionArray[] = $reportGetParmDropdown;
		
		$reportGetInspectorDropdown = $this->auth->createPermission('reportGetInspectorDropdown');
        $reportGetInspectorDropdown->description = 'Get dropdown of available inspectors for a report.';
        $this->permissionAssociationArray['Analyst'][] = $reportGetInspectorDropdown;
        $this->permissionAssociationArray['Dispatcher'][] = $reportGetInspectorDropdown;
        $this->permissionAssociationArray['Accountant'][] = $reportGetInspectorDropdown;
        $this->permissionArray[] = $reportGetInspectorDropdown;
		
		////// Alert Controller Permissions //////
		
		$alertGet = $this->auth->createPermission('alertGet');
        $alertGet->description = 'Get all alerts for given project.';
        $this->permissionAssociationArray['Technician'][] = $alertGet;
        $this->permissionArray[] = $alertGet;
		
		$alertCreate = $this->auth->createPermission('alertCreate');
        $alertCreate->description = 'Create an array of alerts.';
        $this->permissionAssociationArray['Technician'][] = $alertCreate;
        $this->permissionArray[] = $alertCreate;
		
		////// ABC Codes Controller Permissions //////
		$abcCodesCreateTaskOut = $this->auth->createPermission('abcCodesCreateTaskOut');
        $abcCodesCreateTaskOut->description = 'Create ABCTaskOut records.';
        $this->permissionAssociationArray['Technician'][] = $abcCodesCreateTaskOut;
        $this->permissionArray[] = $abcCodesCreateTaskOut;
		
		////// Question Controller Permissions //////
		$questionCreate = $this->auth->createPermission('questionCreate');
        $questionCreate->description = 'Create question records.';
        $this->permissionAssociationArray['Technician'][] = $questionCreate;
        $this->permissionArray[] = $questionCreate;
		
		////// Pto Controller Permissions //////
		$ptoCreate = $this->auth->createPermission('ptoCreate');
        $ptoCreate->description = 'Create pto records.';
        $this->permissionAssociationArray['Technician'][] = $ptoCreate;
        $this->permissionArray[] = $ptoCreate;
		
		$ptoGetBalance = $this->auth->createPermission('ptoGetBalance');
        $ptoGetBalance->description = 'Get the PTO blanace of a user based on timecard.';
		$this->permissionAssociationArray['Dispatcher'][] = $ptoGetBalance;
        $this->permissionAssociationArray['Accountant'][] = $ptoGetBalance;
        $this->permissionArray[] = $ptoGetBalance;
		
		////// Route Controller Permissions //////
		
		$routeOptimization1 = $this->auth->createPermission('routeOptimization1');
        $routeOptimization1->description = 'Get optimized route for data set.';
        $this->permissionAssociationArray['Admin'][] = $routeOptimization1;
        $this->permissionArray[] = $routeOptimization1;
		
		$routeOptimization2 = $this->auth->createPermission('routeOptimization2');
        $routeOptimization2->description = 'Get optimized route for data set.';
        $this->permissionAssociationArray['Admin'][] = $routeOptimization2;
        $this->permissionArray[] = $routeOptimization2;

		////// Module Menu Permissions //////
		
		$viewAdministrationMenu = $this->auth->createPermission('viewAdministrationMenu');
        $viewAdministrationMenu->description = 'View Administration Menu';
        $this->permissionAssociationArray['Engineer'][] = $viewAdministrationMenu;
        $this->permissionAssociationArray['Dispatcher'][] = $viewAdministrationMenu;
        $this->permissionAssociationArray['Accountant'][] = $viewAdministrationMenu;
        $this->permissionArray[] = $viewAdministrationMenu;
		
		$viewReportsMenu = $this->auth->createPermission('viewReportsMenu');
        $viewReportsMenu->description = 'View Reports Menu';
        $this->permissionAssociationArray['Analyst'][] = $viewReportsMenu;
        $this->permissionAssociationArray['Dispatcher'][] = $viewReportsMenu;
        $this->permissionAssociationArray['Accountant'][] = $viewReportsMenu;
        $this->permissionArray[] = $viewReportsMenu;
		
		$viewHomeMenu = $this->auth->createPermission('viewHomeMenu');
        $viewHomeMenu->description = 'View Home Menu';
        $this->permissionAssociationArray['Engineer'][] = $viewHomeMenu;
        $this->permissionAssociationArray['Analyst'][] = $viewHomeMenu;
        $this->permissionAssociationArray['Dispatcher'][] = $viewHomeMenu;
        $this->permissionAssociationArray['Accountant'][] = $viewHomeMenu;
        $this->permissionArray[] = $viewHomeMenu;
		
		$viewTrackerMenu = $this->auth->createPermission('viewTrackerMenu');
        $viewTrackerMenu->description = 'View Tracker Menu';
        $this->permissionAssociationArray['Supervisor'][] = $viewTrackerMenu;
        $this->permissionArray[] = $viewTrackerMenu;
		
		$viewTrainingMenu = $this->auth->createPermission('viewTrainingMenu');
        $viewTrainingMenu->description = 'View Training Menu';
        $this->permissionAssociationArray['Supervisor'][] = $viewTrainingMenu;
        $this->permissionArray[] = $viewTrainingMenu;
		
        ////// Module Sub Menu Permissions //////

        $viewClientMgmt = $this->auth->createPermission('viewClientMgmt');
        $viewClientMgmt->description = 'View client management menu item';
        $this->permissionAssociationArray['Admin'][] = $viewClientMgmt;
        $this->permissionArray[] = $viewClientMgmt;

        $viewProjectMgmt = $this->auth->createPermission('viewProjectMgmt');
        $viewProjectMgmt->description = 'View project management  menu item';
        $this->permissionAssociationArray['ProjectManager'][] = $viewProjectMgmt;
        $this->permissionArray[] = $viewProjectMgmt;

        $viewUserMgmt = $this->auth->createPermission('viewUserMgmt');
        $viewUserMgmt->description = 'View user management menu item';
        $this->permissionAssociationArray['Supervisor'][] = $viewUserMgmt;
        $this->permissionArray[] = $viewUserMgmt;

        $viewEquipmentMgmt = $this->auth->createPermission('viewEquipmentMgmt');
        $viewEquipmentMgmt->description = 'View equipment management menu item';
        $this->permissionAssociationArray['Engineer'][] = $viewEquipmentMgmt;
        $this->permissionAssociationArray['Supervisor'][] = $viewEquipmentMgmt;
        $this->permissionArray[] = $viewEquipmentMgmt;

        $viewTimeCardMgmt = $this->auth->createPermission('viewTimeCardMgmt');
        $viewTimeCardMgmt->description = 'View time card management menu item';
        $this->permissionAssociationArray['Dispatcher'][] = $viewTimeCardMgmt;
        $this->permissionAssociationArray['Accountant'][] = $viewTimeCardMgmt;
        $this->permissionArray[] = $viewTimeCardMgmt;

        $viewMileageCardMgmt = $this->auth->createPermission('viewMileageCardMgmt');
        $viewMileageCardMgmt->description = 'View mileage card management menu item';
        $this->permissionAssociationArray['Dispatcher'][] = $viewMileageCardMgmt;
        $this->permissionAssociationArray['Accountant'][] = $viewMileageCardMgmt;
        $this->permissionArray[] = $viewMileageCardMgmt;
		
		$viewExpenseMgmt = $this->auth->createPermission('viewExpenseMgmt');
        $viewExpenseMgmt->description = 'View expense management menu item';
        $this->permissionAssociationArray['Supervisor'][] = $viewExpenseMgmt;
        $this->permissionAssociationArray['Accountant'][] = $viewExpenseMgmt;
        $this->permissionArray[] = $viewExpenseMgmt;
		
		$viewBreadcrumbDisplay = $this->auth->createPermission('viewBreadcrumbDisplay');
        $viewBreadcrumbDisplay->description = 'View breadcrumb display menu item';
        $this->permissionAssociationArray['Dispatcher'][] = $viewBreadcrumbDisplay;
        $this->permissionArray[] = $viewBreadcrumbDisplay;
		
		echo "Base Permissions Array Created.\n";		
	}
	
	private function createSurveyPermissions()
	{
		echo "Creating Survey Permissions Array.\n";	
		//Work Queue permissions/////////////////////////////////////////////////////////////////

        $workQueueGet = $this->auth->createPermission('workQueueGet');
        $workQueueGet->description = 'Get User Assigned Work Queue';
        $this->permissionAssociationArray['Technician'][] = $workQueueGet;
        $this->permissionArray[] = $workQueueGet;
		
		//CGE permissions/////////////////////////////////////////////////////////////////

        $cgeGetMapGrids = $this->auth->createPermission('cgeGetMapGrids');
        $cgeGetMapGrids->description = 'Get map grids that contain CGE records';
        $this->permissionAssociationArray['Supervisor'][] = $cgeGetMapGrids;
        $this->permissionArray[] = $cgeGetMapGrids;
		
        $cgeGetByMap = $this->auth->createPermission('cgeGetByMap');
        $cgeGetByMap->description = 'Get cge records for given map';
		$this->permissionAssociationArray['Supervisor'][] = $cgeGetByMap;
        $this->permissionArray[] = $cgeGetByMap;
		
        $cgeGetHistory = $this->auth->createPermission('cgeGetHistory');
        $cgeGetHistory->description = 'Get all previous records for given cge';
        $this->permissionAssociationArray['Supervisor'][] = $cgeGetHistory;
        $this->permissionArray[] = $cgeGetHistory;
		
		//Dispatch permissions/////////////////////////////////////////////////////////////////
		
        $dispatchGetAvailable = $this->auth->createPermission('dispatchGetAvailable');
        $dispatchGetAvailable->description = 'Get all map grids or sections by map grid for unassigned work orders';
        $this->permissionAssociationArray['Supervisor'][] = $dispatchGetAvailable;
        $this->permissionArray[] = $dispatchGetAvailable;
		
        $dispatchGetAvailableAssets = $this->auth->createPermission('dispatchGetAvailableAssets');
        $dispatchGetAvailableAssets->description = 'Get all unassigned work order records';
        $this->permissionAssociationArray['Supervisor'][] = $dispatchGetAvailableAssets;
        $this->permissionArray[] = $dispatchGetAvailableAssets;
		
        $dispatchGetSurveyors = $this->auth->createPermission('dispatchGetSurveyors');
        $dispatchGetSurveyors->description = 'Get all users that can be assigned work';
        $this->permissionAssociationArray['Supervisor'][] = $dispatchGetSurveyors;
        $this->permissionArray[] = $dispatchGetSurveyors;
		
        $dispatch = $this->auth->createPermission('dispatch');
        $dispatch->description = 'Dispatch work orders to users';
        $this->permissionAssociationArray['Supervisor'][] = $dispatch;
        $this->permissionArray[] = $dispatch;
		
        $dispatchGetAssigned = $this->auth->createPermission('dispatchGetAssigned');
        $dispatchGetAssigned->description = 'Get all map grids or sections by map grid for assigned work orders';
        $this->permissionAssociationArray['Supervisor'][] = $dispatchGetAssigned;
        $this->permissionArray[] = $dispatchGetAssigned;
		
        $dispatchGetAssignedAssets = $this->auth->createPermission('dispatchGetAssignedAssets');
        $dispatchGetAssignedAssets->description = 'Get all assigned work order records';
        $this->permissionAssociationArray['Supervisor'][] = $dispatchGetAssignedAssets;
        $this->permissionArray[] = $dispatchGetAssignedAssets;
		
        $dispatchUnassign = $this->auth->createPermission('dispatchUnassign');
        $dispatchUnassign->description = 'Unassign work orders from users';
        $this->permissionAssociationArray['Supervisor'][] = $dispatchUnassign;
        $this->permissionArray[] = $dispatchUnassign;
		
        $dispatchGetDualDispatch = $this->auth->createPermission('dispatchGetDualDispatch');
        $dispatchGetDualDispatch->description = 'Get records for dual dispatch regression test';
        $this->permissionAssociationArray['Supervisor'][] = $dispatchGetDualDispatch;
        $this->permissionArray[] = $dispatchGetDualDispatch;
		
		////// Inspection Controller Permissions //////
		
        $inspectionUpdate = $this->auth->createPermission('inspectionUpdate');
        $inspectionUpdate->description = 'Update Inspection Record.';
        $this->permissionAssociationArray['Technician'][] = $inspectionUpdate;
        $this->permissionArray[] = $inspectionUpdate;
		
        $inspectionClearEvent = $this->auth->createPermission('inspectionClearEvent');
        $inspectionClearEvent->description = 'Clear events on and inspection record.';
        $this->permissionAssociationArray['Technician'][] = $inspectionClearEvent;
        $this->permissionArray[] = $inspectionClearEvent;
		
        $inspectionGetMapGrids = $this->auth->createPermission('inspectionGetMapGrids');
        $inspectionGetMapGrids->description = 'Get map grids for inspection web view.';
        $this->permissionAssociationArray['Supervisor'][] = $inspectionGetMapGrids;
        $this->permissionArray[] = $inspectionGetMapGrids;
		
        $inspectionsGet = $this->auth->createPermission('inspectionsGet');
        $inspectionsGet->description = 'Get inspection records for web view.';
        $this->permissionAssociationArray['Supervisor'][] = $inspectionsGet;
        $this->permissionArray[] = $inspectionsGet;
		
        $inspectionsGetEvents = $this->auth->createPermission('inspectionsGetEvents');
        $inspectionsGetEvents->description = 'Get events for inspection web view.';
        $this->permissionAssociationArray['Supervisor'][] = $inspectionsGetEvents;
        $this->permissionArray[] = $inspectionsGetEvents;
		
		////// Module Menu Permissions //////
		
		$viewDispatchMenu = $this->auth->createPermission('viewDispatchMenu');
        $viewDispatchMenu->description = 'View Dispatch Menu';
        $this->permissionAssociationArray['Supervisor'][] = $viewDispatchMenu;
        $this->permissionArray[] = $viewDispatchMenu;
		
		////// Module Sub Menu Permissions //////
		
        $viewDispatch = $this->auth->createPermission('viewDispatch');
        $viewDispatch->description = 'View dispatch menu item';
        $this->permissionAssociationArray['Supervisor'][] = $viewDispatch;
        $this->permissionArray[] = $viewDispatch;

        $viewAssigned = $this->auth->createPermission('viewAssigned');
        $viewAssigned->description = 'View assigned menu item';
        $this->permissionAssociationArray['Supervisor'][] = $viewAssigned;
        $this->permissionArray[] = $viewAssigned;
        
		$viewInspections = $this->auth->createPermission('viewInspections');
        $viewInspections->description = 'View Inspections';
        $this->permissionAssociationArray['Supervisor'][] = $viewInspections;
        $this->permissionArray[] = $viewInspections;
		
		$viewCGE = $this->auth->createPermission('viewCGE');
        $viewCGE->description = 'View CGEs';
        $this->permissionAssociationArray['Supervisor'][] = $viewCGE;
        $this->permissionArray[] = $viewCGE;
		
		echo "Survey Permissions Array Created.\n";
	}
	
	private function assignPermissions()
	{
		// add roles and children/////////////////////////////////////////////////////////////////
		echo "Creating Role Types and Adding Permissions.\n";
		//add "Technician" role and give this role CRUD permissions
		$technician = $this->auth->createRole('Technician');
		$this->auth->add($technician);
		//add children
		$this->auth->addChildren($technician, $this->permissionAssociationArray['Technician']);
		
		//TODO remove this role
		// add "Engineer" role and give this role CRUD permissions
		$engineer = $this->auth->createRole('Engineer');
		$this->auth->add($engineer);
		//add children
		$this->auth->addChildren($engineer, $this->permissionAssociationArray['Engineer']);
		
		// add "analyst" role and give this role CRUD permissions
        $analyst = $this->auth->createRole('Analyst');
        $this->auth->add($analyst);
		//add children
		$this->auth->addChildren($analyst, array_merge([$technician], $this->permissionAssociationArray['Analyst']));
		
		// add "dispatcher" role and give this role CRUD permissions
        $dispatcher = $this->auth->createRole('Dispatcher');
        $this->auth->add($dispatcher);
		//add children
		$this->auth->addChildren($dispatcher, $this->permissionAssociationArray['Dispatcher']);

        // add "supervisor" role and give this role CRUD permissions
        $supervisor = $this->auth->createRole('Supervisor');
        $this->auth->add($supervisor);
		//add children
		$this->auth->addChildren($supervisor, array_merge([$technician, $dispatcher], $this->permissionAssociationArray['Supervisor']));

        // add "projectManager" role and give this role the permissions of the "supervisor"
        $projectManager = $this->auth->createRole('ProjectManager');
        $this->auth->add($projectManager);
		//add children
		$this->auth->addChildren($projectManager, array_merge([$supervisor], $this->permissionAssociationArray['ProjectManager']));

		// add "accountant" role and give this role CRUD permissions
        $accountant = $this->auth->createRole('Accountant');
        $this->auth->add($accountant);
		//add children
		$this->auth->addChildren($accountant, array_merge([$technician], $this->permissionAssociationArray['Accountant']));
		
		// add "admin" role and give this role the permissions of the "projectManager" and "engineer"
		$admin = $this->auth->createRole('Admin');
        $this->auth->add($admin);
		//add children
		$this->auth->addChildren($admin, array_merge([$engineer, $projectManager], $this->permissionAssociationArray['Admin']));
		
		echo "Role Types Created and Permissions Assigned.\n";
	}
	
	private function reAssignUserRoles()
	{
		//assign roles to existing users////////////////////////////////////////
		$userModel = BaseActiveRecord::getUserModel($this->client);
		$users = $userModel::find()
				->where(['UserActiveFlag' => 1])
				->all();
		
		$userSize = count($users);
		$userRoleTypes = $this->auth->getRoles();
		$maxChunkSize = 500;
		$chunkCount = ceil($userSize/$maxChunkSize);
		
		echo "Preparing Bulk Assignment of Roles to Users for $userSize Users in $chunkCount User Groups.\n";
		
		//loop chunks of users to avoid error on 1000+ row insert
		for($j = 0; $j < $chunkCount; $j++){		
			$bulkUserInsertArray = array();
			$chunkStartIndex = $maxChunkSize * $j;
			$chunkSize = ($j < $chunkCount -1) ? $maxChunkSize : $userSize - ($maxChunkSize * $j);
			echo "Preparing Bulk Assignment of Roles to Users for $chunkSize Users in Group " . ($j+1) . ".\n";
			//assign roles to users already in the system
			for($i = $chunkStartIndex; $i < $maxChunkSize * $j + $chunkSize; $i++)
			{
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
			echo "Execute Bulk Assignment of Roles to Users for Group " . ($j+1) . ".\n";
			$this->auth->bulkAssign($bulkUserInsertArray);
		}
		
		echo "Users Roles Assigned.\n";
    }
}
