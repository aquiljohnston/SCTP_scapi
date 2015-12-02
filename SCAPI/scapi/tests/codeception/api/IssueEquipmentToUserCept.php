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

//Create User
$I->wantTo('Create User via API');
//$I->amHttpAuthenticated('user', 'password');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('?r=user%2Fcreate', ['UserName' => 'Codecept Test']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$userId = $I->grabDataFromResponseByJsonPath('$.UserID');

//Get Equipment
$I->wantTo('GET Equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET("?r=equipment%2Fview&id=".$equipmentId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0]
]);

//Get User
$I->wantTo('GET User by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET("?r=user%2Fview&id=".$userId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'UserID' => $userId[0]
]);

//Issue Equipment to User
$I->wantTo('Issue Equipment to User');
//$I->amHttpAuthenticated('user', 'password');
$I->sendPUT('?r=equipment%2Fupdate&id='.$equipmentId[0],['EquipmentAssignedUserID' => $userId[0]]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0],
		'EquipmentAssignedUserID' => $userId[0]
]);



//Remove Equipment from User
$I->wantTo('Remove Equipment from User');
//$I->amHttpAuthenticated('user', 'password');
$I->sendPUT('?r=equipment%2Fupdate&id='.$equipmentId[0],['EquipmentAssignedUserID' => null]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0],
		'EquipmentAssignedUserID' => null
]);


//Delete Equipment
$I->wantTo('DELETE Equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendDELETE('?r=equipment%2Fdelete&id='.$equipmentId[0]);
$I->seeResponseCodeIs(204);

//Delete User
$I->wantTo('DELETE User by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendDELETE('?r=user%2Fdelete&id='.$userId[0]);
$I->seeResponseCodeIs(204);