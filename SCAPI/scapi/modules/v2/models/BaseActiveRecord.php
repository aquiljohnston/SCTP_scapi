<?php

namespace app\modules\v2\models;

use Yii;
use app\modules\v2\constants\Constants;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	private static $CLIENT_ID = '';
	
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
		//azure
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
	const SCANA_PROD = 'scana';
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
		if (self::$CLIENT_ID == Constants::CT_DEV || self::$CLIENT_ID == Constants::SCCT_DEV)
		{
			return Yii::$app->ctDevDb;
		}
		if (self::$CLIENT_ID == Constants::CT_STAGE || self::$CLIENT_ID == Constants::SCCT_STAGE)
		{
			return Yii::$app->ctStageDb;
		}
		if (self::$CLIENT_ID == Constants::CT_PROD || self::$CLIENT_ID == Constants::SCCT_PROD || self::$CLIENT_ID == Constants::AZURE_CT_PROD)
		{
			return Yii::$app->ctProdDb;
		}
			//azure
			if (self::$CLIENT_ID == self::AZURE_CT_PROD)
			{
				return Yii::$app->azureProdDb;
			}
		//pge
		if (self::$CLIENT_ID == Constants::PGE_DEV)
		{
			return Yii::$app->pgeDevDb;
		}
		if (self::$CLIENT_ID == Constants::PGE_STAGE)
		{
			return Yii::$app->pgeStageDb;
		}
		if (self::$CLIENT_ID == Constants::PGE_PROD)
		{
			return Yii::$app->pgeProdDb;
		}
		//york
		if (self::$CLIENT_ID == Constants::YORK_DEV)
		{
			return Yii::$app->yorkDevDb;
		}
		if (self::$CLIENT_ID == Constants::YORK_STAGE)
		{
			return Yii::$app->yorkStageDb;
		}
		if (self::$CLIENT_ID == Constants::YORK_PROD || self::$CLIENT_ID == Constants::AZURE_YORK_PROD)
		{
			return Yii::$app->yorkProdDb;
		}
			//azure
			if (self::$CLIENT_ID == self::AZURE_YORK_PROD)
			{
				return Yii::$app->azureYorkProdDb;
			}
		//dominion
		if (self::$CLIENT_ID == Constants::DOMINION_STAGE)
		{
			return Yii::$app->dominionStageDb;
		}
		if (self::$CLIENT_ID == Constants::DOMINION_PROD || self::$CLIENT_ID == Constants::AZURE_DOMINION_PROD)
		{
			return Yii::$app->dominionProdDb;
		}
			//azure
			if (self::$CLIENT_ID == self::AZURE_DOMINION_PROD)
			{
				return Yii::$app->azureDeoProdDb;
			}
		//scana
		if (self::$CLIENT_ID == Constants::SCANA_DEV)
		{
			return Yii::$app->scanaDevDb;
		}
		if (self::$CLIENT_ID == Constants::SCANA_STAGE)
		{
			return Yii::$app->scanaStageDb;
		}
		// if (self::$CLIENT_ID == Constants::SCANA_PROD)
		// {
			// return Yii::$app->scanaProdDb;
		// }
			//azure
			if (self::$CLIENT_ID == self::AZURE_SCANA_PROD)
			{
				return Yii::$app->azureScanaProdDb;
			}
		//demo
		if (self::$CLIENT_ID == Constants::DEMO_DEV)
		{
			return Yii::$app->demoDb;
		}
	}
	
	//reutrns the file path for the user model associated to a project based on the client header
	public static function getUserModel($client)
	{
		//CometTracker
		if($client == Constants::CT_DEV
		|| $client == Constants::CT_STAGE
		|| $client == Constants::CT_PROD
		|| $client == Constants::AZURE_CT_PROD//azure
		|| $client == Constants::SCCT_DEV
		|| $client == Constants::SCCT_STAGE
		|| $client == Constants::SCCT_PROD)
		{
			return Constants::CT_USER;
		}
		//York
		if($client == Constants::YORK_DEV
		||$client == Constants::YORK_PROD
		||$client == Constants::AZURE_YORK_PROD//azure
		||$client == Constants::YORK_STAGE)
		{
			return Constants::YORK_USER;
		}
		//Dominion
		if($client == Constants::DOMINION_PROD
		||$client == Constants::AZURE_DOMINION_PROD//azure
		||$client == Constants::DOMINION_STAGE)
		{
			return Constants::DOMINION_USER;
		}
		//Scana
		if($client == Constants::SCANA_DEV
		||$client == Constants::SCANA_PROD
		||$client == Constants::AZURE_SCANA_PROD//azure
		||$client == Constants::SCANA_STAGE)
		{
			return Constants::SCANA_USER;
		}
		//demo
		if($client == Constants::DEMO_DEV)
		{
			return Constants::DEMO_USER;
		}
		return null;
	}
	
	//returns the file path for the auth manager associated to a project based on the client header
	public static function getAuthManager($client)
	{
		//CometTracker
		if($client == Constants::CT_DEV
		|| $client == Constants::CT_STAGE
		|| $client == Constants::CT_PROD
		|| $client == Constants::AZURE_CT_PROD//azure
		|| $client == Constants::SCCT_DEV
		|| $client == Constants::SCCT_STAGE
		|| $client == Constants::SCCT_PROD)
		{
			return Constants::CT_AUTH;
		}
		//York
		if($client == Constants::YORK_DEV
		|| $client == Constants::YORK_PROD
		|| $client == Constants::AZURE_YORK_PROD//azure
		|| $client == Constants::YORK_STAGE)
		{
			return Constants::YORK_AUTH;
		}
		//Dominion
		if($client == Constants::DOMINION_PROD
		|| $client == Constants::AZURE_DOMINION_PROD//azure
		|| $client == Constants::DOMINION_STAGE)
		{
			return Constants::DOMINION_AUTH;
		}
		//Scana
		if($client == Constants::SCANA_DEV
		|| $client == Constants::SCANA_PROD
		|| $client == Constants::AZURE_SCANA_PROD//azure
		|| $client == Constants::SCANA_STAGE)
		{
			return Constants::SCANA_AUTH;
		}
		//demo
		if($client == Constants::DEMO_DEV)
		{
			return Constants::DEMO_AUTH;
		}
		return null;
	}
	
	//returns the file path for the event model associated to a project based on the client header
	public static function getEventModel($client)
	{
		//CometTracker
		if($client == Constants::CT_DEV
		|| $client == Constants::CT_STAGE
		|| $client == Constants::CT_PROD
		|| $client == Constants::AZURE_CT_PROD//azure
		|| $client == Constants::SCCT_DEV
		|| $client == Constants::SCCT_STAGE
		|| $client == Constants::SCCT_PROD)
		{
			return Constants::CT_EVENT;
		}
		//York
		if($client == Constants::YORK_DEV
		|| $client == Constants::YORK_PROD
		|| $client == Constants::AZURE_YORK_PROD//azure
		|| $client == Constants::YORK_STAGE)
		{
			return Constants::YORK_EVENT;
		}
		//Dominion
		if($client == Constants::DOMINION_PROD
		|| $client == Constants::AZURE_DOMINION_PROD//azure
		|| $client == Constants::DOMINION_STAGE)
		{
			return Constants::DOMINION_EVENT;
		}
		//Scana
		if($client == Constants::SCANA_DEV
		|| $client == Constants::SCANA_PROD
		|| $client == Constants::AZURE_SCANA_PROD//azure
		|| $client == Constants::SCANA_STAGE)
		{
			return Constants::SCANA_EVENT;
		}
		//demo
		if($client == Constants::DEMO_DEV)
		{
			return Constants::DEMO_EVENT;
		}
		//PGE - Does not use standard Events
		return null;
	}
	
	//TODO combine with event model function
	//returns the file path for the asset model associated to a project based on the client header
	public static function getAssetModel($client)
	{
		//CometTracker
		if($client == Constants::CT_DEV
		|| $client == Constants::CT_STAGE
		|| $client == Constants::CT_PROD
		|| $client == Constants::SCCT_DEV
		|| $client == Constants::SCCT_STAGE
		|| $client == Constants::SCCT_PROD)
		{
			return Constants::CT_ASSET;
		}
		//York
		if($client == Constants::YORK_DEV
		|| $client == Constants::YORK_PROD
		|| $client == Constants::YORK_STAGE)
		{
			return Constants::YORK_ASSET;
		}
		//Dominion
		if($client == Constants::DOMINION_PROD
		|| $client == Constants::DOMINION_STAGE)
		{
			return Constants::DOMINION_ASSET;
		}
		//Scana
		if($client == Constants::SCANA_DEV
		|| $client == Constants::SCANA_PROD
		|| $client == Constants::SCANA_STAGE)
		{
			return Constants::SCANA_ASSET;
		}
		//demo
		if($client == Constants::DEMO_DEV)
		{
			return Constants::DEMO_ASSET;
		}
		return null;
	}
}