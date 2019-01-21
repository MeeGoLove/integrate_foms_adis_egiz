<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = 'Выгрузка счетов в ФОМС ';
?>
<h3>3 шаг выгрузки</h3>
<p>Перед тем как выполнять этот шаг, убедитесь, что Вы успешно выполнили 1 и 2 шаг выгрузки</p>
<p>Также нужно, чтобы вызова неотложки, которые обслужила скорая с 10:00 
    по 16:00, были занесены через <a href="http://neotlojka.tl/index.php" target="_blank">эту страницу</a></p>
        <p>Если все верно,  смело нажимайте кнопочку "Выполнить 3 шаг выгрузки"</p>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
    <?= Html::submitButton('Выполнить 3 шаг выгрузки', ['class' => 'btn btn-success']);?>
<?php ActiveForm::end() ?>
<hr>
<h4>Результат работы (будет пусто если не нажимали кнопку):</h4>
        <?= $message ?>