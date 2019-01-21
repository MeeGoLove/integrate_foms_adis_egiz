<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = 'Досуточная летальность';
?>

<h3>Досуточная летальность</h3>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?= $form->field($model, 'start')->textInput() ?>
<?= $form->field($model, 'end')->textInput() ?>
    <button>Проверить</button>
<?php ActiveForm::end() ?>