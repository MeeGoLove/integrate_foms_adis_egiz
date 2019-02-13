<?php

/* @var $this yii\web\View */

$this->title = 'Тестирование';

use skeeks\widget\highcharts\Highcharts;
use app\models\SavedCalls;

$calls = SavedCalls::findBySql("select count(*) as calls, date(dateSync) as day "
        . "from savedCalls group by date(dateSync) order by day desc  "
        . "limit 15")->asArray()->all();
$calls = array_reverse($calls);
$days = [];
$counts = [];
foreach ($calls as $day) {
    $days[]= date("m.d", strtotime($day["day"]));
    $counts[]= (int)$day["calls"];
}

echo Highcharts::widget([
    'options' => [
        'title' => ['text' => 'Выгружено в ЕГИСЗ по дням'],
        'xAxis' => [
            'categories' => $days
        ],
        'yAxis' => [
            'title' => ['text' => 'Число вызовов']
        ],
        'plotOptions' =>[
        'line' => [
            'dataLabels' => [
                'enabled' => true
            ],
            'enableMouseTracking' => false
        ]
],
        'series' => [
            ['name' => 'Успешно', 'data' => $counts]
        ]
    ]
        ]
);
?>
<p></p>
<style> hr {
height: 30px;
border-style: double;
border-color: red;
border-width: 1px 0 0 0;
border-radius: 20px;
}
hr:before {
display: block;
content: "";
height: 30px;
margin-top: -31px;
border-style: double;
border-color: red;
border-width: 0 0 1px 0;
border-radius: 20px;
}</style>
<hr size=10px color="red">
<p></p>
<?php
$calls = SavedCalls::findBySql("select count(*) as calls, dprm as day "
        . "from savedCalls group by dprm order by day desc  "
        . "limit 15")->asArray()->all();
$calls = array_reverse($calls);
$days = [];
$counts = [];
foreach ($calls as $day) {
    $days[]= date("m.d", strtotime($day["day"]));
    $counts[]= (int)$day["calls"];
}

echo Highcharts::widget([
    'options' => [
        'title' => ['text' => 'Выгружено в ЕГИСЗ по датам вызова'],
        'xAxis' => [
            'categories' => $days
        ],
        'yAxis' => [
            'title' => ['text' => 'Число вызовов']
        ],
        'plotOptions' =>[
        'line' => [
            'dataLabels' => [
                'enabled' => true
            ],
            'enableMouseTracking' => false
        ]
],
        'series' => [
            ['name' => 'Успешно', 'data' => $counts]
        ]
    ]
        ]
);
?>
<p></p>
<style> hr {
height: 30px;
border-style: double;
border-color: red;
border-width: 1px 0 0 0;
border-radius: 20px;
}
hr:before {
display: block;
content: "";
height: 30px;
margin-top: -31px;
border-style: double;
border-color: red;
border-width: 0 0 1px 0;
border-radius: 20px;
}</style>
<hr size=10px color="red">
<p></p>

<?php
$calls = SavedCalls::findBySql("select count(caseId) as cases, count(visitId) as "
        . "visit, count(serviceRendId) as rend,  dprm as day "
        . "from savedCalls group by dprm order by day desc  "
        . "limit 15")->asArray()->all();
$calls = array_reverse($calls);
$days = [];
$case = [];
$visit = [];
$rend = [];
foreach ($calls as $day) {
    $days[]= date("m.d", strtotime($day["day"]));
    $case[]= (int)$day["cases"];
    $visit[]= (int)$day["visit"];
    $rend[]= (int)$day["rend"];
}

echo Highcharts::widget([
    'options' => [
          'chart' => [
        'type' => 'line'
    ],
        
        
        
        'legend' => [        
        'shadow' => true
  ],
    'credits' => [
        'enabled' => false
    ],
        
        
        
        
        
        'title' => ['text' => 'Соотношение выгруженных случаев, визитов и услуг'],
        'xAxis' => [
            'categories' => $days
        ],
        'yAxis' => [
            'title' => ['text' => 'Количество'],
            'opposite' => true
            
        ],
                'plotOptions' =>[
        'line' => [
            'dataLabels' => [
                'enabled' => false
            ],
            'enableMouseTracking' => false
        ]
],
        'series' => [
            ['name' => 'Случаев', 'data' => $case],
            ['name' => 'Визитов', 'data' => $visit],
            ['name' => 'Услуг', 'data' => $rend],
        ]
    ]
        ]
);
?>