<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use app\modules\v1\models\BaseActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use app\modules\v1\modules\pge\models\WindSpeed;

class WindSpeedController extends Controller 
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
                ],  
            ];
		return $behaviors;	 
	}
	
	//Parses Wind Speed Array from the activity json and stores data into the table.
	public static function create($windSpeedArray, $client, $userUID)
	{
		try
		{
			//set db target
			BaseActiveRecord::setClient($client);
			
			$savedData = [];

			//Indications
			if($windSpeedArray != null)
			{
				//loop wind speed entries
				$windSpeedCount = (count($windSpeedArray));
				for ($i = 0; $i < $windSpeedCount; $i++)
				{
					//new WindSpeed model
					$windSpeed = new WindSpeed();
					//pass data to model
					$windSpeed->attributes = $windSpeedArray[$i];
					//additional fields
					$windSpeed->CreatedUserUID = $userUID;
					$windSpeed->ModifiedUserUID = $userUID;
					
					//save model
					if($windSpeed->save())
					{
						//add to response array
						$savedData[] = $windSpeed;
					}
					else
					{
						$savedData[] = 'Failed to Save Wind Speed Record';
					}
				}
			}
			return $savedData;		
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
        catch(\Exception $e)
        {
            throw new \yii\web\HttpException(400);
        }
	}
}