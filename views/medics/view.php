<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Sync1cEgisAdis */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sync1c Egis Adis', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sync1c-egis-adis-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'tab1c' => $model->tab1c, 'codeadis' => $model->codeadis], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'tab1c' => $model->tab1c, 'codeadis' => $model->codeadis], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'tab1c',
            'surname',
            'name',
            'patrname',
            'snils',
            'birthday',
            'job',
            'codeadis',
            'nameadis',
            'dradis',
            'tab1cadis',
            'egis_id',
            'adis_to_1c_syncdate',
            'egis_sync_date',
            'pol',
            'employment',
            'dismissal',
        ],
    ]) ?>

</div>
