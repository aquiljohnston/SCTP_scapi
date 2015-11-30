<?php 
$I = new ApiTester($scenario);
$I->wantTo('create equipment via API');
//$I->amHttpAuthenticated('user', 'password');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('?r=equipment%2Fcreate', ['EquipmentName' => 'Flame Pack 400', 'EquipmentSerialNumber' => '1234567890']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
