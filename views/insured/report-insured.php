<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = 'Генерация файлов для стола справок и статиста';
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'xlsFile')->fileInput() ?>

    <button>Сформировать файлы</button>

<?php ActiveForm::end() ?>
