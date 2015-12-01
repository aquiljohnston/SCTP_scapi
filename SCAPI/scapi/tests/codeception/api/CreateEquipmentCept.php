<?php 
$I = new ApiTester($scenario);
$I->wantTo('create equipment via API');
//$I->amHttpAuthenticated('user', 'password');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('?r=equipment%2Fcreate', ['EquipmentName' => 'Flame Pack 400', 'EquipmentSerialNumber' => '1234567890']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$equipmentId = $I->grabDataFromResponseByJsonPath('$.EquipmentID');
echo "Id of created equipment".$equipmentId[0];

$I->wantTo('GET equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET("?r=equipment%2Fview&id=".$equipmentId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'EquipmentID' => $equipmentId[0],
		'EquipmentName' => 'Flame Pack 400',
		'EquipmentSerialNumber' => '1234567890'
]);

$I->wantTo('DELETE equipment by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendDELETE('?r=equipment%2Fdelete&id='.$equipmentId[0]);
$I->seeResponseCodeIs(204);