<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Sync1cEgisAdis */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sync1c-egis-adis-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'tab1c')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'surname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'patrname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'snils')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'birthday')->textInput() ?>

    <?= $form->field($model, 'job')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codeadis')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nameadis')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dradis')->textInput() ?>

    <?= $form->field($model, 'tab1cadis')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'egis_id')->textInput() ?>

    <?= $form->field($model, 'adis_to_1c_syncdate')->textInput() ?>

    <?= $form->field($model, 'egis_sync_date')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить запись', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
