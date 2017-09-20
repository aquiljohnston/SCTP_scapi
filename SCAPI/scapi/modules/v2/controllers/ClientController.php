<?php

namespace app\modules\v2\controllers;

use app\authentication\TokenAuth;
use Yii;
use app\modules\v1\models\Client;
use app\modules\v1\models\SCUser;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use app\modules\v2\models\BaseActiveRecord;

/**
 * ClientController implements the CRUD actions for Client model.
 */
class ClientController extends BaseActiveController
{
	public $modelClass = 'app\modules\v1\models\Client'; // TODO: Change to v2 when Michael commits the new model

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		unset($actions['create']);
		unset($actions['update']);
		unset($actions['delete']);
		return $actions;
	}

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //Implements Token Authentication to check for Auth Token in Json Header
        $behaviors['authenticator'] =
            [
                'class' => TokenAuth::className(),
            ];
        $behaviors['verbs'] =
            [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-all' => ['get'],
                    'deactivate' => ['post'],
                    'view' => ['get'],
                    'create' => ['post'],
                    'update' => ['put'],
                    'get-client-dropdowns' => ['get']
                ],
            ];
        return $behaviors;
    }

	/**
	 * Gets all of the class's model's records
	 *
	 * @return Response The records in a JSON format
	 * @throws \yii\web\HttpException 400 if any exceptions are thrown
	 */
	public function actionGetAll($listPerPage = null, $page = null,
                                 $filter = null, $filterclientname = null,
                                 $filtercity = null, $filterstate = null)
	{		
		try
		{
			//set db target
			BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('clientGetAll');


            //initialize response array
            $responseArray['assets'] = [];
            $responseArray['pages'] = [];

			$clientQuery = Client::find();
            if($filter != null)
            {
                $clientQuery->filterWhere([
                    'or',
                    ['like', 'ClientName', $filter],
                    ['like', 'ClientCity', $filter],
                    ['like', 'ClientState', $filter],
                    ['like', 'ClientContactFName', $filter],
                    ['like', 'ClientContactLName', $filter],
                    ['like', 'ClientPhone', $filter],
                    ['like', 'ClientEmail', $filter],
                    ['like', 'ClientAddr1', $filter],
                    ['like', 'ClientAddr2', $filter],
                    ['like', 'ClientTerritory', $filter],
                    ['like', 'ClientComment', $filter]
                ]);
            }
            if($filterclientname != null) {
                $clientQuery->andWhere([
                    'like', 'ClientName', $filterclientname
                ]);
            }
            if($filtercity != null) {
                $clientQuery->andWhere([
                    'like', 'ClientCIty', $filtercity
                ]);
            }
            if($filterstate != null) {
                $clientQuery->andWhere([
                    'like', 'UserLastName', $filterstate
                ]);
            }
            if ($page != null)
            {
                //pass query with pagination data to helper method
                $paginationResponse = self::paginationProcessor($clientQuery, $page, $listPerPage);
                //use updated query with pagination caluse to get data
                $clientArr = $paginationResponse['Query']->all();
                $responseArray['pages'] = $paginationResponse['pages'];
            }
            else
            {
                //if no pagination params were sent use base query
                $clientArr = $clientQuery->all();
            }
            $responseArray['assets'] = $clientArr;
            $response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->setStatusCode(200);
			$response->data = $responseArray;

			return $response;
		}
		catch(\Exception $e)
		{
			throw new \yii\web\HttpException(400);
		}
	}

	use DeleteMethodNotAllowed;

	public function actionDeactivate($id) {
	    try {
            BaseActiveRecord::setClient(BaseActiveController::urlPrefix());
            PermissionsController::requirePermission('clientDeactivate');
            $client = Client::find()->where(['ClientID' => $id])->one();
            if ($client !== null) {
                $client->delete();
            } else {
                // This is actually a bad request exception
                throw new BadRequestHttpException();
            }
            $this->redirect(['client/index']);
        } catch(ForbiddenHttpException $forbiddenHttpException) {
	        // We want the user to see the 403 error
            throw $forbiddenHttpException;
        } catch(\Exception $e) {
	        // This should be a 500 - Internal Server Error but I'm outranked.
	        throw new BadRequestHttpException();
            //throw $e;
        }
    }
	public function actionView($id, $joinNames = false)
	{		
		try
		{
			//set db target
			Client::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('clientView');
			
            if($joinNames) {
                $sql = "IF EXISTS (SELECT * FROM ClientTb WHERE ClientTb.ClientModifiedBy != 0 AND ClientTb.ClientId = :id1 )"
                . " BEGIN SELECT ModifiedUser.UserName as ModifiedUserName, ModifiedUser.UserID as ModifiedUserID, CreatedUser.UserID as CreatedUserID, CreatedUser.UserName as CreatedUserName, ClientTb.*"
			    . " FROM dbo.ClientTb JOIN [UserTb] ModifiedUser ON ClientTb.ClientModifiedBy = ModifiedUser.UserID"
                . " JOIN [UserTb] CreatedUser ON ClientTb.ClientCreatorUserID = CreatedUser.UserID"
                . " WHERE ClientTb.ClientId = :id2 END ELSE"
                . " SELECT CreatedUser.UserID as CreatedUserID, CreatedUser.UserName as CreatedUserName, ClientTb.*,"
                . " 'Not Modified' as ModifiedUserName, 0 as ModifiedUserID"
				. " FROM dbo.ClientTb"
                . " JOIN [UserTb] CreatedUser ON ClientTb.ClientCreatorUserID = CreatedUser.UserID"
                . " WHERE ClientTb.ClientId = :id3";
                $client = Client::getDb()->createCommand($sql)->bindValue(':id1', $id)->bindValue(':id2', $id)->bindValue(':id3', $id);
                Yii::trace("This is the client controller SQL " . $client->getSql());
                    $client = $client
                    ->queryOne();
            } else {
                $client = Client::findOne($id);
            }


			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			$response->data = $client;
			
			return $response;
		}
        catch(ForbiddenHttpException $e)
        {
            throw new ForbiddenHttpException;
        }
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	
	public function actionCreate()
	{		
		try
		{
			//set db target
			Client::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('clientCreate');
			
			$post = file_get_contents("php://input");
			$data = json_decode($post, true);

			$model = new Client(); 
			$model->attributes = $data;  
			$model->ClientCreatorUserID = self::getUserFromToken()->UserID;
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			//create date
			$model->ClientCreateDate = Parent::getDate();
			
			if($model-> save())
			{
				$response->setStatusCode(201);
				$response->data = $model; 
			}
			else
			{
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	public function actionUpdate($id)
	{		
		try
		{
			//set db target
			Client::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('clientUpdate');
			
			$put = file_get_contents("php://input");
			$data = json_decode($put, true);

			$model = Client::findOne($id);
			
			$model->attributes = $data;  
			$model->ClientModifiedBy = self::getUserFromToken()->UserID;
			
			$response = Yii::$app->response;
			$response ->format = Response::FORMAT_JSON;
			
			$model->ClientModifiedDate = Parent::getDate();
			
			if($model-> update())
			{
				$response->setStatusCode(201);
				$response->data = $model; 
			}
			else
			{
				$response->setStatusCode(400);
				$response->data = "Http:400 Bad Request";
			}
			return $response;
		}
		catch(\Exception $e)  
		{
			throw new \yii\web\HttpException(400);
		}
	}
	
	
	//return a json containing pairs of ClientID and ClientName
	public function actionGetClientDropdowns()
	{		
		try
		{
			//set db target
			Client::setClient(BaseActiveController::urlPrefix());
			
			// RBAC permission check
			PermissionsController::requirePermission('clientGetDropdown');
		
			$clients = Client::find()
				->orderBy('ClientName')
				->all();
			$namePairs = [];
			$clientSize = count($clients);
			
			for($i=0; $i < $clientSize; $i++)
			{
				$namePairs[$clients[$i]->ClientID]= $clients[$i]->ClientName;
			}
				
			
			$response = Yii::$app ->response;
			$response -> format = Response::FORMAT_JSON;
			$response -> data = $namePairs;
			
			return $response;
		}
		catch(\Exception $e) 
		{
			throw new \yii\web\HttpException(400);
		}
	}
}
