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
		//add "viewDispatch" permission
		$viewDispatch = $auth->createPermission('viewDispatch');
		$viewDispatch->description = 'View the Dispatch Screen';
		$auth->add($viewDispatch);
		//add "dispatch" permission
		$dispatch = $auth->createPermission('dispatch');
		$dispatch->description = 'Dispatch IRs to Surveyors in the Field';
		$auth->add($dispatch);
		
		//Assigned Permissions//
		//add "viewAssigned" permission
		$viewAssigned = $auth->createPermission('viewAssigned');
		$viewAssigned->description = 'View the Assigned Screen';
		$auth->add($viewAssigned);
		//add "unassign" permission
		$unassign = $auth->createPermission('unassign');
		$unassign->description = 'Unassign a Surveyor from an IR';
		$auth->add($unassign);
		//add "addSurveyor" permission
		$addSurveyor = $auth->createPermission('addSurveyor');
		$addSurveyor->description = 'Add a Surveyor to an IR that has already been assigned';
		$auth->add($addSurveyor);
		
		//Leak Log Permissions//
		//add "viewLeakLogMgnt" permission
		$viewLeakLogMgnt = $auth->createPermission('viewLeakLogMgnt');
		$viewLeakLogMgnt->description = 'View the Leak Log Management screen';
		$auth->add($viewLeakLogMgnt);
		//add "viewLeakLogSearch" permission
		$viewLeakLogSearch = $auth->createPermission('viewLeakLogSearch');
		$viewLeakLogSearch->description = 'View the Leak Log Search screen';
		$auth->add($viewLeakLogSearch);
		//add "viewLeakDetails" permission
		$viewLeakDetails = $auth->createPermission('viewLeakDetails');
		$viewLeakDetails->description = 'View the Leak Log Detail screen';
		$auth->add($viewLeakDetails);
		//add "submitLeak" permission
		$submitLeak = $auth->createPermission('submitLeak');
		$submitLeak->description = 'Submit a Leak to SAP';
		$auth->add($submitLeak);
		//add "editLeak" permission
		$editLeak = $auth->createPermission('editLeak');
		$editLeak->description = 'Edit a Leak';
		$auth->add($editLeak);
		//add "transferFLOC" permission
		$transferFLOC = $auth->createPermission('transferFLOC');
		$transferFLOC->description = 'Transfer a Leak to a new Functional Location';
		$auth->add($transferFLOC);
		
		//Map Stamp Permissions//
		//add "viewMapStampMgnt" permission
		$viewMapStampMgnt = $auth->createPermission('viewMapStampMgnt');
		$viewMapStampMgnt->description = 'View Map Stamp Management Screen';
		$auth->add($viewMapStampMgnt);
		//add "viewMapStampDetail" permission
		$viewMapStampDetail = $auth->createPermission('viewMapStampDetail');
		$viewMapStampDetail->description = 'View Map Stamp Detail Screen';
		$auth->add($viewMapStampDetail);
		//add "editMapStamp" permission
		$editMapStamp = $auth->createPermission('editMapStamp');
		$editMapStamp->description = 'Edit a Map Stamp Record';
		
		//AOC Permissions//
		//add "viewAOC" permission
		$viewAOC = $auth->createPermission('viewAOC');
		$viewAOC->description = 'View the AOC screen';
		$auth->add($viewAOC);
		
		
		/////////////////////////// add roles and children //////////////////////////////////////////////
		
		//add "Surveyor/Inspector" role
		$surveyorInspector = $auth->createRole('Survyeor/Inspector');
		$auth->add($surveyorInspector);
		//add permissions to Surveyor/Inspector
		
		//add "QM" role
		$qm = $auth->createRole('QM');
		$auth->add($qm);
		//add child roles to QM
		//add permissions to QM
		$auth->addChild($qm, $viewLeakLogMgnt);
		$auth->addChild($qm, $viewLeakLogSearch);
		$auth->addChild($qm, $viewLeakDetails);
		$auth->addChild($qm, $viewMapStampMgnt);
		$auth->addChild($qm, $viewMapStampDetail);
		$auth->addChild($qm, $viewAOC);
		
		//add "BSS/Analyst" role
		$bssAnalyst = $auth->createRole('BSS/Analyst');
		$auth->add($bssAnalyst);
		//add child roles to BSS/Analyst
		$auth->addChild($bssAnalyst, $qm);
		//add permissions to BSS/Analyst
		$auth->addChild($bssAnalyst, $viewDispatch);
		$auth->addChild($bssAnalyst, $viewAssigned);
		$auth->addChild($bssAnalyst, $viewMapStampDetailModal);
		
		//add "Supervisor" role
		$supervisor = $auth->createRole('Supervisor');
		$auth->add($supervisor);
		//add child roles to Supervisor
		$auth->addChild($supervisor, $bssAnalyst);
		//add permissions to Supervisor
		$auth->addChild($supervisor, $dispatch);
		$auth->addChild($supervisor, $unassign);
		$auth->addChild($supervisor, $addSurveyor);
		$auth->addChild($supervisor, $submitLeak);
		$auth->addChild($supervisor, $editLeak);
		$auth->addChild($supervisor, $transferFLOC);
		
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
		$auth->addChild($administrator, $transferFLOC);
		
		
		///////////////////////////assign roles to existing users////////////////////////////////////////
		$users = PGEUser::find()
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