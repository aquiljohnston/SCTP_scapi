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
use app\modules\v1\modules\pge\models\WebManagementTrackerHistory;
use app\modules\v1\modules\pge\models\WebManagementTrackerBreadcrumbs;
use app\modules\v1\modules\pge\models\WebManagementTrackerAOC;
use app\modules\v1\modules\pge\models\WebManagementTrackerIndications;
use app\modules\v1\modules\pge\models\WebManagementTrackerMapGridCompliance;
use app\modules\v1\modules\pge\models\WebManagementTrackerHistoryDropDown;
use app\modules\v1\modules\pge\models\AssetAddressCGE;
use app\modules\v1\modules\pge\models\PGEUser;


class TrackerController extends Controller 
{
    public $mapResultsLimit = 50000; // limits the maximum returned results for map api calls
    public $downloadItemsLimit = 1000000; // limits the maximum number of results the csv file will contain
    public $filtersLimit = 104; // limits the number of filter values for CGI or Breadcrumbs

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
                    'get-history' => ['get'],
                    'get-history-map-breadcrumbs' => ['get'],
                    'get-history-map-aocs' => ['get'],
                    'get-history-map-indications' => ['get'],
                    'get-history-map-controls' => ['get'],
                    'get-history-map-cgi' => ['get'],
                    'get-download-history-data' => ['get']
                ],  
            ];
		return $behaviors;	
	}

    public function actionGetHistory($division, $workCenter=null, $surveyor = null, $startDate = null, $endDate = null,
                                     $timeInterval = null, $search = null, $page=1, $perPage=25)
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();
                $timeInterval = intval($timeInterval);
                if ($timeInterval<=0) {
					//andre and jose's madness
                    $timeInterval = 1;
                }
                if ($timeInterval>30) {
                    $timeInterval = 30;
                }
                if ($timeInterval>0){
                    $query->from([
                        'th'=>'fnWebManagementTrackerHistoryFilteredByTimeInterval(:timeInterval, :startDate, :endDate)',
                    ]);
                    $query->addParams([
                        ':timeInterval' => $timeInterval,
                        ':startDate' => $startDate,
                        ':endDate' => $endDate
                    ]);
                } else {
                    $query->from([
                        'th'=>'['.WebManagementTrackerHistory::tableName().']',
                    ]);
                }
                $colsToSelect =[
                    '[th].[Date Time]',
                    '[th].[Surveyor / Inspector]',
                    '[th].[Latitude]',
                    '[th].[Longitude]',
                    '[th].[House No]',
                    '[th].[Street]',
                    '[th].[Apt]',
                    '[th].[City]',
                    '[th].[State]',
                    '[th].[Landmark]',
                    '[th].[Landmark Description]',
                    '[th].[Accuracy (Meters)]',
                    '[th].[UID]',
                ];

                $query->select($colsToSelect);

                $query->where(['[th].[Division]' => $division]);
                $query->andWhere(['[th].[Work Center]' => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(['LOWER([th].[Surveyor / Inspector])' => strtolower($surveyor)]);
                }

                if (trim($search)) {
                    $query->andWhere([
                        'or',
                        ['like', '[th].[Division]', $search],
                        ['like', '[th].[Date Time]', $search],
                        ['like', '[th].[Surveyor / Inspector]', $search],
                        ['like', '[th].[Work Center]', $search],
                        ['like', '[th].[Latitude]', $search],
                        ['like', '[th].[Longitude]', $search],
                        ['like', '[th].[Date Time]', $search],
                        ['like', '[th].[House No]', $search],
                        ['like', '[th].[Street]', $search],
                        ['like', '[th].[Apt]', $search],
                        ['like', '[th].[City]', $search],
                        ['like', '[th].[State]', $search],
                        ['like', '[th].[Landmark]', $search],
                        ['like', '[th].[Landmark Description]', $search],
                        ['like', '[th].[Accuracy (Meters)]', $search]
                    ]);
                }
                if ($startDate !== null && $endDate !== null) {
                    // Only add the following if the DB field is Date Time
                    //// 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    //$endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', '[th].[Date]', $startDate, $endDate]);
                }



                $query->distinct();

                $countQuery = clone $query;

                $orderByCols = [
                    '[th].[Date Time]' => SORT_ASC,
                    '[th].[Surveyor / Inspector]' => SORT_ASC,
                    '[th].[UID]' => SORT_ASC,
                ];

                $query->orderBy($orderByCols);

                /* page index is 0 based */
                $page = max($page-1,0);
                $totalCount = $countQuery->count();
                $pages = new Pagination(['totalCount' => $totalCount]);
                $pages->pageSizeLimit = [1, 100];
                $pages->setPageSize($perPage);
                $pages->setPage($page,true);
                $offset = $pages->getOffset();//$perPage * ($page - 1);
                $limit = $pages->getLimit();

                $items = $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
                 $sqlString = $items->rawSql;
                 Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $items = $items->queryAll();

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

    /**
     * @param string $division
     * @param string $workCenter
     * @param string $surveyors     - comma separated list of lanids. If it is empty not set then it will not filter by
     *                                surveyors and all available surveyors will be considered
     * @param string $startDate
     * @param string $endDate
     * @param string $search
     * @param string $minLat
     * @param string $maxLat
     * @param string $minLong
     * @param string $maxLong
     * @param int $timeInterval [0,30]
     * @return string
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionGetHistoryMapBreadcrumbs($division=null, $workCenter=null, $surveyors = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $timeInterval = null
                                                   )
    {
        try{

            $headers = getallheaders();

            if ($division && $workCenter) {
//                WebManagementTrackerBreadcrumbs::setClient($headers['X-Client']);
//                $query = WebManagementTrackerBreadcrumbs::find();
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();
                if ($timeInterval<=0) {
                    //andre and jose's madness
                    $timeInterval = 1;
                }
                if ($timeInterval>30) {
                    $timeInterval = 30;
                }
                if ($timeInterval>0){
                    $query->from([
                        'th'=>'fnWebManagementTrackerHistoryFilteredByTimeInterval(:timeInterval, :startDate, :endDate)',
                    ]);
                    $query->addParams([
                        ':timeInterval' => $timeInterval,
                        ':startDate' => $startDate,
                        ':endDate' => $endDate
                    ]);
                } else {
                    $query->from([
                        'th'=>'['.WebManagementTrackerHistory::tableName().']',
                    ]);
                }

                $query->select([
                    'th.UID',
                    'tb.LanID as Inspector',
                    'tb.SrcDTLT as Datetime',
                    'th.[Date]',
                    'th.[House No] as [House No]',
                    'th.Street',
                    'th.City',
                    'th.State',
                    'tb.Latitude as Latitude',
                    'tb.Longitude as Longitude',
                    'tb.Speed as Speed',
                    'tb.GPSAccuracy as Accuracy'
                ]);

                $query->innerJoin(
                    ['tb'=>WebManagementTrackerBreadcrumbs::tableName()],
                    '[th].[UID]=[tb].[UID]'
                );

                $query = $this->addTrackerHistoryTableViewFiltersToQuery($query, $division, $workCenter, $startDate, $endDate, $surveyors, $search);

                if (null!=$minLat){
                    $query->andWhere(['>=','tb.Latitude',$minLat]);
                }
                if (null!=$maxLat){
                    $query->andWhere(['<=','tb.Latitude',$maxLat]);
                }
                if (null!=$minLong){
                    $query->andWhere(['>=','tb.Longitude',$minLong]);
                }
                if (null!=$maxLong){
                    $query->andWhere(['<=','tb.Longitude',$maxLong]);
                }

                $query->distinct();
                // the orderBy is needed for applying distinct
                $query->orderBy([
                    //'th.UID' => SORT_ASC,
                    //'[th].[SurveyorLANID]' => SORT_ASC,
                    //'[th].[Date Time]' => SORT_ASC,
                    'tb.LanID' => SORT_ASC,
                    'tb.SrcDTLT' => SORT_ASC,

                ]);


                $limit =$this->mapResultsLimit;
                $offset = 0;

                $queryCommand= $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
                // $sqlString = $queryCommand->sql;
                // $sqlString = $queryCommand->rawSql;
                // Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);

                $reader = $queryCommand->query(); // creates a reader so that information can be processed one row at a time

                $this->processAndOutputCsvResponse($reader);

                return '';
            } // end division and workcenter check

            $this->setCsvHeaders();

            return '';
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * @param string $division
     * @param string $workCenter
     * @param string $surveyors     - comma separated list of lanids. If it is empty not set then it will not filter by
     *                                surveyors and all available surveyors will be considered
     * @param string $startDate
     * @param string $endDate
     * @param string $search
     * @param string $minLat
     * @param string $maxLat
     * @param string $minLong
     * @param string $maxLong
     * @param string $cgi           - comma separated list of user ids.  If it has the value _all_ it will not filter by
     *                                CreatedUserID and consider all
     * @return string
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionGetHistoryMapCgi($division=null, $workCenter=null, $surveyors = null,
                                            $startDate = null, $endDate = null, $search = null,
                                            $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                            $cgi=null)
    {
        try{

            $headers = getallheaders();
            if ($cgi) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();
                $query->select([
                    '[th].[UID]',
                    '[aac].[CreatedUserUID]',
                    '[aac].[SrcDTLT]',
                    '[aac].[Latitude]',
                    '[aac].[Longitude]',
                    '[aac].[StatusType]',
                    '[aac].[CGEReasonType]',
                    '[aac].[CGECardNo]'
                ]);
                $query->from([
                    'th'=>'['.WebManagementTrackerHistory::tableName().']',
                ]);
                $query->innerJoin(
                    ['aac'=>AssetAddressCGE::tableName()],
                    '[th].[UID]=[aac].[AssetAddressCGEUID]'
                );

                $query = $this->addTrackerHistoryTableViewFiltersToQuery($query, $division, $workCenter, $startDate, $endDate, $surveyors, $search);

                if (trim(strtolower($cgi))!='_all_') {
                    $sentCgis = explode(',', $cgi);
                    $filterConditions = null;
                    /*
                     * construct an array of the form
                     * ['CreatedUserUID'=>value] for one entry
                     * [
                     *   'or',
                     *   ['CreatedUserUID'=>value1],
                     *    ...
                     *   ['CreatedUserUID'=>valuen]
                     * ] -- for multiple entries
                     */
                    foreach ($sentCgis as $sentCgi) {
                        $uid = trim(strtolower($sentCgi));//trim(strtolower($sentCgis));
                        if ('' == $uid) {
                            continue;
                        }
                        if (null === $filterConditions) {
                            $filterConditions = ['aac.CreatedUserUID' => $uid];
                        } elseif (isset($filterConditions[0]) && $filterConditions[0] == 'or') {
                            $filterConditions[] = ['aac.CreatedUserUID' => $uid];
                        } else {
                            $tmp = $filterConditions;
                            $filterConditions = [];
                            $filterConditions[0] = 'or';
                            $filterConditions[] = $tmp;
                            $filterConditions[] = ['aac.CreatedUserUID' => $uid];
                        }
                    }

                    if (null != $filterConditions) {
                        $query->andWhere($filterConditions);
                    }
                }
                if (null!=$minLat){
                    $query->andWhere(['>=','aac.Latitude',$minLat]);
                }
                if (null!=$maxLat){
                    $query->andWhere(['<=','aac.Latitude',$maxLat]);
                }
                if (null!=$minLong){
                    $query->andWhere(['>=','aac.Longitude',$minLong]);
                }
                if (null!=$maxLong){
                    $query->andWhere(['<=','aac.Longitude',$maxLong]);
                }

                $query->distinct();
                $query->orderBy([
                    'th.UID' => SORT_ASC
                ]);

                $limit =$this->mapResultsLimit;
                $offset = 0;
                $queryCommand= $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $queryCommand->rawSql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);

                $reader = $queryCommand->query(); // creates a reader so that information can be processed one row at a time

                $this->processAndOutputCsvResponse($reader);

                return '';
            } // end division and workcenter check

            $this->setCsvHeaders();

            return '';
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * @param string $division
     * @param string $workCenter
     * @param string $surveyors     - comma separated list of lanids. If it is empty not set then it will not filter by
     *                                surveyors and all available surveyors will be considered
     * @param string $startDate
     * @param string $endDate
     * @param string $search
     * @param string $minLat
     * @param string $maxLat
     * @param string $minLong
     * @param string $maxLong
     * @param string $aoc           - comma separated list of AOC Types (only the interger part) .  If it has the value _all_ then it will not filter by
     *                                AocType and consider all AOCTypes
     * @return string
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionGetHistoryMapAocs($division=null, $workCenter=null, $surveyors = null,
                                            $startDate = null, $endDate = null, $search = null,
                                            $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                            $aoc=null)
    {
        try{

            $headers = getallheaders();
            if ($aoc) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();

                $query->select([
                    'th.[UID]',
                    'aoc.LanID as Inspector',
                    'aoc.SurveyDateTime as Datetime',
                    'aoc.HouseNo as [House No]',
                    'aoc.Street1 as Street',
                    'aoc.City',
                    'aoc.State',
                    'aoc.Latitude',
                    'aoc.Longitude',
                    'aoc.AOCType as [AOC Type]',
                    'RTRIM(SUBSTRING(aoc.AOCType, 1,CHARINDEX(\'-\',aoc.AOCType)-1)) as AOC'
                ]);
                $query->from([
                    'th'=>'['.WebManagementTrackerHistory::tableName().']',
                ]);
                $query->innerJoin(
                    ['aoc'=>WebManagementTrackerAOC::tableName()],
                    '[th].[UID]=[aoc].[UID]'
                );

                $query = $this->addTrackerHistoryTableViewFiltersToQuery($query, $division, $workCenter, $startDate, $endDate, $surveyors, $search);

                if (trim(strtolower($aoc))!=='_all_') {
                    $sentAocs = explode(',', $aoc);
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
                        $aocTypeCode = intval(trim($sentAoc));
                        if (null === $filterConditions) {
                            $filterConditions = ['RTRIM(SUBSTRING(aoc.AOCType, 1,CHARINDEX(\'-\',aoc.AOCType)-1))' => $aocTypeCode];
                        } elseif (isset($filterConditions[0]) && $filterConditions[0] == 'or') {
                            $filterConditions[] = ['RTRIM(SUBSTRING(aoc.AOCType, 1,CHARINDEX(\'-\',aoc.AOCType)-1))' => $aocTypeCode];
                        } else {
                            $tmp = $filterConditions;
                            $filterConditions = [];
                            $filterConditions[0] = 'or';
                            $filterConditions[] = $tmp;
                            $filterConditions[] = ['RTRIM(SUBSTRING(aoc.AOCType, 1,CHARINDEX(\'-\',aoc.AOCType)-1))' => $aocTypeCode];
                        }
                    }
                    $query->andWhere($filterConditions);
                }

                if (null!=$minLat){
                    $query->andWhere(['>=','aoc.Latitude',$minLat]);
                }
                if (null!=$maxLat){
                    $query->andWhere(['<=','aoc.Latitude',$maxLat]);
                }
                if (null!=$minLong){
                    $query->andWhere(['>=','aoc.Longitude',$minLong]);
                }
                if (null!=$maxLong){
                    $query->andWhere(['<=','aoc.Longitude',$maxLong]);
                }

                $query->distinct();
                $query->orderBy([
                    'th.UID' => SORT_ASC
                ]);

                $limit =$this->mapResultsLimit;
                $offset = 0;
                $queryCommand= $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
                // $sqlString = $queryCommand->rawSql;
                // Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);

                $reader = $queryCommand->query(); // creates a reader so that information can be processed one row at a time
                $this->processAndOutputCsvResponse($reader);

                return '';
            } // end division and workcenter check

            $this->setCsvHeaders();

            return '';
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }
    /**
     * @param string $division
     * @param string $workCenter
     * @param string $surveyors     - comma separated list of lanids. If it is empty not set then it will not filter by
     *                                surveyors and all available surveyors will be considered
     * @param string $startDate
     * @param string $endDate
     * @param string $search
     * @param string $minLat
     * @param string $maxLat
     * @param string $minLong
     * @param string $maxLong
     * @param string $indications   - comma separated list of GradeTypes.  If it is has the value _all_ then it will not filter by
     *                                GradeType and consider all gradeTypes
     * @return string
     * @throws ForbiddenHttpException
     * @throws \yii\web\HttpException
     */
    public function actionGetHistoryMapIndications($division=null, $workCenter=null, $surveyors = null,
                                                   $startDate = null, $endDate = null, $search = null,
                                                   $minLat = null, $maxLat = null, $minLong = null, $maxLong = null,
                                                   $indications=null)
    {
        try{

            $headers = getallheaders();

            if ($indications) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();

                $query->select([
                    'th.UID',
                    'i.LanID as Inspector',
                    'i.SurveyDateTime as Datetime',
                    'i.HouseNo as [House No]',
                    'i.Street1 as Street',
                    'i.City',
                    'i.State',
                    'i.Latitude',
                    'i.Longitude',
                    'i.AboveBelowGroundType as [Leak Source]',//'InitialLeakSourceType as [Leak Source]',
                    'i.SORLType as [Leak SORL]',
                    'i.fndEquipmentType as [Leak Found By]',//'FoundBy as [Leak Found By]',
                    'i.grdEquipmentType as [Leak Grade By]',//'GradeBy as [Leak Grade By]',
                    'i.ReadingGrade as [Leak % Gas]',
                    'i.GradeType as [Leak Grade]'
                ]);
                $query->from([
                    'th'=>'['.WebManagementTrackerHistory::tableName().']',
                ]);
                $query->innerJoin(
                    ['i'=>WebManagementTrackerIndications::tableName()],
                    '[th].[UID]=[i].[UID]'
                );

                $query = $this->addTrackerHistoryTableViewFiltersToQuery($query, $division, $workCenter, $startDate, $endDate, $surveyors, $search);

                if (trim(strtolower($indications))!=='_all_') {
                    $indPossibleValues = ['1' => '1', '2p' => '2+', '2+' => '2+', '2%2B' => '2+', '2 ' => '2+', '2' => '2', '3' => '3'];

                    $sentIndications = explode(',', $indications);
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
                        // $indKey = trim(strtolower($sentIndication));
                        $indKey = strtolower($sentIndication);
                        if (isset($indPossibleValues[$indKey])) {
                            if (null === $indFilterConditions) {
                                $indFilterConditions = ['i.GradeType' => $indPossibleValues[$indKey]];
                            } elseif (isset($indFilterConditions[0]) && $indFilterConditions[0] == 'or') {
                                $indFilterConditions[] = ['i.GradeType' => $indPossibleValues[$indKey]];
                            } else {
                                $tmp = $indFilterConditions;
                                $indFilterConditions = [];
                                $indFilterConditions[0] = 'or';
                                $indFilterConditions[] = $tmp;
                                $indFilterConditions[] = ['i.GradeType' => $indPossibleValues[$indKey]];
                            }
                        }
                    }
                    $query->andWhere($indFilterConditions);
                }

                if (null!=$minLat){
                    $query->andWhere(['>=','i.Latitude',$minLat]);
                }

                if (null!=$maxLat){
                    $query->andWhere(['<=','i.Latitude',$maxLat]);
                }

                if (null!=$minLong){
                    $query->andWhere(['>=','i.Longitude',$minLong]);
                }

                if (null!=$maxLong){
                    $query->andWhere(['<=','i.Longitude',$maxLong]);
                }

                $query->distinct();
                $query->orderBy([
                    'th.UID' => SORT_ASC
                ]);

                $limit =$this->mapResultsLimit;
                $offset = 0;

                $queryCommand= $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
                $sqlString = $queryCommand->rawSql;
                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);

                $reader = $queryCommand->query(); // creates a reader so that information can be processed one row at a time
                $this->processAndOutputCsvResponse($reader);

                return '';
            } // end indications check

            $this->setCsvHeaders();

            return '';
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetDownloadHistoryData($division, $workCenter=null, $surveyor = null, $startDate = null,
                                              $endDate = null, $search = null, $timeInterval = null)
    {
        try{
            $headers = getallheaders();

            if ($division && $workCenter) {
                WebManagementTrackerHistory::setClient($headers['X-Client']);
                $query = WebManagementTrackerHistory::find();
                if ($timeInterval<=0) {
                    //andre and jose's madness
                    $timeInterval = 1;
                }
                if ($timeInterval>30) {
                    $timeInterval = 30;
                }
                if ($timeInterval>0){
                    $query->from([
                        'th'=>'fnWebManagementTrackerHistoryFilteredByTimeInterval(:timeInterval, :startDate, :endDate)',
                    ]);
                    $query->addParams([
                        ':timeInterval' => $timeInterval,
                        ':startDate' => $startDate,
                        ':endDate' => $endDate
                    ]);
                } else {
                    $query->from([
                        'th'=>'['.WebManagementTrackerHistory::tableName().']',
                    ]);
                }
                $query->select(
                    [
                        '[Date Time]',
                        '[Surveyor / Inspector]',
                        '[Latitude]',
                        '[Longitude]',
                        '[House No]',
                        '[Street]',
                        '[Apt]',
                        '[City]',
                        '[State]',
                        '[Landmark]',
                        '[Landmark Description]',
                        '[Accuracy (Meters)]',
                        '[UID]',
                    ]
                );
                $query->where(['Division' => $division]);
                $query->andWhere(['Work Center' => $workCenter]);

                if ($surveyor) {
                    $query->andWhere(['LOWER(SurveyorLANID)' => $surveyor]);
                }

                if (trim($search)!=='') {
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
                    // Only add the following if the DB field is Date Time
                    // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
                    //$endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

                    $query->andWhere(['between', 'Date', $startDate, $endDate]);
                }
                $query->distinct();
                $query->orderBy([
                    'Date Time' => SORT_ASC,
                    'Surveyor / Inspector' => SORT_ASC,
                    'UID' => SORT_ASC,
                ]);


                $offset = 0;
                $limit = $this->downloadItemsLimit;

                $queryCommand= $query->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $queryCommand->rawSql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);

                $reader = $queryCommand->query(); // creates a reader so that information can be processed one row at a time
                $this->processAndOutputCsvResponse($reader);

                return '';
            } // end division and workcenter check

            $this->setCsvHeaders();
            //send response
            return '';
        } catch(ForbiddenHttpException $e) {
            Yii::trace('ForbiddenHttpException '.$e->getMessage());
            throw new ForbiddenHttpException;
        } catch(\Exception $e) {
            Yii::trace('Exception '.$e->getMessage());
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetHistoryMapControls($division=null, $workCenter=null,
                                           $startDate = null, $endDate = null, $search = null, $surveyors = null)
    {
        try{

            $headers = getallheaders();
            if (null !==$division && null !==$workCenter && null !== $startDate && null !== $endDate) {
                WebManagementTrackerHistoryDropDown::setClient($headers['X-Client']);
                $lanIdsQuery = WebManagementTrackerHistoryDropDown::find()
                    ->select(
                        [
                            'Key'=>'LOWER([SurveyorLANID])',
                            'DisplayedText'=>'[Surveyor]'
                        ]
                    )
                    ->where(['Division' => $division])
                    ->andWhere(['WorkCenter' => $workCenter])
                    ->andWhere(['not' ,['Division' => null]])
                    ->andWhere(['not' ,['WorkCenter' => null]])
                    ->andWhere(['not' ,['Surveyor' => null]])
                    ->andWhere(['between', 'Date', $startDate, $endDate])
                    ->distinct();

                if (null!=$surveyors) {
                    $sentLanIds = explode(',',$surveyors);
                    $filterConditions = null;

                    /*
                     * construct an array of the form
                     * ['LanID'=>value] for one entry
                     * [
                     *   'or',
                     *   ['LanID'=>value1],
                     *    ...
                     *   ['LanID'=>valuen]
                     * ] -- for multiple entries
                     */
                    foreach ($sentLanIds as $sentLanId) {
                        $lanId = trim(strtolower($sentLanId));//trim(strtolower($sentCgis));
                        if (''==$lanId){
                            continue;
                        }
                        if (null === $filterConditions){
                            $filterConditions = ['LOWER(SurveyorLANID)'=>$lanId];
                        } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                            $filterConditions[]= ['LOWER(SurveyorLANID)'=>$lanId];
                        } else {
                            $tmp = $filterConditions;
                            $filterConditions=[];
                            $filterConditions[0] = 'or';
                            $filterConditions[]= $tmp;
                            $filterConditions[]= ['LOWER(SurveyorLANID)'=>$lanId];
                        }
                    }
                    if (null!=$filterConditions) {
                        $lanIdsQuery->andWhere($filterConditions);
                    }
                }

                $lanIdsQuery->orderBy(['Surveyor'=>SORT_ASC]);

//                $lanIdsQuery->offset($offset)
//                    ->limit($limit)
                $lanIdsQueryCommand = $lanIdsQuery->createCommand();
//                $sqlString = $lanIdsQueryCommand->sql;
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);
                $surveyorFilters = $lanIdsQueryCommand->queryAll();


                PGEUser::setClient($headers['X-Client']);
                $cgiQuery = PGEUser::find();
                $cgiQuery->select([
                    'Key'=>'u.UserUID',
                    'DisplayedText'=>"CONCAT(u.UserLastName,', ',u.UserFirstName)"
//                    'DisplayedText'=>"CONCAT(u.UserLastName,', ',u.UserFirstName,' (',u.UserLANID ,')')"
                ])->distinct();

                $cgiQuery->from([
                    'u'=>'['.PGEUser::tableName().']',
                ]);

                $filterConditions = null;
                $lanIds=[];
                foreach ($surveyorFilters as $surveyor) {
                    $lanIds[] = $surveyor['Key'];
                }
                if (!empty($lanIds)) {
                    $cgiQuery->where(['u.UserLANID'=>$lanIds]);
                }
                $cgiQuery->orderBy([
                    'DisplayedText' => SORT_ASC
                ]);

                $limit = $this->filtersLimit;
                $offset = 0;
                $uidsQueryCommand = $cgiQuery->offset($offset)
                    ->limit($limit)
                    ->createCommand();
//                $sqlString = $uidsQueryCommand->rawSql; //print_r($sqlString);
//                Yii::trace(print_r($sqlString,true).PHP_EOL.PHP_EOL.PHP_EOL);

                $cgiUids = $uidsQueryCommand->queryAll();


                $items = ['cgiFilters'=>$cgiUids,'surveyorFilters'=>$surveyorFilters];

            } else {
                $items = ['cgiFilters'=>[],'surveyorFilters'=>[]];
            } // end division and workcenter check

            $data = [];
            $data['controls'] = $items;

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

////////////////////
// Helper methods
////////////////////

    // helper method for setting the csv header for tracker maps csv output
    public function setCsvHeaders(){
        header('Content-Type: text/csv;charset=UTF-8');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    // helper method for outputting csv data without storing the whole result
    public function processAndOutputCsvResponse($reader){
        Yii::$app->response->format = Response::FORMAT_RAW;

        $this->setCsvHeaders();
        // TODO find a way to use Yii response but without storing the whole response content in a variable
        $firstLine = true;
        $fp = fopen('php://output','w');

        while($row = $reader->read()){
            if($firstLine) {
                $firstLine = false;
                fputcsv($fp, array_keys($row));
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
    }

    /* used as a helper function to avoid repeating the code section for indications, aoc, cge and breadcrumbs */
    public function addTrackerHistoryTableViewFiltersToQuery($query, $division, $workCenter, $startDate, $endDate, $surveyors, $search) {
        $query->where(['[th].[Division]' => $division]);
        $query->andWhere(['[th].[Work Center]' => $workCenter]);

        if ($surveyors) {
            $sentLanIds = explode(',',$surveyors);
            $filterConditions = null;

            /*
             * construct an array of the form
             * ['LanID'=>value] for one entry
             * [
             *   'or',
             *   ['LanID'=>value1],
             *    ...
             *   ['LanID'=>valuen]
             * ] -- for multiple entries
             */
            foreach ($sentLanIds as $sentLanId) {
                $lanId = trim(strtolower($sentLanId));
                if (''==$lanId){
                    continue;
                }
                if (null === $filterConditions){
                    $filterConditions = ['LOWER([th].[SurveyorLANID])'=>$lanId];
                } elseif ( isset($filterConditions[0]) && $filterConditions[0]=='or') {
                    $filterConditions[]= ['LOWER([th].[SurveyorLANID])'=>$lanId];
                } else {
                    $tmp = $filterConditions;
                    $filterConditions=[];
                    $filterConditions[0] = 'or';
                    $filterConditions[]= $tmp;
                    $filterConditions[]= ['LOWER([th].[SurveyorLANID])'=>$lanId];
                }
            }
            if (null!=$filterConditions) {
                $query->andWhere($filterConditions);
            }
        }

        if (trim($search)) {
            $query->andWhere([
                'or',
                ['like', 'th.Division', $search],
                ['like', 'th.Date', $search],
                ['like', 'th.[Surveyor / Inspector]', $search],
                ['like', 'th.[Work Center]', $search],
                ['like', 'th.Latitude', $search],
                ['like', 'th.Longitude', $search],
                ['like', 'th.[Date Time]', $search],
                ['like', 'th.[House No]', $search],
                ['like', 'th.Street', $search],
                ['like', 'th.Apt', $search],
                ['like', 'th.City', $search],
                ['like', 'th.State', $search],
                ['like', 'th.Landmark', $search],
                ['like', 'th.[Landmark Description]', $search],
                ['like', 'th.[Accuracy (Meters)]', $search]
            ]);
        }
        if ($startDate !== null && $endDate !== null) {
            // Only add the following if the DB field is Date Time
            // 'Between' takes into account the first second of each day, so we'll add another day to have both dates included in the results
            //$endDate = date('m/d/Y 00:00:00', strtotime($endDate.' +1 day'));

            $query->andWhere(['between', 'th.Date', $startDate, $endDate]);
        }

        return $query;
    }
}