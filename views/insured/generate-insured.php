<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = 'Генерация незастрахованных';
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'xlsFile')->fileInput() ?>

<?= $form->field($model, 'dbfFile')->fileInput() ?>
<p>Если Вы хотите определенное число незастрахованных, то смените 0 на нужное число</p>
<?= $form->field($model, 'needInsuredCount')->textInput() ?>


<?= Html::submitButton('Сгенерировать незастрахованных', ['class' => 'btn btn-success']); ?>

<?php ActiveForm::end() ?>
<hr>
<h3>Результат работы (появится после нажатия на кнопку)</h3>
<?= $x ?>
