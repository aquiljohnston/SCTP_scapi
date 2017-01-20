<?php

namespace app\modules\v1\models;

use Yii;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	private static $CLIENT_ID = '';
	
	//base comet tracker databases
	const CT_DEV = 'apidev';	
	const CT_STAGE = 'apistage';
	const CT_PROD = 'api';
	
	//pg&e databases
	const PGE_DEV = 'pgedev';
	const PGE_STAGE = 'pgestage';
	const PGE_PROD = 'pge';
	
	//beta client database
	const BETA = 'beta';
	
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
		if (self::$CLIENT_ID == self::CT_DEV)
		{
			return Yii::$app->ctDevDb;
		}
		if (self::$CLIENT_ID == self::PGE_DEV)
		{
			return Yii::$app->pgeDevDb;
		}
		if (self::$CLIENT_ID == self::CT_STAGE)
		{
			return Yii::$app->ctStageDb;
		}
		if (self::$CLIENT_ID == self::PGE_STAGE)
		{
			return Yii::$app->pgeStageDb;
		}
		if (self::$CLIENT_ID == self::CT_PROD)
		{
			return Yii::$app->ctProdDb;
		}
		if (self::$CLIENT_ID == self::PGE_PROD)
		{
			return Yii::$app->pgeProdDb;
		}
		if (self::$CLIENT_ID == self::BETA)
		{
			return Yii::$app->betaDb;
		}
	}
}