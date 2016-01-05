<?php

namespace app\controllers;

use Yii;
use yii\base\Security;
use yii\web\Controller;

class HandlePasswordController extends Controller
{

	public function actionSecurePassword($password)
	{
		$iv = "abcdefghijklmnop";
		$key = "sparusholdings12";
		//$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
		//$hash = password_hash($password, PASSWORD_BCRYPT,$options);
		//$encryptedPass = Yii::$app->getSecurity()->encryptByPassword($hash, $key);
		//$encryptedPass = Yii::$app->getSecurity()->encryptByPassword($password, $key);
		$encryptedPass = openssl_encrypt($password,  'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
		//echo ($hash);
		//echo ($encryptedPass);
		//echo base64_encode($hash);
		echo base64_encode($encryptedPass);
	}
	
	//public function action

}