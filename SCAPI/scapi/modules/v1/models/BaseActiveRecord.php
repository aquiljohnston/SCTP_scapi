<?php

namespace app\modules\v1\models;

use Yii;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	private static $CLIENT_ID = '';
	
	//scct databases
	const SCCT_DEV = 'scctdev';
	const SCCT_STAGE = 'scctstage';
	const SCCT_PROD = 'scct';
	
	//base comet tracker databases
	const CT_DEV = 'apidev';	
	const CT_STAGE = 'apistage';
	const CT_PROD = 'api';
	
	//pg&e databases
	const PGE_DEV = 'pgedev';
	const PGE_STAGE = 'pgestage';
	const PGE_PROD = 'pge';
	
	//beta client database
	const BETA_DEV = 'betadev';
	
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
		//beta
		if (self::$CLIENT_ID == self::BETA_DEV)
		{
			return Yii::$app->betaDb;
		}
	}
}