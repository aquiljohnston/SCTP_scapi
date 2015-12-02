<?php 
$I = new ApiTester($scenario);
$I->wantTo('Set start mileage');
$I->haveHttpHeader('Content-Type', 'application/json');

$I->sendPOST('?r=mileage-entry%2Fcreate', ['MileageEntryStartingMileage' => '5000.00']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$mileageId = $I->grabDataFromResponseByJsonPath('$.MileageEntryID');
echo "Id of created mileage entry".$mileageId[0];
codecept_debug('create mileage entry');

$I->wantTo('GET mileage by ID');
$I->sendGET("?r=mileage-entry%2Fview&id=".$mileageId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'MileageEntryID' => $mileageId[0],
		'MileageEntryStartingMileage' => '5000.00'
]);

$I->wantTo('set end mileage');
$I->sendPUT('?r=mileage-entry%2Fupdate&id='.$mileageId[0],['MileageEntryEndingMileage' => '5010.00']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'MileageEntryID' => $mileageId[0],
		'MileageEntryEndingMileage' => '5010.00'
]);


$I->wantTo('delete mileage entry by id');
$I->sendDELETE('?r=mileage-entry%2Fdelete&id='.$mileageId[0]);
$I->seeResponseCodeIs(204);
