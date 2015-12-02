<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add a User to Project');

//Create User
$I->wantTo('Create User via API');
//$I->amHttpAuthenticated('user', 'password');
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('?r=user%2Fcreate', ['UserName' => 'CodeceptTester']);
$I->seeResponseCodeIs(201);
$I->seeResponseIsJson();
$userId = $I->grabDataFromResponseByJsonPath('$.UserID');

//Get User
$I->wantTo('GET user by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET("?r=user%2Fview&id=".$userId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'UserID' => $userId[0]
]);

//Add User to Project
$I->wantTo('Add a User to Project');
//$I->amHttpAuthenticated('user', 'password');
$I->sendPUT("?r=user%2Fadd-user-to-project&projectID=1&userID=".$userId[0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$projUserId = $I->grabDataFromResponseByJsonPath('$.ProjUserID');



//Get User Project Relation
$I->wantTo('View Project User Relation');
//$I->amHttpAuthenticated('user', 'password');
$I->sendGET('?r=project-user%2Fview&id='.$projUserId [0]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
		'ProjUserID' => $projUserId[0]
]);

//Remove User from Project
$I->wantTo('Remove User from Project');
//$I->amHttpAuthenticated('user', 'password');
$I->sendDELETE('?r=project-user%2Fdelete&id='.$projUserId [0]);
$I->seeResponseCodeIs(204);

//Delete User
$I->wantTo('DELETE User by ID');
//$I->amHttpAuthenticated('user', 'password');
$I->sendDELETE('?r=user%2Fdelete&id='.$userId[0]);
$I->seeResponseCodeIs(204);
