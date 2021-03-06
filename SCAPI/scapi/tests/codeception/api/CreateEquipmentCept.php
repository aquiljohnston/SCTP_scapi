<?php 
$I = new ApiTester($scenario);

//Create Equipment
$I->wantTo('Create Equipment via API');
//$I->amHttpAuthenticated('user', 'password');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('?r=equipment%2Fcreate', ['EquipmentName' => 'Flame Pack 400', 'EquipmentSerialNumber' => '1234567890']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$equipmentId = $I->grabDataFromResponseByJsonPath('$.EquipmentID');
$I->comment("equipment Id is".$equipmentId[0]);

//Get Equipment
$I->wantTo('GET Equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET("?r=equipment%2Fview&id=".$equipmentId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0],
		'EquipmentName' => 'Flame Pack 400',
		'EquipmentSerialNumber' => '1234567890'
]);

//Get all Equipment
$I->wantTo('GET all Equipment');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET('?r=equipment%2Fview-all');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();


//Delete Equipment
$I->wantTo('DELETE Equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendDELETE('?r=equipment%2Fdelete&id='.$equipmentId[0]);
$I->seeResponseCodeIs(204);