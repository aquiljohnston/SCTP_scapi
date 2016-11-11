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
		
		/////////////////////////// add roles and children //////////////////////////////////////////////
		
		//add "Surveyor/Inspector" role
		$surveyorInspector = $auth->createRole('Survyeor/Inspector');
		$auth->add($surveyorInspector);
		//add permissions to Surveyor/Inspector
		
		//add "BSS/Analyst" role
		$bssAnalyst = $auth->createRole('BSS/Analyst');
		$auth->add($bssAnalyst);
		//add permissions to BSS/Analyst
		
		//add "QM" role
		$qm = $auth->createRole('QM');
		$auth->add($qm);
		//add permissions to QM
		
		//add "Supervisor" role
		$supervisor = $auth->createRole('Supervisor');
		$auth->add($supervisor);
		//add permissions to Supervisor
		$auth->addChild($supervisor, $dispatch);
		
		//add "Administrator" role
		$administrator = $auth->createRole('Administrator');
		$auth->add($administrator);
		//add child roles to Administrator
		$auth->addChild($administrator, $supervisor);
		//add permissions to Administrator
		
		
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