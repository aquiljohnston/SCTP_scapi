<?php 
$I = new ApiTester($scenario);
$I->wantTo('Set start of work day');
$I->haveHttpHeader('Content-Type', 'application/json');

$I->sendPOST('?r=time-entry%2Fcreate', ['TimeEntryStartTime' => '9:30:50.000','TimeEntryDate' => '2015-12-02 00:00:00.000']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$timeEntryId = $I->grabDataFromResponseByJsonPath('$.TimeEntryID');
echo "Id of created time entry".$timeEntryId[0];
codecept_debug('create time entry');

$I->wantTo('GET time entry by ID');
$I->sendGET("?r=time-entry%2Fview&id=".$timeEntryId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'TimeEntryID' => $timeEntryId[0],
		'TimeEntryStartTime' => '09:30:50.0000000',
		'TimeEntryDate' => '2015-12-02 00:00:00.000'
]);

$I->wantTo('set end of work day');
$I->sendPUT('?r=time-entry%2Fupdate&id='.$timeEntryId[0],['TimeEntryEndTime' => '11:30:50.0000000']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'TimeEntryID' => $timeEntryId[0],
		'TimeEntryStartTime' => '09:30:50.0000000',
		'TimeEntryEndTime' => '11:30:50.0000000',
		'TimeEntryDate' => '2015-12-02 00:00:00.000',
]);
// grab start time
$timeEntryEndTime = $I->grabDataFromResponseByJsonPath('$.TimeEntryEndTime');
// grab end time
$timeEntryStartDate = $I->grabDataFromResponseByJsonPath('$.TimeEntryStartTime');
// assert end-start = 2
date_default_timezone_set("America/New_York");
$endTime = new DateTime("2015-12-02 ".$timeEntryEndTime[0]);
$startTime = new DateTime("2015-12-02 ".$timeEntryStartDate[0]);
codecept_debug('Time Difference');
codecept_debug($endTime->diff($startTime));
$difference = $endTime->diff($startTime);
$hour = $difference->h;
codecept_debug('Difference in Hours');
codecept_debug($hour);
$I->assertEquals($hour,2);


$I->wantTo('delete time entry by id');
$I->sendDELETE('?r=time-entry%2Fdelete&id='.$timeEntryId[0]);
$I->seeResponseCodeIs(204); 
