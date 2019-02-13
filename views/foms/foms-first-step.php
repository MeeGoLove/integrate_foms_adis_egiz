<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use unclead\multipleinput\MultipleInput;
use yii\widgets\Pjax;

#use kartik\date\DatePicker;
$this->title = 'Выгрузка в ФОМС';
?>


<h3>1 шаг выгрузки</h3>
<p>Введите даты суббот и воскресений отчетного месяца, а также все дни в которые 
    не работала неотложная помощь поликлиник</p>
<p>Праздничные дни заносите в воскресенья</p>
<p>Таким образом Вы указываете дни, в которые службы неотложной помощи не работали</p>
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
$form->field($model, 'saturdays')->widget(MultipleInput::className(), [
    'max' => 15,
    'columns' => [
        [
            'name' => 'saturdays',
            'type' => \kartik\date\DatePicker::className(),
            'options' => [
                'data' => $model,
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
                ]
            ]
        ]
    ]
]);
?>
<?=
$form->field($model, 'sundays')->widget(MultipleInput::className(), [
    'max' => 15,
    'columns' => [
        [
            'name' => 'sundays',
            'type' => \kartik\date\DatePicker::className(),
            'options' => [
                'data' => $model,
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
                ]
            ]
        ]
    ]
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
<?= Html::submitButton('Запомнить субботы и воскресенья', ['class' => 'btn btn-success']); ?>
<?php ActiveForm::end();
?>
<?php
$js = <<< JS
        $('#model-saturdays').on('afterInit', function(){
            console.log('calls on after initialization event');
        }).on('beforeAddRow', function(e) {
            console.log('calls on before add row event');            
        }).on('afterAddRow', function(e, row) {
            console.log('calls on after add row event', $(row));
        }).on('beforeDeleteRow', function(e, item){
            console.log(item);
            console.log('calls on before remove row event');            
        }).on('afterDeleteRow', function(e, item){       
            console.log('calls on after remove row event');
            console.log('User_id:' + item.find('.list-cell__user_id').find('select').first().val());
        }).on('afterDropRow', function(e, item){       
            console.log('calls on after drop row', item);
        });
JS;
$this->registerJs($js);

$js = <<< JS
        $('#model-sundays').on('afterInit', function(){
            console.log('calls on after initialization event');
        }).on('beforeAddRow', function(e) {
            console.log('calls on before add row event');            
        }).on('afterAddRow', function(e, row) {
            console.log('calls on after add row event', $(row));
        }).on('beforeDeleteRow', function(e, item){
            console.log(item);
            console.log('calls on before remove row event');            
        }).on('afterDeleteRow', function(e, item){       
            console.log('calls on after remove row event');
            console.log('User_id:' + item.find('.list-cell__user_id').find('select').first().val());
        }).on('afterDropRow', function(e, item){       
            console.log('calls on after drop row', item);
        });
JS;
$this->registerJs($js);

$js = <<<JS
     $('form').on('beforeSubmit', function(){
	 var data = $(this).serialize();
	 $.ajax({
	    url: 'index.php?r=foms/foms-first-step',
	    type: 'POST',
	    data: data,
	    success: function(res){
	       alert('Даты успешно сохранены! Подождите немного пока перекомпилируется Zotonic!');
	    },
	    error: function(){
	       alert('Error!');
	    }
	 });
	 return false;
     });
JS;

$this->registerJs($js);
