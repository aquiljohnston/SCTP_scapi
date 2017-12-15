<?php

namespace app\modules\v2\models;

use Yii;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	private static $CLIENT_ID = '';
	
	//base user
	const BASE_USER = 'app\modules\v2\models\BaseUser';
	const BASE_EVENT = 'app\modules\v2\models\Event';
	
	//TODO: create object/array for all clients and refactor get methods(exclude getDb) into single function
	//that takes in client and model to retrive that will be based on client object keys
	
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
		//azure
		const AZURE_YORK_PROD = 'azureyork';
	//york models
	const YORK_USER = self::BASE_USER;
	const YORK_EVENT = self::BASE_EVENT;
	//york auth manager
	const YORK_AUTH = 'app\rbac\ClientDbManager';
	
	//dominion databases
	const DOMINION_STAGE = 'deostage';
	const DOMINION_PROD = 'deo';
		//azure
		const AZURE_DOMINION_PROD = 'azuredeo';
	//dominion models
	const DOMINION_USER = self::BASE_USER;
	const DOMINION_EVENT = 'app\modules\v2\models\DominionEvent';
	//dominion auth manager
	const DOMINION_AUTH = 'app\rbac\ClientDbManager';
	
	//scana databases
	const SCANA_DEV = 'scanadev';
	const SCANA_STAGE = 'scanastage';
	const SCANA_PROD = 'azurescana';
		//azure
		const AZURE_SCANA_PROD = 'azurescana';
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
	
	
	public static function getClient()
	{
		return self::$CLIENT_ID;
	}

	public static function setClient($id)
	{
		self::$CLIENT_ID = $id;
	}
	
	public static function getDb()
	{
		//comet tracker
		if (self::$CLIENT_ID == self::CT_DEV || self::$CLIENT_ID == self::SCCT_DEV)
		{
			return Yii::$app->ctDevDb;
		}
		if (self::$CLIENT_ID == self::CT_STAGE || self::$CLIENT_ID == self::SCCT_STAGE)
		{
			return Yii::$app->ctStageDb;
		}
		if (self::$CLIENT_ID == self::CT_PROD || self::$CLIENT_ID == self::SCCT_PROD || self::$CLIENT_ID == self::AZURE_CT_PROD)
		{
			return Yii::$app->ctProdDb;
		}
		//pge
		if (self::$CLIENT_ID == self::PGE_DEV)
		{
			return Yii::$app->pgeDevDb;
		}
		if (self::$CLIENT_ID == self::PGE_STAGE)
		{
			return Yii::$app->pgeStageDb;
		}
		if (self::$CLIENT_ID == self::PGE_PROD)
		{
			return Yii::$app->pgeProdDb;
		}
		//york
		if (self::$CLIENT_ID == self::YORK_DEV)
		{
			return Yii::$app->yorkDevDb;
		}
		if (self::$CLIENT_ID == self::YORK_STAGE)
		{
			return Yii::$app->yorkStageDb;
		}
		if (self::$CLIENT_ID == self::YORK_PROD || self::$CLIENT_ID == self::AZURE_YORK_PROD)
		{
			return Yii::$app->yorkProdDb;
		}
		//dominion
		if (self::$CLIENT_ID == self::DOMINION_STAGE)
		{
			return Yii::$app->dominionStageDb;
		}
		if (self::$CLIENT_ID == self::DOMINION_PROD || self::$CLIENT_ID == self::AZURE_DOMINION_PROD)
		{
			return Yii::$app->dominionProdDb;
		}
		//scana
		if (self::$CLIENT_ID == self::SCANA_DEV)
		{
			return Yii::$app->scanaDevDb;
		}
		// if (self::$CLIENT_ID == self::SCANA_STAGE)
		// {
			// return Yii::$app->scanaStageDb;
		// }
		if (self::$CLIENT_ID == self::SCANA_PROD || self::$CLIENT_ID == self::AZURE_SCANA_PROD)
		{
			return Yii::$app->scanaProdDb;
		}
		//demo
		if (self::$CLIENT_ID == self::DEMO_DEV)
		{
			return Yii::$app->demoDb;
		}
	}
	
	//reutrns the file path for the user model associated to a project based on the client header
	public static function getUserModel($client)
	{
		//CometTracker
		if($client == self::CT_DEV
		|| $client == self::CT_STAGE
		|| $client == self::CT_PROD
		|| $client == self::AZURE_CT_PROD//azure
		|| $client == self::SCCT_DEV
		|| $client == self::SCCT_STAGE
		|| $client == self::SCCT_PROD)
		{
			return self::CT_USER;
		}
		//York
		if($client == self::YORK_DEV
		||$client == self::YORK_PROD
		||$client == self::AZURE_YORK_PROD//azure
		||$client == self::YORK_STAGE)
		{
			return self::YORK_USER;
		}
		//Dominion
		if($client == self::DOMINION_PROD
		||$client == self::AZURE_DOMINION_PROD//azure
		||$client == self::DOMINION_STAGE)
		{
			return self::DOMINION_USER;
		}
		//Scana
		if($client == self::SCANA_DEV
		||$client == self::SCANA_PROD
		||$client == self::AZURE_SCANA_PROD//azure
		||$client == self::SCANA_STAGE)
		{
			return self::SCANA_USER;
		}
		//demo
		if($client == self::DEMO_DEV)
		{
			return self::DEMO_USER;
		}
		//PGE - Deos not use standard user propagation
		// if($client == self::PGE_DEV 
		// || $client == self::PGE_STAGE
		// || $client == self::PGE_PROD)
		// {
			// return self::PGE_USER;
		// }
		return null;
	}
	
	//returns the file path for the auth manager associated to a project based on the client header
	public static function getAuthManager($client)
	{
		//CometTracker
		if($client == self::CT_DEV
		|| $client == self::CT_STAGE
		|| $client == self::CT_PROD
		|| $client == self::AZURE_CT_PROD//azure
		|| $client == self::SCCT_DEV
		|| $client == self::SCCT_STAGE
		|| $client == self::SCCT_PROD)
		{
			return self::CT_AUTH;
		}
		//York
		if($client == self::YORK_DEV
		|| $client == self::YORK_PROD
		|| $client == self::AZURE_YORK_PROD//azure
		|| $client == self::YORK_STAGE)
		{
			return self::YORK_AUTH;
		}
		//Dominion
		if($client == self::DOMINION_PROD
		|| $client == self::AZURE_DOMINION_PROD//azure
		|| $client == self::DOMINION_STAGE)
		{
			return self::DOMINION_AUTH;
		}
		//Scana
		if($client == self::SCANA_DEV
		|| $client == self::SCANA_PROD
		|| $client == self::AZURE_SCANA_PROD//azure
		|| $client == self::SCANA_STAGE)
		{
			return self::SCANA_AUTH;
		}
		//demo
		if($client == self::DEMO_DEV)
		{
			return self::DEMO_AUTH;
		}
		//PGE - Deos not use standard Auth
		// if($client == self::PGE_DEV 
		// || $client == self::PGE_STAGE
		// || $client == self::PGE_PROD)
		// {
			// return self::PGE_USER;
		// }
		return null;
	}
	
	//returns the file path for the event model associated to a project based on the client header
	public static function getEventModel($client)
	{
		//CometTracker
		if($client == self::CT_DEV
		|| $client == self::CT_STAGE
		|| $client == self::CT_PROD
		|| $client == self::AZURE_CT_PROD//azure
		|| $client == self::SCCT_DEV
		|| $client == self::SCCT_STAGE
		|| $client == self::SCCT_PROD)
		{
			return self::CT_EVENT;
		}
		//York
		if($client == self::YORK_DEV
		|| $client == self::YORK_PROD
		|| $client == self::AZURE_YORK_PROD//azure
		|| $client == self::YORK_STAGE)
		{
			return self::YORK_EVENT;
		}
		//Dominion
		if($client == self::DOMINION_PROD
		|| $client == self::AZURE_DOMINION_PROD//azure
		|| $client == self::DOMINION_STAGE)
		{
			return self::DOMINION_EVENT;
		}
		//Scana
		if($client == self::SCANA_DEV
		|| $client == self::SCANA_PROD
		|| $client == self::AZURE_SCANA_PROD//azure
		|| $client == self::SCANA_STAGE)
		{
			return self::SCANA_EVENT;
		}
		//demo
		if($client == self::DEMO_DEV)
		{
			return self::DEMO_EVENT;
		}
		//PGE - Does not use standard Events
		return null;
	}
}