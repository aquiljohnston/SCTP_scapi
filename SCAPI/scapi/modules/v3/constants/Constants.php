<?php 

namespace app\modules\v3\constants;

final class Constants
{
	const DATE_FORMAT = 'Y-m-d H:i:s';
	const PERMISSION_CONTROLLER = 'app\modules\v3\controllers\PermissionsController';
	
	//user messages
	const USERNAME_EXIST_MESSAGE = 'UserName already exist.';
	const METHOD_NOT_ALLOWED = 'Method Not Allowed';
	
	//Active Record Client Configs
	//base user
	const BASE_USER = 'app\modules\v3\models\BaseUser';
	const BASE_EVENT = 'app\modules\v3\models\Event';
	const BASE_ASSET = 'app\modules\v3\models\Asset';
	const BASE_TASKOUT = 'app\modules\v3\models\TaskOut';
	const CLIENT_DB_MANAGER = 'app\modules\v3\rbac\ClientDbManager';
	
	//Time Card Submission File Locations
	const DEV_DEFAULT_QB_PATH = 'C:\\ClientShare\\QuickBooksDev\\';
	const DEV_DEFAULT_OASIS_PATH = 'C:\\ClientShare\\OasisDev\\';
	const DEV_DEFAULT_ADP_PATH = 'C:\\ClientShare\\ADPDev\\';
	
	const STAGE_DEFAULT_QB_PATH = 'C:\\ClientShare\\QuickBooksStage\\';
	const STAGE_DEFAULT_OASIS_PATH = 'C:\\ClientShare\\OasisStage\\';
	const STAGE_DEFAULT_ADP_PATH = 'C:\\ClientShare\\ADPStage\\';
	
	const PROD_DEFAULT_OASIS_PATH = 'C:\\ClientShare\\Oasis\\';
	const PROD_DEFAULT_QB_PATH = 'C:\\ClientShare\\QuickBooks\\';
	const PROD_DEFAULT_ADP_PATH = 'C:\\ClientShare\\ADP\\';
	
	//COA Values
	const OT_PAYROLL_HOURS_ID = 4005;
	const HOLIDAY_BEREAVEMENT_PAYROLL_HOURS_ID = 5015;
	const PTO_PAYROLL_HOURS_ID = 5020;
	const PERDIEM_EXPENSE_ID = 4450;

	//Time Card File Types
	const OASIS = 'OASIS';
	//need to change to payroll instead of quickbooks
	const MSDYNAMICS_TIMECARD = 'tcMSDynamics';
	const ADP = 'ADP';
	
	//Payment Method
	const PAY_METHOD_SALARY = 'S';
	const PAY_METHOD_HOURLY = 'H';
	
	//Time Card Submission file namespace
	const OASIS_FILE_NAME = 'oasis_history_';
	const PAYROLL_FILE_NAME = 'payroll_history_';
	const ADP_FILE_NAME = 'adp_history';
	
	//Time Card Event History Types
	const TIME_CARD_APPROVAL = 'Supervisor Approval';
	const TIME_CARD_PM_APPROVAL = 'PM Approval';
	const TIME_CARD_SUBMISSION_OASIS = 'Oasis Submission';
	const TIME_CARD_SUBMISSION_MSDYNAMICS = 'MS Dynamics Submission';
	const TIME_CARD_SUBMISSION_ADP = 'ADP Submission';
	const TIME_CARD_SUBMISSION_RESET = 'Reset Submission';
	const TIME_CARD_ACCOUNTANT_RESET = 'Accountant Reset Time';
	const TIME_CARD_PM_RESET = 'PM Reset Time';
	
	//Mileage Card Submission File Name
	const OASIS_MILEAGE_FILE_NAME = 'oasis_mileage_history_';
	const MSDYNAMICS_MILEAGE_FILE_NAME = 'msdynamics_mileage_history';
	
	//Mileage Card Event History Types
	//growing crossover with time card type may want to consolidate.
	const MILEAGE_CARD_APPROVAL = 'Supervisor Approval';
	const MILEAGE_CARD_PM_APPROVAL = 'PM Approval';
	const MILEAGE_CARD_SUBMISSION_OASIS = 'Oasis Submission';
	const MILEAGE_CARD_SUBMISSION_MSDYNAMICS = 'MS Dynamics Submission';
	const MILEAGE_CARD_SUBMISSION_RESET = 'Reset Submission';
	const MILEAGE_CARD_ACCOUNTANT_RESET = 'Accountant Reset Mileage';
	const MILEAGE_CARD_PM_RESET = 'PM Reset Mileage';
	
	//Mileage Card File Types
	const MILEAGE_CARD_OASIS = 'mcOASIS';
	const MSDYNAMICS_MILEAGECARD = 'mcMSDynamics';
	
	//Mileage Card Submission File Locations
	const DEV_DEFAULT_MILEAGE_OASIS_PATH = 'C:\\ClientShare\\MileageCardOasisDev\\';
	const DEV_DEFAULT_MILEAGE_ADP_PATH = 'C:\\ClientShare\\MileageCardADPDev\\';
	
	const STAGE_DEFAULT_MILEAGE_OASIS_PATH = 'C:\\ClientShare\\MileageCardOasisStage\\';
	const STAGE_DEFAULT_MILEAGE_ADP_PATH = 'C:\\ClientShare\\MileageCardADPStage\\';
	
