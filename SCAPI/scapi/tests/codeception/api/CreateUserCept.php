<?php 
$I = new ApiTester($scenario);
$I->wantTo('create a user via API');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('?r=user%2Fcreate', ['UserName' => 'Yasmin']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$userId = $I->grabDataFromResponseByJsonPath('$.UserID');
echo "Id of created user".$userId[0];
codecept_debug('create user');

$I->wantTo('GET user by ID');
$I->sendGET("?r=user%2Fview&id=".$userId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'UserID' => $userId[0],
		'UserName' => 'Yasmin',
]);

$I->wantTo('delete user by id');
$I->sendDELETE('?r=user%2Fdelete&id='.$userId[0]);
$I->seeResponseCodeIs(204);