<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Медики';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sync1c-egis-adis-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать медика вручную', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'tab1c',
            'surname',
            'name',
            'patrname',
            'snils',
            //'birthday',
            //'job',
            //'codeadis',
            //'nameadis',
            //'dradis',
            //'tab1cadis',
            //'egis_id',
            //'adis_to_1c_syncdate',
            //'egis_sync_date',
            //'pol',
            //'employment',
            //'dismissal',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
