<?php

namespace app\modules\v2\models;

use Yii;
use app\modules\v2\constants\Constants;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	private static $CLIENT_ID = '';
	
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
		$clientConfigObj = self::getClientObj(self::$CLIENT_ID);
		
		//try and do this in a better way to avoid a second switch if possible
		switch(self::$CLIENT_ID){
			case array_key_exists('DEV_HEADER', $clientConfigObj) ? $clientConfigObj['DEV_HEADER']: null:
				return Yii::$app->{$clientConfigObj['DEV_DB']};
			case array_key_exists('STAGE_HEADER', $clientConfigObj) ? $clientConfigObj['STAGE_HEADER']: null:
				return Yii::$app->{$clientConfigObj['STAGE_DB']};
			case array_key_exists('PROD_HEADER', $clientConfigObj) ? $clientConfigObj['PROD_HEADER']: null:
			case array_key_exists('AZURE_PROD_HEADER', $clientConfigObj) ? $clientConfigObj['AZURE_PROD_HEADER']: null:
				return Yii::$app->{$clientConfigObj['PROD_DB']};		
		}
	}
	
	//returns the file path for the user model associated to a project based on the client header
	public static function getUserModel($client)
	{
		return self::getClientObj($client)['USER'];
	}
	
	//returns the file path for the auth manager associated to a project based on the client header
	public static function getAuthManager($client)
	{
		return self::getClientObj($client)['AUTH'];
	}
	
	//returns the file path for the event model associated to a project based on the client header
	public static function getEventModel($client)
	{
		return self::getClientObj($client)['EVENT'];
	}
	
	//returns the file path for the asset model associated to a project based on the client header
	public static function getAssetModel($client)
	{
		return self::getClientObj($client)['ASSET'];
	}
	
	//returns the file path for the taskout model associated to a project based on the client header
	public static function getTaskOutModel($client)
	{
		return self::getClientObj($client)['TASKOUT'];
	}
	
	private static function getClientObj($client)
	{
		//matches given client to associated client configuration 
		switch($client){
			//API
			case Constants::API_CONFIG['DEV_HEADER']:
			case Constants::API_CONFIG['STAGE_HEADER']:
			case Constants::API_CONFIG['PROD_HEADER']:
			case Constants::API_CONFIG['AZURE_PROD_HEADER']:
				return Constants::API_CONFIG;
			//SCCT
			case Constants::SCCT_CONFIG['DEV_HEADER']:
			case Constants::SCCT_CONFIG['STAGE_HEADER']:
			case Constants::SCCT_CONFIG['PROD_HEADER']:
				return Constants::SCCT_CONFIG;
			//SCANA
			case Constants::SCANA_CONFIG['DEV_HEADER']:
			case Constants::SCANA_CONFIG['STAGE_HEADER']:
			case Constants::SCANA_CONFIG['PROD_HEADER']:
				return Constants::SCANA_CONFIG;
			//DEMO
			case Constants::DEMO_CONFIG['DEV_HEADER']:
				return Constants::DEMO_CONFIG;
		}
	}
}