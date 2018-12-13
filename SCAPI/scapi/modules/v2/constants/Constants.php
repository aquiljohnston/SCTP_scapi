<?php 

namespace app\modules\v2\constants;

final class Constants
{
	const DATE_FORMAT = 'Y-m-d H:i:s';
	const PERMISSION_CONTROLLER = 'app\modules\v2\controllers\PermissionsController';
	
	//user messages
	const USERNAME_EXIST_MESSAGE = 'UserName already exist.';
	const METHOD_NOT_ALLOWED = 'Method Not Allowed';
	
	//Active Record Client Configs
	//base user
	const BASE_USER = 'app\modules\v2\models\BaseUser';
	const BASE_EVENT = 'app\modules\v2\models\Event';
	const BASE_ASSET = 'app\modules\v2\models\Asset';
	const BASE_TASKOUT = 'app\modules\v2\models\TaskOut';
	const CLIENT_DB_MANAGER = 'app\modules\v2\rbac\ClientDbManager';
	
	//time card submission file locations
	const DEV_DEFAULT_QB_PATH = 'C:\\ClientShare\\QuickBooksDev\\';
	const DEV_DEFAULT_OASIS_PATH = 'C:\\ClientShare\\OasisDev\\';
	const DEV_DEFAULT_ADP_PATH = 'C:\\ClientShare\\ADPDev\\';
	
	const STAGE_DEFAULT_QB_PATH = 'C:\\ClientShare\\QuickBooksStage\\';
	const STAGE_DEFAULT_OASIS_PATH = 'C:\\ClientShare\\OasisStage\\';
	const STAGE_DEFAULT_ADP_PATH = 'C:\\ClientShare\\ADPStage\\';
	
	const PROD_DEFAULT_OASIS_PATH = 'K:\\Oasis\\';
	const PROD_DEFAULT_QB_PATH = 'K:\\QuickBooks\\';
	const PROD_DEFAULT_ADP_PATH = 'K:\\ADP\\';

	const OASIS = 'OASIS';
	//need to change to payroll instead of quickbooks
	const QUICKBOOKS = 'QB';
	const ADP = 'ADP';
	const OT_PAYROLL_HOURS_ID = 5110;
	
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
	const TIME_CARD_SUBMISSION_QB = 'QB Submission';
	const TIME_CARD_SUBMISSION_ADP = 'ADP Submission';
	const TIME_CARD_SUBMISSION_RESET = 'Reset Submission';
	
	const API_CONFIG = [
		'DEV_HEADER' => 'apidev',
		'STAGE_HEADER' => 'apistage',
		'PROD_HEADER' => 'api',
		'AZURE_PROD_HEADER' => 'azureapi',
		'DEMO_HEADER' => 'apidemo',
		'DEV_DB' => 'ctDevDb',
		'STAGE_DB' => 'ctStageDb',
		'PROD_DB' => 'ctProdDb',
		'DEMO_DB' => 'ctDemoDb',
		'AUTH' => 'app\modules\v2\rbac\ScDbManager',
		'ASSET' => self::BASE_ASSET,
		'EVENT' => self::BASE_EVENT,
		'TASKOUT' => self::BASE_TASKOUT,
		'USER' => 'app\modules\v2\models\SCUser'
	];
	
	const SCCT_CONFIG = [
		'BASE_PROJECT' => 'SOUTHERN CROSS:CT2',
		'DEV_HEADER' => 'scctdev',
		'STAGE_HEADER' => 'scctstage',
		'PROD_HEADER' => 'scct',
		'DEV_DB' => 'ctDevDb',
		'STAGE_DB' => 'ctStageDb',
		'PROD_DB' => 'ctProdDb',
		'AUTH' => 'app\modules\v2\rbac\ScDbManager',
		'ASSET' => self::BASE_ASSET,
		'EVENT' => self::BASE_EVENT,
		'TASKOUT' => self::BASE_TASKOUT,
		'USER' => 'app\modules\v2\models\SCUser'
	];

	const PGE_CONFIG = [
		'DEV_HEADER' => 'pgedev',
		'STAGE_HEADER' => 'pgestage',
		'PROD_HEADER' => 'pge',
		'DEV_DB' => 'pgeDevDb',
		'STAGE_DB' => 'pgeStageDb',
		'PROD_DB' => 'pgeProdDb',
		'AUTH' => 'app\modules\v2\rbac\PgeDbManager',
		'USER' => 'app\modules\v2\modules\pge\models\PGEUser'
	];
	
	const YORK_CONFIG = [
		'DEV_HEADER' => 'yorkdev',
		'STAGE_HEADER' => 'yorkstage',
		'PROD_HEADER' => 'york',
		'DEV_DB' => 'yorkDevDb',
		'STAGE_DB' => 'yorkStageDb',
		'PROD_DB' => 'yorkProdDb',
		'AUTH' => self::CLIENT_DB_MANAGER,
		'ASSET' => 'app\modules\v2\modules\york\models\Asset',
		'EVENT' => self::BASE_EVENT,
		'TASKOUT' => 'app\modules\v2\modules\york\models\TaskOut',
		'USER' => self::BASE_USER
	];
	
	const DOMINION_CONFIG = [
		'STAGE_HEADER' => 'deostage',
		'PROD_HEADER' => 'deo',
		'STAGE_DB' => 'dominionStageDb',
		'PROD_DB' => 'dominionProdDb',
		'AUTH' => self::CLIENT_DB_MANAGER,
		'ASSET' => self::BASE_ASSET,
		'EVENT' => 'app\modules\v2\models\DominionEvent',
		'TASKOUT' => self::BASE_TASKOUT,
		'USER' => self::BASE_USER
	];
	
	const SCANA_CONFIG = [
		'DEV_HEADER' => 'scanadev',
		'STAGE_HEADER' => 'scanastage',
		'PROD_HEADER' => 'azurescana',
		'DEV_DB' => 'scanaDevDb',
		'STAGE_DB' => 'scanaStageDb',
		'PROD_DB' => 'scanaProdDb',
		'AUTH' => self::CLIENT_DB_MANAGER,
		'ASSET' => 'app\modules\v2\modules\scana\models\Asset',
		'EVENT' => 'app\modules\v2\models\ScanaEvent',
		'TASKOUT' => 'app\modules\v2\modules\scana\models\TaskOut',
		'USER' => self::BASE_USER
	];
	
	const DEMO_CONFIG = [
		'DEV_HEADER' => 'demo',
		'DEV_DB' => 'demoDb',
		'AUTH' => self::CLIENT_DB_MANAGER,
		'ASSET' => 'app\modules\v2\models\DemoAsset',
		'EVENT' => 'app\modules\v2\models\DemoEvent',
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