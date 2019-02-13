<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Sync1cEgisAdis */

$this->title = 'Создание медика';
$this->params['breadcrumbs'][] = ['label' => 'Создание медика', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sync1c-egis-adis-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
