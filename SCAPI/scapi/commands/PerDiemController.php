<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\base\ErrorException;
use app\modules\v3\constants\Constants;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\SCUser;
use app\modules\v3\models\Expense;
use app\modules\v3\models\TimeCard;
	
class PerDiemController extends Controller
{
    /**
     * Purpose of this file is to generate new daily per diem records for contracted employees.
    **/
    
	//Cmd: yii per-diem/create scctdev
	public function actionCreate($client){
		try{
			//set db target
			BaseActiveRecord::setClient($client);
			//create transaction
			$connection = BaseActiveRecord::getDb();
			$transaction = $connection->beginTransaction();
			//get all contracted user's data and per diem rate and number of days
			$contractedUsers = SCUser::find()
				->select(['[UserTb].[UserID]', '[UserTb].[UserName]', '[refPerDiem].[Rate]', '[refPerDiem].[No Of Days]'])
				->innerJoin('refPerDiem', '[refPerDiem].[ID] = [UserTb].[Division]')
				->asArray()
				->all();
			//get date
			$date = BaseActiveController::getDate();
			//loop users
			foreach($contractedUsers as $user){
				//get project id for all projects user has recorded work against this week
				$timeCardProjectID = TimeCard::find()
					->select(['[TimeCardTb].[TimeCardProjectID]'])
					->distinct()
					->innerJoin('TimeEntryTb', '[TimeEntryTb].[TimeEntryTimeCardID] = [TimeCardTb].[TimeCardID]')
					->innerJoin('ActivityTb', '[ActivityTb].[ActivityID] = [TimeEntryTb].[TimeEntryActivityID]')
					->where(['TimeCardTechID' => $user['UserID']])
					->andWhere(['<=', '[TimeCardStartDate]', $date])
					->andWhere(['>=', '[TimeCardEndDate]', $date])
					->andWhere([
						'and',
						[
							'or',
							['like', 'ActivityTitle', 'Task'],
							['ActivityTitle' => 'AdminActivity']
						],
						['not like', 'ActivityTitle', 'TaskOut']
					])
					->asArray()
					->all();
				//loop projects and create per diem record
				foreach($timeCardProjectID as $projectID){
					//populate perdiem records
					$perDiem = new Expense;
					$perDiem->Username = $user['UserName'];
					$perDiem->CreatedDate = $date;
					$perDiem->CreatedDateTime = $date;
					$perDiem->Quantity = $user['Rate'] * $user['No Of Days'];
					$perDiem->ChargeAccount = Constants::PERDIEM_EXPENSE_ID;
					$perDiem->ProjectID = $projectID['TimeCardProjectID'];
					$perDiem->UserID = $user['UserID'];
					//save per diem
					if(!$perDiem->save()){
						throw BaseActiveController::modelValidationException($perDiem);
					}
				}
			}
			$transaction->commit();
			echo nl2br("TimeTracker Per Diem Generation Complete.\n");
		} catch (\Exception $e) { 
			$transaction->rollback();
			BaseActiveController::archiveErrorJson('PerDiem Generation Script', $e, $client);
			echo nl2br("TimeTracker Per Diem Error.\n");
		}
	}
}
?>
