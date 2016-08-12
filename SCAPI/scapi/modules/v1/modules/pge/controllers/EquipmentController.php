<?php

namespace app\modules\v1\modules\pge\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\authentication\TokenAuth;
use app\modules\v1\controllers\BaseActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

class EquipmentController extends Controller 
{

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = 
		[
			'class' => TokenAuth::className(),
		];
		$behaviors['verbs'] = 
			[
                'class' => VerbFilter::className(),
                'actions' => [
					'get' => ['get']
                ],  
            ];
		return $behaviors;	
	}
	
	public function actionGet($LANID)
	{
		try
		{
			$data = [];
			
			$equipment1 = [];
			$equipment1['EQOBJTYPE'] = 'G_COGIDPIR';
			$equipment1['EQSERNO'] = 'GI_9001217011';
			$equipment1['MWC'] = 'Fresno';
			$equipment1['CALBDATE'] = '07042016';
			$equipment1['LASTCALBSTAT'] = 'PASS';
			$equipment1['MPRNO'] = '000111193000';
			$equipment1['UPDATEFLAG'] = '';
			$equipment1['MPR_STAT'] = '?';
			$equipment1['USEDYESTERDAY'] = '1';
			$equipment1['EquipmentCalibrated'] = '?';
			$equipment1['CALBTIME'] = '071443';
			$equipment1['CALBSTAT'] = 'PASS';
			$equipment1['SRVYLANID'] = 'JOSE';
			$equipment1['SPVRLANID'] = 'SUP1';
			$equipment1['CALBHRS'] = '.025';
			
			$equipment2 = [];
			$equipment2['EQOBJTYPE'] = 'G_COGIRMLD';
			$equipment2['EQSERNO'] = 'GI_7003';
			$equipment2['MWC'] = 'RICHMOND';
			$equipment2['CALBDATE'] = '07042016';
			$equipment2['LASTCALBSTAT'] = 'PASS';
			$equipment2['MPRNO'] = '000111193000';
			$equipment2['UPDATEFLAG'] = '';
			$equipment2['MPR_STAT'] = '?';
			$equipment2['USEDYESTERDAY'] = '0';
			$equipment2['EquipmentCalibrated'] = '?';
			$equipment2['CALBTIME'] = '093856';
			$equipment2['CALBSTAT'] = 'OOT';
			$equipment2['SRVYLANID'] = 'PGE1';
			$equipment2['SPVRLANID'] = 'SUP1';
			$equipment2['CALBHRS'] = '.025';
			
			$equipment3 = [];
			$equipment3['EQOBJTYPE'] = 'G_COGIFMPK';
			$equipment3['EQSERNO'] = 'GI_9560-5';
			$equipment3['MWC'] = 'SNJOSE';
			$equipment3['CALBDATE'] = '07042016';
			$equipment3['LASTCALBSTAT'] = 'PASS';
			$equipment3['MPRNO'] = '000111193000';
			$equipment3['UPDATEFLAG'] = '';
			$equipment3['MPR_STAT'] = '?';
			$equipment3['USEDYESTERDAY'] = '1';
			$equipment3['EquipmentCalibrated'] = '?';
			$equipment3['CALBTIME'] = '071443';
			$equipment3['CALBSTAT'] = 'OOS';
			$equipment3['SRVYLANID'] = 'PGE1';
			$equipment3['SPVRLANID'] = 'SUP1';
			$equipment3['CALBHRS'] = '.025';
			
			$equipment4 = [];
			$equipment4['EQOBJTYPE'] = 'G_COGIFMPK';
			$equipment4['EQSERNO'] = 'GI_900121770';
			$equipment4['MWC'] = 'Fresno';
			$equipment4['CALBDATE'] = '07042016';
			$equipment4['LASTCALBSTAT'] = 'PASS';
			$equipment4['MPRNO'] = '000111193000';
			$equipment4['UPDATEFLAG'] = '';
			$equipment4['MPR_STAT'] = '?';
			$equipment4['USEDYESTERDAY'] = '0';
			$equipment4['EquipmentCalibrated'] = '?';
			$equipment4['CALBTIME'] = '071443';
			$equipment4['CALBSTAT'] = 'PASS';
			$equipment4['SRVYLANID'] = 'PGE1';
			$equipment4['SPVRLANID'] = 'SUP1';
			$equipment4['CALBHRS'] = '.025';
			
			$equipment5 = [];
			$equipment5['EQOBJTYPE'] = 'G_COGIOMD';
			$equipment5['EQSERNO'] = 'GI_900121754';
			$equipment5['MWC'] = 'STCRUZ';
			$equipment5['CALBDATE'] = '07042016';
			$equipment5['LASTCALBSTAT'] = 'PASS';
			$equipment5['MPRNO'] = '000111193000';
			$equipment5['UPDATEFLAG'] = '';
			$equipment5['MPR_STAT'] = '?';
			$equipment5['USEDYESTERDAY'] = '0';
			$equipment5['EquipmentCalibrated'] = '?';
			$equipment5['CALBTIME'] = '071443';
			$equipment5['CALBSTAT'] = 'PASS';
			$equipment5['SRVYLANID'] = 'JOSE';
			$equipment5['SPVRLANID'] = 'SUP1';
			$equipment5['CALBHRS'] = '.025';
			
			$equipment6 = [];
			$equipment6['EQOBJTYPE'] = 'G_COGISCOP';
			$equipment6['EQSERNO'] = 'GI_900121793';
			$equipment6['MWC'] = 'NAOA';
			$equipment6['CALBDATE'] = '07042016';
			$equipment6['LASTCALBSTAT'] = 'PASS';
			$equipment6['MPRNO'] = '000111193000';
			$equipment6['UPDATEFLAG'] = '';
			$equipment6['MPR_STAT'] = '?';
			$equipment6['USEDYESTERDAY'] = '0';
			$equipment6['EquipmentCalibrated'] = '?';
			$equipment6['CALBTIME'] = '071443';
			$equipment6['CALBSTAT'] = 'PASS';
			$equipment6['SRVYLANID'] = 'PGE1';
			$equipment6['SPVRLANID'] = 'SUP1';
			$equipment6['CALBHRS'] = '.025';
			
			$equipment7 = [];
			$equipment7['EQOBJTYPE'] = 'G_COGIPICA';
			$equipment7['EQSERNO'] = 'GI_FDDS2018';
			$equipment7['MWC'] = '';
			$equipment7['CALBDATE'] = '07042016';
			$equipment7['LASTCALBSTAT'] = 'PASS';
			$equipment7['MPRNO'] = '000111193000';
			$equipment7['UPDATEFLAG'] = '';
			$equipment7['MPR_STAT'] = '';
			$equipment7['USEDYESTERDAY'] = '0';
			$equipment7['EquipmentCalibrated'] = '';
			$equipment7['CALBTIME'] = '071443';
			$equipment7['CALBSTAT'] = 'PASS';
			$equipment7['SRVYLANID'] = 'PGE1';
			$equipment7['SPVRLANID'] = 'SUP1';
			$equipment7['CALBHRS'] = '.025';
			
			$data[] = $equipment1;
			$data[] = $equipment2;
			$data[] = $equipment3;
			$data[] = $equipment4;
			$data[] = $equipment5;
			$data[] = $equipment6;
			$data[] = $equipment7;
			
			//send response
			$response = Yii::$app->response;
			$response->format = Response::FORMAT_JSON;
			$response->data = $data;
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
}