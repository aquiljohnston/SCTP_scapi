<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add Equipment to Project');
 
//Create Equipment
$I->wantTo('Create Equipment via API');
//$I->amHttpAuthenticated('user', 'password');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('?r=equipment%2Fcreate', ['EquipmentName' => 'Flame Pack 400', 'EquipmentSerialNumber' => '1234567890']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$equipmentId = $I->grabDataFromResponseByJsonPath('$.EquipmentID');

//Get Equipment
$I->wantTo('GET Equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET("?r=equipment%2Fview&id=".$equipmentId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0]
]);

//Add Equipment to Project
$I->wantTo('Add Equipment to Project');
//$I->amHttpAuthenticated('user', 'password');
$I->sendPUT('?r=equipment%2Fupdate&id='.$equipmentId[0],['EquipmentProjectID' => 1]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0],
		'EquipmentProjectID' => 1
]);



//Remove Equipment from Project
$I->wantTo('Remove Equipment from Project');
//$I->amHttpAuthenticated('user', 'password');
$I->sendPUT('?r=equipment%2Fupdate&id='.$equipmentId[0],['EquipmentProjectID' => null]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0],
		'EquipmentProjectID' => null
]);


//Delete Equipment
$I->wantTo('DELETE Equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendDELETE('?r=equipment%2Fdelete&id='.$equipmentId[0]);
$I->seeResponseCodeIs(204);