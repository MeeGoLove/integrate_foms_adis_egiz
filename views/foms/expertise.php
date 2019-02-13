<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use unclead\multipleinput\MultipleInput;
use yii\widgets\Pjax;

#use kartik\date\DatePicker;
$this->title = 'Экспертиза';
?>


<h3>Экспертиза</h3>
<p></p>
<?= $form->field($model, 'xlsFile')->fileInput() ?>
<?php
$form = ActiveForm::begin([
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
            'validateOnChange' => false,
            'validateOnSubmit' => true,
            'validateOnBlur' => false,
        ]);
?>


<?=
$form->field($model, 'start')->widget(kartik\date\DatePicker::className(), ['data' => $model,
    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
    'language' => 'ru',
    'removeButton' => false,
    'pluginOptions' => [
        'autoclose' => true,
        'format' => 'yyyy-mm-dd',
        'clearBtn' => false,
        'minViewMode' => 0,
        'maxViewMode' => 2,
        'width' => 80,
]]);
?>

<?=
$form->field($model, 'end')->widget(kartik\date\DatePicker::className(), ['data' => $model,
    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
    'language' => 'ru',
    'removeButton' => false,
    'pluginOptions' => [
        'autoclose' => true,
        'format' => 'yyyy-mm-dd',
        'clearBtn' => false,
        'minViewMode' => 0,
        'maxViewMode' => 2,
        'width' => 80,
]]);
?>
<?= Html::submitButton('Сформировать файл', ['class' => 'btn btn-success']); ?>
<?php ActiveForm::end();
?>
