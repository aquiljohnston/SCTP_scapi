<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Client */

$this->title = $model->ClientID;
$this->params['breadcrumbs'][] = ['label' => 'Clients', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->ClientID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->ClientID], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'ClientID',
            'ClientName',
            'ClientContactTitle',
            'ClientContactFName',
            'ClientContactMI',
            'ClientContactLName',
            'ClientPhone',
            'ClientEmail:email',
            'ClientAddr1',
            'ClientAddr2',
            'ClientCity',
            'ClientState',
            'ClientZip4',
            'ClientTerritory',
            'ClientActiveFlag',
            'ClientDivisionsFlag',
            'ClientComment',
            'ClientCreateDate',
            'ClientCreatorUserID',
            'ClientModifiedDate',
            'ClientModifiedBy',
        ],
    ]) ?>

</div>
