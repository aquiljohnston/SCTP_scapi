<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Client */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ClientName')->textInput() ?>

    <?= $form->field($model, 'ClientContactTitle')->textInput() ?>

    <?= $form->field($model, 'ClientContactFName')->textInput() ?>

    <?= $form->field($model, 'ClientContactMI')->textInput() ?>

    <?= $form->field($model, 'ClientContactLName')->textInput() ?>

    <?= $form->field($model, 'ClientPhone')->textInput() ?>

    <?= $form->field($model, 'ClientEmail')->textInput() ?>

    <?= $form->field($model, 'ClientAddr1')->textInput() ?>

    <?= $form->field($model, 'ClientAddr2')->textInput() ?>

    <?= $form->field($model, 'ClientCity')->textInput() ?>

    <?= $form->field($model, 'ClientState')->textInput() ?>

    <?= $form->field($model, 'ClientZip4')->textInput() ?>

    <?= $form->field($model, 'ClientTerritory')->textInput() ?>

    <?= $form->field($model, 'ClientActiveFlag')->textInput() ?>

    <?= $form->field($model, 'ClientDivisionsFlag')->textInput() ?>

    <?= $form->field($model, 'ClientComment')->textInput() ?>

    <?= $form->field($model, 'ClientCreateDate')->textInput() ?>

    <?= $form->field($model, 'ClientCreatorUserID')->textInput() ?>

    <?= $form->field($model, 'ClientModifiedDate')->textInput() ?>

    <?= $form->field($model, 'ClientModifiedBy')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
