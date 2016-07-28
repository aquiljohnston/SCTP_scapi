<?php

namespace app\modules\v1\models;

use Yii;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	
	private static $CLIENT_ID = 0;
	private static $DEFAULT_DB = 'CometTracker';	
	private static $SCANA_DB = 'CT_SCANA';
	private static $DEFAULT_QA_DB = 'CometTrackerQA';
	private static $SCANA_QA_DB = 'CT_SCANAQA';
	
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
		if (self::$CLIENT_ID == self::$DEFAULT_DB)
		{
			return Yii::$app->db;
		}
		if (self::$CLIENT_ID == self::$DEFAULT_QA_DB)
		{
			return Yii::$app->dbQA;
		}
		if (self::$CLIENT_ID == self::$SCANA_DB)
		{
			return Yii::$app->scanaDb;
		}
		if (self::$CLIENT_ID == self::$SCANA_QA_DB)
		{
			return Yii::$app->scanaQADb;
		}
		
	}
}