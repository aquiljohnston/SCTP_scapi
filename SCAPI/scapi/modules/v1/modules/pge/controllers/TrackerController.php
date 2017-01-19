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
use app\modules\v1\modules\pge\models\WebManagementTrackerBreadcrumbs;
use app\modules\v1\modules\pge\models\WebManagementTrackerAOC;
use app\modules\v1\modules\pge\models\WebManagementTrackerIndications;
use app\modules\v1\modules\pge\models\WebManagementTrackerMapGridCompliance;

class TrackerController extends Controller 
{
    public $resultsLimit = 300; // limits the maximum returned results

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
                    'get-history' => ['get'],
                    'get-history-map-breadcrumbs' => ['get'],
                    'get-history-map-aocs' => ['get'],
                    'get-history-map-indications' => ['get'],
                    'get-history-map-compliance' => ['get'],
                    'get-history-map-controls' => ['get'],
                    'get-recent-activity-map-info' => ['get'],

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

    public function actionGetHistoryMapBreadcrumbs($division=null, $workCenter=null, $surveyor = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $compliance=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
//                WebManagementTrackerBreadcrumbs::setClient($headers['X-Client']);
//                $query = WebManagementTrackerBreadcrumbs::find();
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();

//                $query->innerJoinWith([
//                    WebManagementTrackerBreadcrumbs::tableName() => function($q) {
//                        $q->select(['*']);
//                        $q->onCondition(['['.WebManagementTrackerHistory::tableName().'].[UID]'=>'['.WebManagementTrackerBreadcrumbs::tableName().'].[UID]']);
//                    },
//                ]);
                $query->select(['*']);
                $query->from([
                    'th'=>'['.WebManagementTrackerHistory::tableName().']',
                ]);
                $query->innerJoin(
                    ['tb'=>WebManagementTrackerBreadcrumbs::tableName()],
                    '[th].[UID]=[tb].[UID]'
                );

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

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

                $limit =$this->resultsLimit;
                $offset = 0;

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();


            } else {
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

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

    public function actionGetHistoryMapAocs($division=null, $workCenter=null, $surveyor = null,
                                            $startDate = null, $endDate = null, $search = null,
                                            $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                            $compliance=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();
            if ($aoc) {
                WebManagementTrackerAOC::setClient($headers['X-Client']);
                $query = WebManagementTrackerAOC::find();
                $aocPossibleValues = ['19'=>'19',
                    '31'=>'31',
                    '32'=>'32',
                    '33'=>'33',
                    '23'=>'23',
                    '30'=>'30',
                    '20'=>'20',
                    '34'=>'34',
                    '35'=>'35',
                    '36'=>'36',
                    '37'=>'37',
                    '38'=>'38',
                    '39'=>'39',
                    '51'=>'51',
                    '52'=>'52',
                    '54'=>'54',
                    '56'=>'56',
                    '57'=>'57',
                    '58'=>'58',
                    '59'=>'59',
                    '99'=>'99',
                ];

                $sentAocs = explode(',',$aoc);
                $filterConditions = null;
                /*
                 * construct an array of the form
                 * ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX('-',AOCType)-1))'=>value] for one entry
                 * [
                 *   'or',
                 *   ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX('-',AOCType)-1))'=>value1],
                 *    ...
                 *   ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX('-',AOCType)-1))'=>valuen]
                 * ] -- for multiple entries
                 */
                foreach ($sentAocs as $sentAoc) {
                    $aocKey = trim(strtolower($sentAoc));
                    if (isset($aocPossibleValues[$aocKey])){
                        if (null === $filterConditions){
                            $filterConditions = ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX(\'-\',AOCType)-1))'=>$aocPossibleValues[$aocKey]];
                        } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                            $filterConditions[]= ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX(\'-\',AOCType)-1))'=>$aocPossibleValues[$aocKey]];
                        } else {
                            $tmp = $filterConditions;
                            $filterConditions=[];
                            $filterConditions[0] = 'or';
                            $filterConditions[]= $tmp;
                            $filterConditions[]= ['RTRIM(SUBSTRING(AOCType, 1,CHARINDEX(\'-\',AOCType)-1))'=>$aocPossibleValues[$aocKey]];
                        }
                    }
                }

                $query->andWhere($filterConditions);

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

// TODO see if the workcenter, surveyor.... filter should be applied here

                $limit =$this->resultsLimit;
                $offset = 0;
//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->all();

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
                $sqlString = $items->sql;
                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();

            } else {
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

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

    public function actionGetHistoryMapIndications($division=null, $workCenter=null, $surveyor = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $compliance=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

            if ($indications) {
                WebManagementTrackerIndications::setClient($headers['X-Client']);
                $query = WebManagementTrackerIndications::find();

                $indPossibleValues = ['1'=>'1','2p'=>'2+','2'=>'2','3'=>'3'];

                $sentIndications = explode(',',$indications);
                $indFilterConditions = null;

                /*
                 * construct an array of the form
                 * ['GradeType'=>value] for one entry
                 * [
                 *   'or',
                 *   ['GradeType'=>value1],
                 *    ...
                 *   ['GradeType'=>valuen]
                 * ] -- for multiple entries
                 */
                foreach ($sentIndications as $sentIndication) {
                    $indKey = trim(strtolower($sentIndication));
                    if (isset($indPossibleValues[$indKey])){
                        if (null === $indFilterConditions){
                            $indFilterConditions = ['GradeType'=>$indPossibleValues[$indKey]];
                        } elseif ( isset($indFilterConditions[0]) && $indFilterConditions[0]=='or') {
                            $indFilterConditions[]= ['GradeType'=>$indPossibleValues[$indKey]];
                        } else {
                            $tmp = $indFilterConditions;
                            $indFilterConditions=[];
                            $indFilterConditions[0] = 'or';
                            $indFilterConditions[]= $tmp;
                            $indFilterConditions[]= ['GradeType'=>$indPossibleValues[$indKey]];
                        }
                    }
                }

                $query->andWhere($indFilterConditions);
// TODO see if the workcenter, surveyor.... filter should be applied here

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

                $limit =$this->resultsLimit;
                $offset = 0;

//                $items = $query->offset($offset)
//                    ->limit($limit)
//                    ->all();

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();
            } else {
                $items =[];
            } // end indications check

            $data = [];
            $data['results'] = $items;

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

    public function actionGetHistoryMapCompliance($division=null, $workCenter=null, $surveyor = null,
                                                  $startDate = null, $endDate = null, $search = null,
                                                  $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                  $compliance=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

//            if ($division && $workCenter) {
            WebManagementTrackerMapGridCompliance::setClient($headers['X-Client']);
            $query = WebManagementTrackerMapGridCompliance::find();

            $query->where(['Division' => $division]);
//            $query->where(['Division' => $division]);
//            $query->andWhere(["Work Center" => $workCenter]);
//
//            if ($surveyor) {
//                $query->andWhere(["Surveyor / Inspector" => $surveyor]);
//            }
//
//            if (trim($search)) {
// ?????
//            }

//            if ($startDate !== null && $endDate !== null) {
                // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
//                $endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));
//
//                $query->andWhere(['between', 'Date', $startDate, $endDate]);
//            }

            if (null!=$minLat){
                $query->andWhere(['>=','Latitude',$minLat]);
            }

            if (null!=$maxLat){
                $query->andWhere(['<=','Latitude',$maxLat]);
            }

            if (null!=$minLong){
                $query->andWhere(['>=','Longitude',$minLong]);
            }

            if (null!=$maxLong){
                $query->andWhere(['<=','Longitude',$maxLong]);
            }

            $limit =$this->resultsLimit;
            $offset = 0;
            $items = $query->offset($offset)
                ->limit($limit)
                ->all();

//            } else {
//                $items =[];
//            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

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

    public function actionGetRecentActivityMapInfo($division=null, $workCenter=null, $surveyor = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $compliance=null, $aoc=null, $indications=null, $surveyorBreadcrumbs = null)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
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

                if (null!=$minLat){
                    $query->andWhere(['>=','Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','Longitude',$maxLong]);
                }

                $limit =$this->resultsLimit;
                $offset = 0;

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $items->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();


            } else {
                $items =[];
            } // end division and workcenter check

            $data = [];
            $data['results'] = $items;

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