<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\base\ErrorException;
use app\modules\v3\constants\Constants;
use app\modules\v3\controllers\BaseActiveController;
use app\modules\v3\controllers\TaskController;
use app\modules\v3\models\BaseActiveRecord;
use app\modules\v3\models\SCUser;
use app\modules\v3\models\TimeCard;
use app\modules\v3\models\Holidays;
	
class HolidayPayController extends Controller
{
    /**
     * Purpose of this file is to generate new holiday pay time entries for qualified employees.
    **/
    
	//Cmd: yii holiday-pay/create scctdev "2020-06-28 22:00:00.000"(optional)
	public function actionCreate($client, $datetime = null){
		try{
			//set db target
			BaseActiveRecord::setClient($client);
			//create transaction
			$connection = BaseActiveRecord::getDb();
			$transaction = $connection->beginTransaction();
			//get datetime
			if($datetime == null){
				$datetime = BaseActiveController::getDate();
			}
			//get date part of datetime for week begin and end
			$sundayDate = date('Y-m-d', strtotime($datetime));
			$saturdayDate = date('Y-m-d', strtotime($datetime. ' + 6 days'));
			echo nl2br("Sunday Date: " . $sundayDate . "\n");
			echo nl2br("Saturday Date: " . $saturdayDate . "\n");
			//get all holidays for the current week
			$holidays = Holidays::find()
				->where(['>=', '[HolidayDate]', $sundayDate])
				->andWhere(['<=', '[HolidayDate]', $saturdayDate])
				->all();
			//loop days
			foreach($holidays as $day){
				echo nl2br("Holidays: " . json_encode($day->attributes) . "\n");
				//get 60 day prior to holiday cutoff
				$qualifiedDate = date('Y-m-d', strtotime($day->HolidayDate. ' - 60 days'));
				echo nl2br("Qualified Date: " . json_encode($qualifiedDate) . "\n");
				//get all qualified users who are both full time and whose hire date is 60 days before holiday
				$qualifiedUsers = SCUser::find()
					->select(['UserID','UserCreatedDate'])
					//->where(fulltime) using temp check for testing until real value is available
					->where(['UserPayMethod' => 'H'])
					//->andWhere(hiredate>=qualifiedDate) using temp check for testing until real value is available
					->andWhere(['<=', '[UserCreatedDate]', $qualifiedDate])
					->andWhere(['UserAppRoleType' => 'Technician'])
					->andWhere(['UserActiveFlag' => 1])
					//limit data set for testing
					->andWhere(['in', 'UserID', [4857, 1481]])
					->asArray()
					->all();				
				//loop users
				foreach($qualifiedUsers as $user){
					echo nl2br("UserID: " . json_encode($user['UserID']) . "\n");
					//get timecard id for most recent timecard user has recorded work against
					$projectID = TimeCard::find()
						->select(['[TimeCardTb].[TimeCardProjectID]', '[TimeEntryTb].[TimeEntryEndTime]'])
						->distinct()
						->innerJoin('TimeEntryTb', '[TimeEntryTb].[TimeEntryTimeCardID] = [TimeCardTb].[TimeCardID]')
						->innerJoin('ActivityTb', '[ActivityTb].[ActivityID] = [TimeEntryTb].[TimeEntryActivityID]')
						->where(['TimeCardTechID' => $user['UserID']])
						->andWhere([
							'and',
							[
								'or',
								['like', 'ActivityTitle', 'Task'],
								['ActivityTitle' => 'AdminActivity']
							],
							['not like', 'ActivityTitle', 'TaskOut']
						])
						->orderBy('[TimeEntryTb].[TimeEntryEndTime] DESC')
						->asArray()
						->limit(1)
						->one();
					echo nl2br("Project ID: " . json_encode($projectID['TimeCardProjectID']) . "\n");
					$timeCardID = TimeCard::find()
						->where(['TimeCardTechID' => $user['UserID']])
						->andWhere(['TimeCardProjectID' => $projectID['TimeCardProjectID']])
						->andWhere(['TimeCardStartDate' => $sundayDate])
						->andWhere(['TimeCardEndDate' => $saturdayDate])
						->asArray()
						->one();
					echo nl2br("Time Card ID: " . json_encode($timeCardID['TimeCardID']) . "\n");
					//build data package for time creation
					$data = [
						'TimeCardID' => $timeCardID['TimeCardID'], 
						'TaskName' => 'Task OTHER',
						'Date' => $day->HolidayDate,
						'StartTime' => '08:00:00', //8am
						'EndTime' => '16:00:00', // 4pm
						'CreatedByUserName' => 'Automation',
						'ChargeOfAccountType' => Constants::HOLIDAY_BEREAVEMENT_PAYROLL_HOURS_ID,
						'TimeReason' => Constants::TIME_REASON_PTO_HOLIDAY
					];
					//call function to create holiday record
					$results = TaskController::addActivityAndTime($data);
					
					if($results['successFlag'] ==0){
						$e = new ErrorException($results['warningMessage'], 42, 2);
						BaseActiveController::archiveErrorJson('Holiday Pay Generation Overlap', $e, $client, $data);
					}
				}
			}
			$transaction->commit();
			echo nl2br("TimeTracker Holiday Pay Generation Complete.\n");
		} catch (\Exception $e) { 
			$transaction->rollback();
			throw $e;
			BaseActiveController::archiveErrorJson('Holiday Pay Generation Script', $e, $client);
			echo nl2br("TimeTracker Holiday Pay Error.\n");
		}
	}
}
?>
