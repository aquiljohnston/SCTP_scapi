<?php

namespace app\modules\v1\models;

use Yii;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	private static $CLIENT_ID = '';
	
	//base user
	const BASE_USER = 'app\modules\v1\models\BaseUser';
	
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
	//comet tracker user
	const CT_USER = 'app\modules\v1\models\SCUser';
	//comet tracker auth manager
	const CT_AUTH = 'app\rbac\ScDbManager';
	
	//pg&e databases
	const PGE_DEV = 'pgedev';
	const PGE_STAGE = 'pgestage';
	const PGE_PROD = 'pge';
	//pg&e user model
	const PGE_USER = 'app\modules\v1\modules\pge\models\PGEUser';
	//pg&e auth manager
	const PGE_AUTH = 'app\rbac\PgeDbManager';
	
	//york databases
	const YORK_DEV = 'yorkdev';
	//york user model
	const YORK_USER = self::BASE_USER;
	//york auth manager
	const YORK_AUTH = 'app\rbac\YorkDbManager';
	
	//beta client database
	const BETA_DEV = 'betadev';
	//beta user model
	const BETA_USER = self::BASE_USER;
	//beta auth manager
	const BETA_AUTH = 'app\rbac\BetaDbManager';
	
	//azure test databases
	const AZURE_BASE_TEST = 'azureapidev';
	//beta models
	const AZURE_BASE_USER = 'app\modules\v1\models\SCUser';
	//beta auth manager
	const AZURE_BASE_AUTH = 'app\rbac\ScDbManager';
	
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
		if (self::$CLIENT_ID == self::CT_PROD || self::$CLIENT_ID == self::SCCT_PROD)
		{
			return Yii::$app->ctProdDb;
		}
			//azure
			if (self::$CLIENT_ID == self::AZURE_CT_PROD)
			{
				return Yii::$app->azureProdDb;
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
		//beta
		if (self::$CLIENT_ID == self::BETA_DEV)
		{
			return Yii::$app->betaDb;
		}
		//Azure
		if (self::$CLIENT_ID == self::AZURE_BASE_TEST)
		{
			return Yii::$app->azureBaseDb;
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
		if($client == self::YORK_DEV)
		{
			return self::YORK_USER;
		}
		//Beta
		if($client == self::BETA_DEV)
		{
			return self::BETA_USER;
		}
		//Azure
		if($client == self::AZURE_BASE_TEST)
		{
			return self::AZURE_BASE_USER;
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
		if($client == self::YORK_DEV)
		{
			return self::YORK_AUTH;
		}
		//Beta
		if($client == self::BETA_DEV)
		{
			return self::BETA_AUTH;
		}
		//Azure
		if($client == self::AZURE_BASE_TEST)
		{
			return self::AZURE_BASE_AUTH;
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
}