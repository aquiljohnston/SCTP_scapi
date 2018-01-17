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
	
	//scct databases
	const SCCT_DEV = 'scctdev';
	const SCCT_STAGE = 'scctstage';
	const SCCT_PROD = 'scct';
	
	//base comet tracker databases
	const CT_DEV = 'apidev';	
	const CT_STAGE = 'apistage';
	const CT_PROD = 'api';
	//comet tracker models
	const CT_USER = 'app\modules\v2\models\SCUser';
	const CT_EVENT = self::BASE_EVENT;
	//comet tracker auth manager
	const CT_AUTH = 'app\rbac\ScDbManager';
	
	//pg&e databases
	const PGE_DEV = 'pgedev';
	const PGE_STAGE = 'pgestage';
	const PGE_PROD = 'pge';
	//pg&e user model
	const PGE_USER = 'app\modules\v2\modules\pge\models\PGEUser';
	//pg&e auth manager
	const PGE_AUTH = 'app\rbac\PgeDbManager';
	
	//york databases
	const YORK_DEV = 'yorkdev';
	const YORK_STAGE = 'yorkstage';
	const YORK_PROD = 'york';
	//york models
	const YORK_USER = self::BASE_USER;
	const YORK_EVENT = self::BASE_EVENT;
	//york auth manager
	const YORK_AUTH = 'app\rbac\ClientDbManager';
	
	//dominion databases
	const DOMINION_STAGE = 'deostage';
	const DOMINION_PROD = 'deo';
	//dominion models
	const DOMINION_USER = self::BASE_USER;
	const DOMINION_EVENT = 'app\modules\v2\models\DominionEvent';
	//dominion auth manager
	const DOMINION_AUTH = 'app\rbac\ClientDbManager';
	
	//scana databases
	const SCANA_DEV = 'scanadev';
	const SCANA_STAGE = 'scanastage';
	const SCANA_PROD = 'scana';
	//york models
	const SCANA_USER = self::BASE_USER;
	const SCANA_EVENT = 'app\modules\v2\models\ScanaEvent';
	//york auth manager
	const SCANA_AUTH = 'app\rbac\ClientDbManager';
	
	//demo client database
	const DEMO_DEV = 'demo';
	//beta models
	const DEMO_USER = self::BASE_USER;
	const DEMO_EVENT = 'app\modules\v2\models\DemoEvent';
	//beta auth manager
	const DEMO_AUTH = 'app\rbac\ClientDbManager';
	
	//azure test database
	const AZURE_TEST = 'azure';
	//beta models
	const AZURE_USER = self::BASE_USER;
	const AZURE_EVENT = self::BASE_EVENT;
	//beta auth manager
	const AZURE_AUTH = 'app\rbac\ClientDbManager';
	
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