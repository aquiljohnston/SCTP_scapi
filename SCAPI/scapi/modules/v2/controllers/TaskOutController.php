<?php

namespace app\modules\v2\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\modules\v2\authentication\TokenAuth;
use app\modules\v2\controllers\BaseActiveController;
use app\modules\v2\models\BaseActiveRecord;
use app\modules\v2\models\TaskOut;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class TaskOutController extends Controller 
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
	
	public static function processTaskOut($data, $client, $activityID)
	{
		try
		{
			//set client header
			BaseActiveRecord::setClient($client);
			
			$taskOutCount = count($data);
			$responseArray = [];
			
			//traverse task out array
			for($i = 0; $i < $taskOutCount; $i++)
			{
				//try catch to log individual errors
				try
				{					
					$successFlag = 0;
					$comment = '';
					$taskOutID = null;
					$newTaskOut = new TaskOut;
					$newTaskOut->attributes = $data[$i];
					$newTaskOut->ActivityID = $activityID;
					
					//check if taskout already exist.
					$previousTaskOut = TaskOut::find()
						->where(['CreatedUserID' => $newTaskOut->CreatedUserID])
						->andWhere(['MapGrid' => $newTaskOut->MapGrid])
						->andWhere(['SrcDTLT' => $newTaskOut->SrcDTLT])
						->andWhere(['DeletedFlag' => 0])
						->one();

					if ($previousTaskOut == null) {
						if ($newTaskOut->save()) {
							$createdUserID = $newTaskOut->CreatedUserID;
							$mapGrid = $newTaskOut->MapGrid;
							$taskOutDateTime = $newTaskOut->SrcDTLT;
							if(BaseActiveController::isSCCT($client))
							{
								$comment = 'Task Out SP not available in base SCCT.';
							}
							else
							{
								//Call Task Out SP
								$connection = BaseActiveRecord::getDb();
								$processJSONCommand = $connection->createCommand("EXECUTE spTaskOut :UserID,:MapGrid,:TaskOutDateTime");
								$processJSONCommand->bindParam(':UserID', $createdUserID,  \PDO::PARAM_INT);
								$processJSONCommand->bindParam(':MapGrid', $mapGrid,  \PDO::PARAM_STR);
								$processJSONCommand->bindParam(':TaskOutDateTime', $taskOutDateTime,  \PDO::PARAM_STR);
								$processJSONCommand->execute();
								$taskOutID = $newTaskOut->ID;
								$successFlag = 1;
								$comment = 'Task Out SP executed.';
							}
						} else {
							throw BaseActiveController::modelValidationException($newTaskOut);
						}
					}
					else
					{
						//send success if Calibration record was already saved previously
						$taskOutID = $previousTaskOut->ID;
						$successFlag = 1;
					}
					$responseArray[] = ['ID' => $taskOutID, 'MapGrid' => $data[$i]['MapGrid'], 'SuccessFlag' => $successFlag, 'Comment' => $comment];
				}
				catch(\Exception $e)
				{
					BaseActiveController::archiveErrorJson(file_get_contents("php://input"), $e, getallheaders()['X-Client'], $data[$i]);
					$responseArray[] = ['MapGrid' => $data[$i]['MapGrid'], 'SuccessFlag' => $successFlag, 'Comment' => $comment];
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