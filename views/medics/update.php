<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Sync1cEgisAdis */

$this->title = 'Медики: ' . $model->surname . " " . $model->name . " " . $model->patrname;
$this->params['breadcrumbs'][] = ['label' => 'Медики', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->surname . " " . $model->name . " " . $model->patrname, 'url' => ['view', 'tab1c' => $model->tab1c, 'codeadis' => $model->codeadis]];
$this->params['breadcrumbs'][] = 'Изменение';
?>
<div class="sync1c-egis-adis-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
    ?>

</div>