	const PROD_DEFAULT_MILEAGE_OASIS_PATH = 'C:\\ClientShare\\MileageCardOasis\\';
	const PROD_DEFAULT_MILEAGE_ADP_PATH = 'C:\\ClientShare\\MileageCardADP\\';
	
	//Expense Submission file namespace
	const EXPENSE_FILE_NAME = 'expense_history_';
	
	//Exepnse Event History Types
	const EXPENSE_APPROVAL = 'PM/Supervisor Approval';
	const EXPENSE_SUBMISSION = 'Submission';
	const EXPENSE_DEACTIVATE = 'Deactivate';
	
	//Expense File Types
	const EXPENSE_OUTPUT = 'expense';
	
	//Expense Submission File Locations
	const DEV_DEFAULT_EXPENSE_PATH = 'C:\\ClientShare\\ExpenseDev\\';
	const STAGE_DEFAULT_EXPENSE_PATH = 'C:\\ClientShare\\ExpenseStage\\';
	const PROD_DEFAULT_EXPENSE_PATH = 'C:\\ClientShare\\Expense\\';
	
	//Notifications
	const NOTIFICATION_TYPE_TIME = 'Time Card';
	const NOTIFICATION_TYPE_MILEAGE = 'Mileage Card';
	const NOTIFICATION_DESCRIPTION_RESET_PM_TIME = 'Reset PM Time Submission';
	const NOTIFICATION_DESCRIPTION_RESET_PM_MILEAGE = 'Reset PM Mileage Submission';
	const NOTIFICATION_DESCRIPTION_RESET_REQUEST_PM_TIME = 'Request to Reset PM Time Submission';
	const NOTIFICATION_DESCRIPTION_RESET_REQUEST_PM_MILEAGE = 'Request to Reset PM Mileage Submission';
	
	//App Role
	const APP_ROLE_ADMIN = 'Admin';
	const APP_ROLE_PROJECT_MANAGER = 'ProjectManager';
	const APP_ROLE_SUPERVISOR = 'Supervisor';
	const APP_ROLE_TECHNICIAN = 'Technician';
	const APP_ROLE_ACCOUNTANT = 'Accountant';
	const APP_ROLE_ANALYST = 'Analyst';
	
	const API_CONFIG = [
		'DEV_HEADER' => 'apidev',
		'STAGE_HEADER' => 'apistage',
		'PROD_HEADER' => 'api',
		'AZURE_PROD_HEADER' => 'azureapi',
		'DEV_DB' => 'ctDevDb',
		'STAGE_DB' => 'ctStageDb',
		'PROD_DB' => 'ctProdDb',
		'AUTH' => 'app\modules\v3\rbac\ScDbManager',
		'ASSET' => self::BASE_ASSET,
		'EVENT' => self::BASE_EVENT,
		'TASKOUT' => self::BASE_TASKOUT,
		'USER' => 'app\modules\v3\models\SCUser'
	];
	
	const SCCT_CONFIG = [
		'BASE_PROJECT' => 'SOUTHERN CROSS:CT2',
		'DEV_HEADER' => 'scctdev',
		'STAGE_HEADER' => 'scctstage',
		'PROD_HEADER' => 'scct',
		'DEV_DB' => 'ctDevDb',
		'STAGE_DB' => 'ctStageDb',
		'PROD_DB' => 'ctProdDb',
		'AUTH' => 'app\modules\v3\rbac\ScDbManager',
		'ASSET' => self::BASE_ASSET,
		'EVENT' => self::BASE_EVENT,
		'TASKOUT' => self::BASE_TASKOUT,
		'USER' => 'app\modules\v3\models\SCUser'
	];
	
	const SCANA_CONFIG = [
		'DEV_HEADER' => 'scanadev',
		'STAGE_HEADER' => 'scanastage',
		'PROD_HEADER' => 'azurescana',
		'DEV_DB' => 'scanaDevDb',
		'STAGE_DB' => 'scanaStageDb',
		'PROD_DB' => 'scanaProdDb',
		'AUTH' => self::CLIENT_DB_MANAGER,
		'ASSET' => 'app\modules\v3\modules\scana\models\Asset',
		'EVENT' => 'app\modules\v3\modules\scana\models\Event',
		'TASKOUT' => 'app\modules\v3\modules\scana\models\TaskOut',
		'USER' => self::BASE_USER
	];
	
	const DEMO_CONFIG = [
		'DEV_HEADER' => 'demo',
		'DEV_DB' => 'demoDb',
		'AUTH' => self::CLIENT_DB_MANAGER,
		'ASSET' => 'app\modules\v3\models\DemoAsset',
		'EVENT' => 'app\modules\v3\models\DemoEvent',
		'TASKOUT' => self::BASE_TASKOUT,
		'USER' => self::BASE_USER
	];
	
	//work queue status codes
	const WORK_QUEUE_ASSIGNED = 100;
	const WORK_QUEUE_IN_PROGRESS = 101;
	const WORK_QUEUE_COMPLETED = 102;
	
	//work order event indicators
	const WORK_ORDER_COMPLETED_NO_EVENT = 0;
	const WORK_ORDER_COMPLETED_WITH_EVENT = 1;
	const WORK_ORDER_CGE = 2;
	const WORK_ORDER_ADHOC = 3;
	
	private function __construct()
	{
		throw new Exception("Can't get an instance of Constants.");
	}
}