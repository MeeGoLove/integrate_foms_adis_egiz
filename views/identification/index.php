<?php

/* @var $this yii\web\View */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
$this->title = 'Тестовый модуль по идентификации пациентов';

     $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); 
     
     
     
     ?>
        <?= $form->field($model, 'surname')->textInput(['autofocus' => true])?>
        <?= $form->field($model, 'name')->textInput(['autofocus' => true])?>
        <?= $form->field($model, 'patrName')->textInput(['autofocus' => true])?>
        <?= $form->field($model, 'gender')->textInput(['autofocus' => true])?>
<?= $form->field($model, 'birthDate')->textInput(['autofocus' => true])?>
        <?= $form->field($model, 'docTypeId')->textInput(['autofocus' => true])?>
        <?= $form->field($model, 'docNumber')->textInput(['autofocus' => true])?>
        <?= $form->field($model, 'docSeries')->textInput(['autofocus' => true])?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Search', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>