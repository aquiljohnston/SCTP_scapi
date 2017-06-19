<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\v1\modules\pge\models\PGEUser;
use app\rbac\PgeDbManager;

/**
* This Class establishes the rules of the RBAC system for the API
* Permissions are created and assigned and the role hierarchy is established
*/
class PgeRbacController extends Controller
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
		PGEUser::setClient($client);
		$db = PGEUser::getDb();
		$auth = new PgeDbManager($db);
		
		//////////////////////////remove existing permissions///////////////////////////////////////////////
		try{
			//create connection
			$connection = $db;
			//start transaction
			$transaction = $connection-> beginTransaction();
			//create commands
			$deleteAssignmentCommand = $connection->createCommand("DELETE FROM auth_assignment");
			$deleteItemChildCommand = $connection->createCommand("DELETE FROM auth_item_child");
			$deleteItemCommand = $connection->createCommand("DELETE FROM auth_item");
			$deleteRuleCommand = $connection->createCommand("DELETE FROM auth_rule");
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
		
		////////////////////////////create permissions////////////////////////////////////////////////////
		
		//Dispatch Permissions//
		//add "dispatch" permission
		$dispatch = $auth->createPermission('dispatch');
		$dispatch->description = 'Dispatch IRs to Surveyors in the Field';
		$auth->add($dispatch);
		
		//Assigned Permissions//
		//add "unassign" permission
		$unassign = $auth->createPermission('unassign');
		$unassign->description = 'Unassign a Surveyor from an IR';
		$auth->add($unassign);
		//add "addSurveyor" permission
		$addSurveyor = $auth->createPermission('addSurveyor');
		$addSurveyor->description = 'Add a Surveyor to an IR that has already been assigned';
		$auth->add($addSurveyor);
		
		//Leak Log Permissions//
		//add "viewLeakDetails" permission
		$viewLeakDetails = $auth->createPermission('viewLeakDetails');
		$viewLeakDetails->description = 'View the Leak Log Detail screen';
		$auth->add($viewLeakDetails);
		//add "submitLeak" permission
		$submitLeak = $auth->createPermission('submitLeak');
		$submitLeak->description = 'Submit a Leak to SAP';
		$auth->add($submitLeak);
		//add "approveLeak" permission
		$approveLeak = $auth->createPermission('approveLeak');
		$approveLeak->description = 'Approve a Leak';
		$auth->add($approveLeak);
		//add "editLeak" permission
		$editLeak = $auth->createPermission('editLeak');
		$editLeak->description = 'Edit a Leak';
		$auth->add($editLeak);
		//add "transferFLOC" permission
		$transferFLOC = $auth->createPermission('transferFLOC');
		$transferFLOC->description = 'Transfer a Leak to a new Functional Location';
		$auth->add($transferFLOC);
		
		//Map Stamp Permissions//
		//add "editMapStampDetail" permission
		$editMapStampDetail = $auth->createPermission('editMapStampDetail');
		$editMapStampDetail->description = 'Edit a Map Stamp Detail Record';
		$auth->add($editMapStampDetail);
		//add "viewMapStampDetailModal"
		$viewMapStampDetailModal = $auth->createPermission('viewMapStampDetailModal');
		$viewMapStampDetailModal->description = 'View the Modal on the Map Stamp Detail Screen';
		$auth->add($viewMapStampDetailModal);
		//add "submitMapStamp"
		$submitMapStamp = $auth->createPermission('submitMapStamp');
		$submitMapStamp->description = 'Submit a Map Stamp';
		$auth->add($submitMapStamp);
		$associateToMaintenancePlan = $auth->createPermission('associateToMaintenancePlan');
		$associateToMaintenancePlan->description = 'Associate a Map Stamp Record to Different IR';
		$auth->add($associateToMaintenancePlan);
		
		//AOC Permissions//
		
		//User Mgmt Permissions//
		//add "addUser" permission
		$addUser = $auth->createPermission('addUser');
		$addUser->description = 'Add a New User';
		$auth->add($addUser);
		//add "editUser" permission
		$editUser = $auth->createPermission('editUser');
		$editUser->description = 'Edit an Existing User';
		$auth->add($editUser);
		//add "deactivateUser" permission
		$deactivateUser = $auth->createPermission('deactivateUser');
		$deactivateUser->description = 'Deactivate an Existing User';
		$auth->add($deactivateUser);
		
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
		
		
		/////////////////////////// add roles and children //////////////////////////////////////////////
		
		//add "Surveyor/Inspector" role
		$surveyorInspector = $auth->createRole('Surveyor/Inspector');
		$auth->add($surveyorInspector);
		//add permissions to Surveyor/Inspector
		
		//add "QM" role
		$qm = $auth->createRole('QM');
		$auth->add($qm);
		//add child roles to QM
		//add permissions to QM
		$auth->addChild($qm, $viewDispatch);
		$auth->addChild($qm, $viewLeakLogMgmt);
		$auth->addChild($qm, $viewLeakLogSearch);
		$auth->addChild($qm, $viewLeakDetails);
		$auth->addChild($qm, $viewMapStampMgmt);
		$auth->addChild($qm, $viewMapStampDetail);
		$auth->addChild($qm, $viewAOC);
		$auth->addChild($qm, $viewUserMgmt);
		//menu permissions
		$auth->addChild($qm, $viewAdministrationMenu);
		$auth->addChild($qm, $viewDashboardMenu);
		$auth->addChild($qm, $viewDispatchMenu);
		$auth->addChild($qm, $viewReportsMenu);
		//sub menu permissions
		$auth->addChild($qm, $viewTracker);
		
		//add "BSS/Analyst" role
		$bssAnalyst = $auth->createRole('BSS/Analyst');
		$auth->add($bssAnalyst);
		//add child roles to BSS/Analyst
		$auth->addChild($bssAnalyst, $qm);
		//add permissions to BSS/Analyst
		$auth->addChild($bssAnalyst, $viewAssigned);
		$auth->addChild($bssAnalyst, $viewMapStampDetailModal);
		$auth->addChild($bssAnalyst, $addUser);
		$auth->addChild($bssAnalyst, $editUser);
		$auth->addChild($bssAnalyst, $deactivateUser);
		
		//add "SupervisorSupport" role
		$supervisorSupport = $auth->createRole('SupervisorSupport');
		$auth->add($supervisorSupport);
		//add child roles to SupervisorSupport
		$auth->addChild($supervisorSupport, $bssAnalyst);
		//add permissions to SupervisorSupport
		$auth->addChild($supervisorSupport, $dispatch);
		$auth->addChild($supervisorSupport, $unassign);
		$auth->addChild($supervisorSupport, $addSurveyor);
		$auth->addChild($supervisorSupport, $submitLeak);
		$auth->addChild($supervisorSupport, $approveLeak);
		$auth->addChild($supervisorSupport, $editLeak);
		$auth->addChild($supervisorSupport, $transferFLOC);
		$auth->addChild($supervisorSupport, $editMapStampDetail);
		$auth->addChild($supervisorSupport, $submitMapStamp);
		$auth->addChild($supervisorSupport, $associateToMaintenancePlan);
		
		//add "Supervisor" role
		$supervisor = $auth->createRole('Supervisor');
		$auth->add($supervisor);
		//add child roles to Supervisor
		$auth->addChild($supervisor, $supervisorSupport);
		
		//add "Administrator" role
		$administrator = $auth->createRole('Administrator');
		$auth->add($administrator);
		//add child roles to Administrator
		$auth->addChild($administrator, $bssAnalyst);
		//add permissions to Administrator
		$auth->addChild($administrator, $dispatch);
		$auth->addChild($administrator, $unassign);
		$auth->addChild($administrator, $addSurveyor);
		$auth->addChild($administrator, $submitLeak);
		$auth->addChild($administrator, $approveLeak);
		$auth->addChild($administrator, $transferFLOC);
		//menu permissions
		$auth->addChild($administrator, $viewHomeMenu);
		// sub menu permissions
		$auth->addChild($administrator, $viewClientMgmt);
		$auth->addChild($administrator, $viewProjectMgmt);
		$auth->addChild($administrator, $viewEquipmentMgmt);
		$auth->addChild($administrator, $viewTimeCardMgmt);
		$auth->addChild($administrator, $viewMileageCardMgmt);
		
		
		///////////////////////////assign roles to existing users////////////////////////////////////////
		$users = PGEUser::find()
				->where(['UserInActiveFlag' => 0])
				->all();
		
		$userSize = count($users);
		
		//assign roles to users already in the system
		for($i = 0; $i < $userSize; $i++)
		{
			if($userRole = $auth->getRole($users[$i]["UserAppRoleType"]))
			{
				$auth->assign($userRole, $users[$i]["UserUID"]);
			}
		}
	}
}