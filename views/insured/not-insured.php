<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = 'Идентификация незастрахованных';
?>
<h3>Помощь</h3>
<p>Потом напишу((((</p>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'xlsFile')->fileInput() ?>

    <?= Html::submitButton('Отсеять лишних и сгенерировать запрос', ['class' => 'btn btn-success']); ?>

<?php ActiveForm::end() ?>
<hr>
<h3>Результат работы (появится после нажатия на кнопку)</h3>
<?= $x ?>
