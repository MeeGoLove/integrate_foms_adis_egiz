<?php
/* @var $this yii\web\View */

use yii\grid\GridView;
?>
<h1>smp/index</h1>

<p>
    You may change the content of this page by modifying
    the file <code><?= __FILE__; ?></code>.
</p>

<?=
GridView::widget([
    'dataProvider' => $calls,
    'columns' => [
        //['class' => 'yii\grid\SerialColumn'],
        'год. №',
            [
            'attribute' => 'dprm',
            'value' => function ($data) {
                return date("d.m.Y", strtotime($data["dprm"]))                ;
            }],
        'diag'
    /* 'rezl',
      'fam:ntext',
      'imya:ntext',
      'otch:ntext',
      'povd:ntext',
      'vozr:ntext',
      'ds1' */
    ],
]);
?>