<?php

namespace app\controllers;

use Yii;
use app\models\Equipment;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class EquipmentController extends \yii\web\Controller
{
	public $equipment;
	
     /* public function actionIndex()
     {
         return $this->render('index');
     }
	 */
	 public function actionCreate()
    {
        $equipment = new Equipment();
		$equipment['EquipmentID'] = 7;
		$equipment['EquipmentName'] = "Flame Pack 400";
		$equipment['EquipmentSerialNumber'] = "123454322";
		
		$equipment->save();
		echo "Equipment model: <br />" . "Name: " . $equipment['EquipmentName'];
    }
	
	 public function actionView($id)
    {
		$equipment = Equipment::findOne($id);
		$arrayequipment = (array) $equipment;
		return json_encode($arrayequipment);
	}
	
	public function actionUpdate()
    {
		$tempEquipment = Equipment::findOne(7);
		$tempEquipment['EquipmentProjectID'] = 1;
		$tempEquipment->save();
    }
	
	public function actionDelete($id)
    {
        $equipment = Equipment::findOne($id);
		$equipment -> delete(); 
    }
}
