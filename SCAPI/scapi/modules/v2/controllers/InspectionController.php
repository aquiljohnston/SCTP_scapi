<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\Inspection;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class InspectionController extends Controller 
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					//'create' => ['post'],
                ],  
            ];
		return $behaviors;	
	}
	
	public static function processInspection($data, $client, $activityID)
	{
		try
		{
			//set client header
			BaseActiveRecord::setClient($client);
			
			$inspectionCount = count($data);
			$responseArray = [];
			
			//traverse Inspection array
			for($i = 0; $i < $inspectionCount; $i++)
			{
				//try catch to log individual errors
				try
				{					
					$newInspection = new Inspection;
					$newInspection->attributes = $data[$i];
					$newInspection->ActivityID = $activityID;
					
					//check if pge Inspection already exist.
					$previousInspection = Inspection::find()
						->where(['InspectionTabletID' => $newInspection->InspectionTabletID])
						//->andWhere(['DeletedFlag' => 0]) no flag exist currently
						->one();

					if ($previousInspection == null) {
						if ($newInspection->save()) {
							$responseArray[] = ['ID' => $newInspection->ID, 'InspectionTabletID' => $newInspection->InspectionTabletID, 'SuccessFlag' => 1];
						} else {
							throw BaseActiveController::modelValidationException($newInspection);
						}
					}
					else
					{
						//Handle responses if applicable.
						//send success if Inspection record was already saved previously
						$responseArray[] = ['ID' => $previousInspection->ID, 'InspectionTabletID' => $newInspection->InspectionTabletID, 'SuccessFlag' => 1];
					}
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i]);
					$responseArray[] = ['InspectionTabletID' => $data[$i]['InspectionTabletID'],'SuccessFlag' => 0];
				}
			}
			//return response data
			return $responseArray;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
			BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client']);
            throw new \yii\web\HttpException(400);
        }
	}
}