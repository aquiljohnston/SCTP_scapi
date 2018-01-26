<?php

namespace app\modules\v2\models;

use Yii;
use app\modules\v2\constants\Constants;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	private static $CLIENT_ID = '';
	
	//TODO: create object/array for all clients and refactor get methods(exclude getDb) into single function
	//that takes in client and model to retrive that will be based on client object keys
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
		//dominion
		if (self::$CLIENT_ID == Constants::DOMINION_STAGE)
		{
			return Yii::$app->dominionStageDb;
		}
		if (self::$CLIENT_ID == Constants::DOMINION_PROD || self::$CLIENT_ID == Constants::AZURE_DOMINION_PROD)
		{
			return Yii::$app->dominionProdDb;
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
		//demo
		if (self::$CLIENT_ID == Constants::DEMO_DEV)
		{
			return Yii::$app->demoDb;
		}
		//azure test
		if (self::$CLIENT_ID == Constants::AZURE_TEST)
		{
			return Yii::$app->azureDb;
		}
	}
	
	//reutrns the file path for the user model associated to a project based on the client header
	public static function getUserModel($client)
	{
		//CometTracker
		if($client == Constants::CT_DEV
		|| $client == Constants::CT_STAGE
		|| $client == Constants::CT_PROD
		|| $client == Constants::SCCT_DEV
		|| $client == Constants::SCCT_STAGE
		|| $client == Constants::SCCT_PROD)
		{
			return Constants::CT_USER;
		}
		//York
		if($client == Constants::YORK_DEV
		||$client == Constants::YORK_PROD
		||$client == Constants::YORK_STAGE)
		{
			return Constants::YORK_USER;
		}
		//Dominion
		if($client == Constants::DOMINION_PROD
		||$client == Constants::DOMINION_STAGE)
		{
			return Constants::DOMINION_USER;
		}
		//Scana
		if($client == Constants::SCANA_DEV
		||$client == Constants::SCANA_PROD
		||$client == Constants::SCANA_STAGE)
		{
			return Constants::SCANA_USER;
		}
		//demo
		if($client == Constants::DEMO_DEV)
		{
			return Constants::DEMO_USER;
		}
		//azure test
		if($client == Constants::AZURE_TEST)
		{
			return Constants::AZURE_USER;
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
		|| $client == Constants::SCCT_DEV
		|| $client == Constants::SCCT_STAGE
		|| $client == Constants::SCCT_PROD)
		{
			return Constants::CT_AUTH;
		}
		//York
		if($client == Constants::YORK_DEV
		|| $client == Constants::YORK_PROD
		|| $client == Constants::YORK_STAGE)
		{
			return Constants::YORK_AUTH;
		}
		//Dominion
		if($client == Constants::DOMINION_PROD
		|| $client == Constants::DOMINION_STAGE)
		{
			return Constants::DOMINION_AUTH;
		}
		//Scana
		if($client == Constants::SCANA_DEV
		|| $client == Constants::SCANA_PROD
		|| $client == Constants::SCANA_STAGE)
		{
			return Constants::SCANA_AUTH;
		}
		//demo
		if($client == Constants::DEMO_DEV)
		{
			return Constants::DEMO_AUTH;
		}
		//azure test
		if($client == Constants::AZURE_TEST)
		{
			return Constants::AZURE_AUTH;
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
		|| $client == Constants::SCCT_DEV
		|| $client == Constants::SCCT_STAGE
		|| $client == Constants::SCCT_PROD)
		{
			return Constants::CT_EVENT;
		}
		//York
		if($client == Constants::YORK_DEV
		|| $client == Constants::YORK_PROD
		|| $client == Constants::YORK_STAGE)
		{
			return Constants::YORK_EVENT;
		}
		//Dominion
		if($client == Constants::DOMINION_PROD
		|| $client == Constants::DOMINION_STAGE)
		{
			return Constants::DOMINION_EVENT;
		}
		//Scana
		if($client == Constants::SCANA_DEV
		|| $client == Constants::SCANA_PROD
		|| $client == Constants::SCANA_STAGE)
		{
			return Constants::SCANA_EVENT;
		}
		//demo
		if($client == Constants::DEMO_DEV)
		{
			return Constants::DEMO_EVENT;
		}
		//azure test
		if($client == Constants::AZURE_TEST)
		{
			return Constants::AZURE_EVENT;
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