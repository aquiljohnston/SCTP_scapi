<?php

namespace app\models;

use Yii;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
	
	private static $clientID = 0;
	
	public static function getClient()
	{
		return self::$clientID;
	}

	public static function setClient($id)
	{
		self::$clientID = $id;
	}
	
	public static function getDb()
	{
		if (self::$clientID == 0)
		{
			return Yii::$app->db;
		}
		if (self::$clientID == 1)
		{
			return Yii::$app->scanaQADb;
		}
	}
}