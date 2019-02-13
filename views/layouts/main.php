<?php
/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>

        <div class="wrap">
            <?php
            NavBar::begin([
                'brandLabel' => Yii::$app->name,
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                ],
            ]);
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    ['label' => 'Графики выгрузки в ЕГИСЗ', 'url' => ['/site/index']],
                    ['label' => 'Выгрузка счетов', 'items' => [
                            ['label' => '1 шаг выгрузки', 'url' => ['/foms/foms-first-step']],
                            ['label' => '2 шаг выгрузки', 'url' => ['/foms/foms-second-step']],
                            ['label' => '3 шаг выгрузки', 'url' => ['/foms/foms-third-step']],
                        ]],
                    ['label' => 'МЭК', 'url' => ['/foms/mek']],
					['label' => 'Незастрахованные', 'items' => [
                            ['label' => '1. Для идентификации в ТФОМС', 'url' => ['/insured/not-insured']],
                            ['label' => '2. Генерация реестра', 'url' => ['/insured/generate-insured']],
                            ['label' => '3. Для статиста и стола справок', 'url' => ['/insured/report-insured']]]],                    
					['label' => 'Медики[не лезть!]', 'url' => ['/medics/index']],
                    Yii::$app->user->isGuest ? (
                            ['label' => 'Вход', 'url' => ['/site/login']]
                            ) : (
                            '<li>'
                            . Html::beginForm(['/site/logout'], 'post')
                            . Html::submitButton(
                                    'Выйти (' . Yii::$app->user->identity->username . ')', ['class' => 'btn btn-link logout']
                            )
                            . Html::endForm()
                            . '</li>'
                            )
                ],
            ]);
            NavBar::end();
            ?>

            <div class="container">
<?=
Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
])
?>
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <p class="pull-left">&copy; devel-su Developer [from Serj] <?= date('Y') ?></p>

                <p class="pull-right"><?= Yii::powered() ?></p>
            </div>
        </footer>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
