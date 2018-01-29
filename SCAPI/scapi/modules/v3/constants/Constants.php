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
	const CLIENT_DB_MANAGER = 'app\modules\v3\rbac\ClientDbManager';
	
	//scct databases
	const SCCT_DEV = 'scctdev';
	const SCCT_STAGE = 'scctstage';
	const SCCT_PROD = 'scct';
	
	//base comet tracker databases
	const CT_DEV = 'apidev';	
	const CT_STAGE = 'apistage';
	const CT_PROD = 'api';
	//azure prod
	const AZURE_CT_PROD = 'azureapi';
	//comet tracker models
	const CT_USER = 'app\modules\v3\models\SCUser';
	const CT_EVENT = self::BASE_EVENT;
	const CT_ASSET = self::BASE_ASSET;
	//comet tracker auth manager
	const CT_AUTH = 'app\modules\v3\rbac\ScDbManager';
	
	//york databases
	const YORK_DEV = 'yorkdev';
	const YORK_STAGE = 'yorkstage';
	const YORK_PROD = 'york';
	//azure
	const AZURE_YORK_PROD = 'azureyork';
	//york models
	const YORK_USER = self::BASE_USER;
	const YORK_EVENT = self::BASE_EVENT;
	const YORK_ASSET = 'app\modules\v3\modules\york\models\Asset';
	//york auth manager
	const YORK_AUTH = self::CLIENT_DB_MANAGER;
	
	//scana databases
	const SCANA_DEV = 'scanadev';
	const SCANA_STAGE = 'scanastage';
	const SCANA_PROD = 'scana';
	//azure
	const AZURE_SCANA_PROD = 'azurescana';
	//york models
	const SCANA_USER = self::BASE_USER;
	const SCANA_EVENT = 'app\modules\v3\modules\scana\models\Event';
	const SCANA_ASSET = 'app\modules\v3\modules\scana\models\Asset';
	//york auth manager
	const SCANA_AUTH = self::CLIENT_DB_MANAGER;
	
	//demo client database
	const DEMO_DEV = 'demo';
	//demo models
	const DEMO_USER = self::BASE_USER;
	//should there be a demo module?
	const DEMO_EVENT = 'app\modules\v3\models\DemoEvent';
	const DEMO_ASSET = self::BASE_ASSET;
	//demo auth manager
	const DEMO_AUTH = self::CLIENT_DB_MANAGER;
	
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