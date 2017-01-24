<?php

namespace app\modules\v1\controllers;

use Yii;
use app\modules\v1\models\Equipment;
use app\modules\v1\models\SCUser;
use app\modules\v1\models\ProjectUser;
use app\modules\v1\models\DailyEquipmentCalibrationVw;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\data\Pagination;

class EquipmentController extends BaseActiveController
{
    public $modelClass = 'app\modules\v1\models\Equipment';
    public $equipment;

    /**
     * Activates VerbFilter behaviour
     * See documentation on behaviours at http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html
     * @return array An array containing behaviours
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //Implements Token Authentication to check for Auth Token in Json  Header
        $behaviors['verbs'] =
            [
                'class' => VerbFilter::className(),
                'actions' => [
                    'accept-equipment' => ['put'],
                    'get-equipment-by-manager' => ['get'],
                    'view-all-by-user-by-project' => ['get'],
                    'equipment-view' => ['get'],
                ],
            ];
        return $behaviors;
    }

    /**
     * Unsets the view and update actions to prevent security holes.
     * @return array An array containing parent's actions with view and update removed
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * Finds a specific Equipment based on ID and returns it to the client. Otherwise
     * returns a 404.
     *
     * @param $id int ID of the Equipment to view
     * @return Response
     * @throws \yii\web\HttpException 400 when Exception thrown
     */
    public function actionView($id)
    {
        try {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //set db target
            Equipment::setClient(BaseActiveController::urlPrefix());

            // RBAC permission check
            PermissionsController::requirePermission('equipmentView');

            if ($equipment = Equipment::findOne($id)) {
                $response->data = $equipment;
                $response->setStatusCode(200);
            } else {
                $response->setStatusCode(404);
            }

            return $response;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * Creates an Equipment based on POST input and saves it. If it can not save it returns at 400 status code.
     *
     * @return Response
     * @throws \yii\web\HttpException When saving the model fails
     */
    public function actionCreate()
    {
        try {
            //set db target
            $headers = getallheaders();
            Equipment::setClient(BaseActiveController::urlPrefix());

            // RBAC permission check
            PermissionsController::requirePermission('equipmentCreate');

            $post = file_get_contents("php://input");
            $data = json_decode($post, true);

            $model = new Equipment();
            $model->attributes = $data;
            $model->EquipmentCreatedBy = self::getUserFromToken()->UserID;
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //create date
            $model->EquipmentCreateDate = Parent::getDate();

            if ($model->save()) {
                $response->setStatusCode(201);
                $response->data = $model;
            } else {
                $response->setStatusCode(400);
                $response->data = "Http:400 Bad Request";
            }
            return $response;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * Updates an Equipment model with JSON data from POST.
     *
     * @param $id int The ID of the model to update
     * @return Response JSON object of updated model.
     * @throws \yii\web\HttpException
     */
    public function actionUpdate($id)
    {
        try {
            //set db target
            Equipment::setClient(BaseActiveController::urlPrefix());

            // RBAC permission check
            PermissionsController::requirePermission('equipmentUpdate');

            $put = file_get_contents("php://input");
            $data = json_decode($put, true);

            $model = Equipment::findOne($id);
            $currentProject = $model->EquipmentProjectID;

            $model->attributes = $data;

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model->EquipmentModifiedDate = Parent::getDate();
            $model->EquipmentModifiedBy = self::getUserFromToken()->UserID;

            //TODO set flag to "Pending" when the project information is changed.
            if ($model->EquipmentProjectID != $currentProject) {
                $model->EquipmentAcceptedFlag = "Pending";
                $model->EquipmentAcceptedBy = null;
            }

            if ($model->update()) {
                $response->setStatusCode(200);
                $response->data = $model;
            } else {
                $response->setStatusCode(400);
                $response->data = "Http:400 Bad Request";
            }
            return $response;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    public function actionGetEquipment($listPerPage = null, $page = null)
    {
        // Will combine actionViewAllByUserByProject and actionEquipmentView
        // after rbac is complete
        try {
            //set db target
            DailyEquipmentCalibrationVw::setClient(BaseActiveController::urlPrefix());

            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            if (PermissionsController::can('getAllEquipment')) {
                $equipments = DailyEquipmentCalibrationVw::find();
                $paginationResponse = self::paginationProcessor($equipments, $page, $listPerPage);
                $equipmentsArr = $paginationResponse['Query']->all();
                $responseArray['assets'] = $equipmentsArr;
                $responseArray['pages'] = $paginationResponse['pages'];

                if ($responseArray['assets']) {
                    $response->data = $responseArray;
                    $response->setStatusCode(200);
                } else {
                    $response->setStatusCode(404);
                }
                //format response
            } else if (PermissionsController::can('getOwnEquipment')) {
                //get user project relations array
                $userId = parent::getUserFromToken()->UserID;

                Yii::trace("user id: $userId");

                $projects = ProjectUser::find()
                    ->where("ProjUserUserID = $userId")
                    ->all();
                $projectsSize = count($projects);

                if ($projectsSize > 0) {
                    $equipments = DailyEquipmentCalibrationVw::find()->where(['EquipmentProjectID' => $projects[0]->ProjUserProjectID]);

                    //loop user project array get all equipment WHERE equipmentProjectID is equal
                    for ($i = 0; $i < $projectsSize; $i++) {
                        $projectID = $projects[$i]->ProjUserProjectID;
                        $equipments->addWhere(['EquipmentProjectID' => $projectID]);
                    }
                    $paginationResponse = self::paginationProcessor($equipments, $page, $listPerPage);
                    $equipmentsArr = $paginationResponse['Query']->all();
                    $responseArray['assets'] = $equipmentsArr;
                    $responseArray['pages'] = $paginationResponse['pages'];

                    $response->data = $responseArray;
                    $response->setStatusCode(200);
                } else {
                    $response->setStatusCode(404);
                }
            } else {
                throw new ForbiddenHttpException;
            }

            return $response;
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    /**
     * Accepts one or more Equipments.
     *
     * Changes the flag on each Equipment that it receives to indicate that they are accepted.
     *
     * @return Response
     * @throws \yii\web\HttpException
     */
    public function actionAcceptEquipment()
    {
        try {
            //set db target
            Equipment::setClient(BaseActiveController::urlPrefix());

            // RBAC permission check
            PermissionsController::requirePermission('acceptEquipment');

            //capture put body
            $put = file_get_contents("php://input");
            $data = json_decode($put, true);

            //create response
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //get userID
            $acceptedBy = self::getUserFromToken()->UserID;

            //parse json
            $equipmentIDs = $data["equipmentIDArray"];

            //get equipment
            foreach ($equipmentIDs as $id) {
                $approvedEquipment[] = Equipment::findOne($id);
            }

            //try to accept equipment
            try {
                //create transaction
                $connection = \Yii::$app->db;
                $transaction = $connection->beginTransaction();

                foreach ($approvedEquipment as $equipment) {
                    $equipment->EquipmentAcceptedFlag = "Yes";
                    $equipment->EquipmentAcceptedBy = $acceptedBy;
                    $equipment->update();
                }
                $transaction->commit();
                $response->setStatusCode(200);
                $response->data = $approvedEquipment;
                return $response;
            } //if transaction fails rollback changes and send error
            catch (Exception $e) {
                throw $e;
                $transaction->rollBack();
                $response->setStatusCode(400);
                $response->data = "Http:400 Bad Request";
                return $response;
            }
        } catch (\Exception $e) {
            throw new \yii\web\HttpException(400);
        }
    }

    public function paginationProcessor($assetQuery, $page, $listPerPage)
    {

        if ($page != null) {
            // set pagination
            $countAssetQuery = clone $assetQuery;
            $pages = new Pagination(['totalCount' => $countAssetQuery->count()]);
            $pages->pageSizeLimit = [1, 100];
            $offset = $listPerPage * ($page - 1);
            $pages->setPageSize($listPerPage);
            $pages->pageParam = 'equipmentPage';
            $pages->params = ['per-page' => $listPerPage, 'equipmentPage' => $page];

            $assetQuery->offset($offset)
                ->limit($listPerPage);

            $asset['pages'] = $pages;
            $asset['Query'] = $assetQuery;

            return $asset;
        }
    }
}