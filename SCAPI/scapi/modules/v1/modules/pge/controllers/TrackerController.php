<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\data\Pagination;
use app\modules\v1\modules\pge\models\WebManagementTrackerCurrentLocation;
use app\modules\v1\modules\pge\models\WebManagementTrackerHistory;

class TrackerController extends Controller 
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
                    'get' => ['get'],
                    'get-recent-activity' => ['get'],
                    'get-history' => ['get']
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGet($employee = null, $trackingGroup = null, $deviceID = null, $startDate, $endDate/*, $distanceFactor, $timeFactor*/)
	{
		try
		{
			$record1 = [];
			$record1["Date/Time"] = "05/10/2016 19:20:10";
			$record1["Surveyor"] = "John Doe";
			$record1["Latitude"] = "33.96323806";
			$record1["Longitude"] = "29.2019515";
			$record1["Speed"] = "2";
			$record1["Heading"] = "N";
			$record1["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record1["State"] = "GA";
			$record1["Zip Code"] = "30071-1557";
			$record1["NumStats"] = "9";
			$record1["Pos Scr"] = "G";
			$record1["Landmark"] = "";
			$record1["Accuracy"] = "40";
			$record1["Tracking Group"] = "Diablo";
			$record1["Device ID"] = "12345678";
			
			$record2 = [];
			$record2["Date/Time"] = "05/09/2016 15:24:32";
			$record2["Surveyor"] = "Jane Doe";
			$record2["Latitude"] = "33.96323806";
			$record2["Longitude"] = "29.2019515";
			$record2["Speed"] = "3";
			$record2["Heading"] = "NW";
			$record2["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record2["State"] = "GA";
			$record2["Zip Code"] = "30071-1557";
			$record2["NumStats"] = "5";
			$record2["Pos Scr"] = "G";
			$record2["Landmark"] = "";
			$record2["Accuracy"] = "40";
			$record2["Tracking Group"] = "Diablo";
			$record2["Device ID"] = "87654321";
			
			$record3 = [];
			$record3["Date/Time"] = "05/12/2016 07:53:45";
			$record3["Surveyor"] = "Bob Smith";
			$record3["Latitude"] = "33.96323806";
			$record3["Longitude"] = "29.2019515";
			$record3["Speed"] = "6";
			$record3["Heading"] = "E";
			$record3["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record3["State"] = "GA";
			$record3["Zip Code"] = "30071-1557";
			$record3["NumStats"] = "7";
			$record3["Pos Scr"] = "G";
			$record3["Landmark"] = "";
			$record3["Accuracy"] = "40";
			$record3["Tracking Group"] = "Azmodan";
			$record3["Device ID"] = "13572468";
			
			$record4 = [];
			$record4["Date/Time"] = "05/11/2016 13:17:25";
			$record4["Surveyor"] = "Fred Milstone";
			$record4["Latitude"] = "33.96323806";
			$record4["Longitude"] = "29.2019515";
			$record4["Speed"] = "9";
			$record4["Heading"] = "SW";
			$record4["Street/City"] = "Corners North Ct, Peachtree Corners";
			$record4["State"] = "GA";
			$record4["Zip Code"] = "30071-1557";
			$record4["NumStats"] = "6";
			$record4["Pos Scr"] = "G";
			$record4["Landmark"] = "";
			$record4["Accuracy"] = "40";
			$record4["Tracking Group"] = "Malthael";
			$record4["Device ID"] = "24681357";
			
			$records = [];
			$records[] = $record1;
			$records[] = $record2;
			$records[] = $record3;
			$records[] = $record4;
			$recordCount = count($records);
			
			$data = [];
			
			//get employee
			if ($employee != null)
			{
				$employeeArray = explode(" ", $employee);
				$employeeLName = trim($employeeArray[0], ",");
				$employee = $employeeArray[1] . " " . $employeeLName;
			}
			
			//filter records
			for($i = 0; $i < $recordCount; $i++)
			{
				if($employee == null || $records[$i]["Surveyor"] == $employee)
				{
					if($trackingGroup == null || $records[$i]["Tracking Group"] == $trackingGroup)
					{
						if($deviceID == null || $records[$i]["Device ID"] == $deviceID)
						{
							if(BaseActiveController::inDateRange($records[$i]["Date/Time"], $startDate, $endDate))
							{
								$data[] = $records[$i];
							}
						}
					}
				}
			}
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
			return $response;
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

    public function actionGetRecentActivity($division, $workCenter=null, $surveyor = null, $startDate = null, $endDate = null, $search = null, $page=1, $perPage=25)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
                Yii::trace('in if');
                WebManagementTrackerCurrentLocation::setClient($headers['X-Client']);
                $query = WebManagementTrackerCurrentLocation::find();

                $query->where(['Division' => $division]);
                $query->andWhere(["Work Center" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', '[Date]', $search],
                        ['like', '[Surveyor / Inspector]', $search],
                        ['like', '[Work Center]', $search],
                        ['like', 'Latitude', $search],
                        ['like', 'Longitude', $search],
                        ['like', '[Battery Level]', $search],
                        ['like', '[GPS Type]', $search],
                        ['like', '[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $countQuery = clone $query;

                /* page index is 0 based */
                $page = max($page-1,0);
                $totalCount = $countQuery->count();
                $pages = new Pagination(['totalCount' => $totalCount]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPageSize($perPage);
                $pages->setPage($page,true);
                $offset = $pages->getOffset();//$perPage * ($page - 1);
                $limit = $pages->getLimit();

//                $query->orderBy(['Date' => SORT_ASC, 'Surveyor / Inspector' => SORT_ASC]);

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->all();
//                $items = WebManagementTrackerCurrentLocation::find()->all();
//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
//                $items = $items->queryAll();
            } else {
                $pages = new Pagination(['totalCount' => 0]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(0);
                $pages->setPageSize($perPage);
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;
            $data['pages'] = $pages;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistory($division, $workCenter=null, $surveyor = null, $startDate = null, $endDate = null, $search = null, $page=1, $perPage=25)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();
                $query->where(['Division' => $division]);
                $query->andWhere(["Work Center" => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(["Surveyor / Inspector" => $surveyor]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', 'Division', $search],
                        ['like', 'Date', $search],
                        ['like', '[Surveyor / Inspector]', $search],
                        ['like', 'Work Center', $search],
                        ['like', 'Latitude', $search],
                        ['like', 'Longitude', $search],
                        ['like', '[Date Time]', $search],
                        ['like', 'House No', $search],
                        ['like', 'Street', $search],
                        ['like', 'Apt', $search],
                        ['like', 'City', $search],
                        ['like', 'State', $search],
                        ['like', 'Landmark', $search],
                        ['like', '[Landmark Description]', $search],
                        ['like', '[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }

                $countQuery = clone $query;

                /* page index is 0 based */
                $page = max($page-1,0);
                $totalCount = $countQuery->count();
                $pages = new Pagination(['totalCount' => $totalCount]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPageSize($perPage);
                $pages->setPage($page,true);
                $offset = $pages->getOffset();//$perPage * ($page - 1);
                $limit = $pages->getLimit();

                $query->orderBy(['Date' => SORT_ASC, 'Surveyor / Inspector' => SORT_ASC]);

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->all();
//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
//                $items = $items->queryAll();

            } else {
                $pages = new Pagination(['totalCount' => 0]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPage(0);
                $pages->setPageSize($perPage);
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;
            $data['pages'] = $pages;

            //send response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            $response->data = $data;
            return $response;
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

}