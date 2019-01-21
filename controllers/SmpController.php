<?php

namespace app\controllers;

use app\models\ArchiveCalls;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

class SmpController extends \yii\web\Controller {

    public function actionIndex() {
        /* $neotlCalls = ArchiveCalls::findBySql("select * from archive_calls, cmpdiagn where

          dprm = curdate() and
          (povd in ('04Б', '04Г', '04Д', '11А', '11У', '11Я', '12Г', '12К', '12У', '12Я', '13С') or (povd = '13Я' and convert(vozr,signed)<='17'))
          and time(tprm)>=\"08:00:00\" and time(tprm)<=\"15:00:00\"
          and mest = 1 and ds1 = cmpdiagn.code"); */

        $neotlCalls = ArchiveCalls::find()->where("dprm>='2018/08/01'")
                ->andWhere("time(tprm)>=\"08:00:00\"")
                ->andWhere("time(tprm)<=\"15:00:00\"")
                ->andWhere("povd in ('04Б', '04Г', '04Д', '11А', '11У', '11Я', '12Г', '12К', '12У', '12Я', '13С') or (povd = '13Я' and convert(vozr,signed)<='17')")
                ->andWhere("mest = 1")
                ->andWhere("rezl < 90")
                ->Join('LEFT OUTER JOIN', 'cmpdiagn', 'cmpdiagn.code = archive_calls.ds1')
        ;


        /* $provider = new ActiveDataProvider([
          'query' => $neotlCalls, // запрос на выборку новостей
          'pagination' => [ //постраничная разбивка
          'pageSize' => 50, // 10 новостей на странице
          ],
          'sort' => [ // подключаем сортировку
          'attributes' => ['ngod', 'povd', 'vozr'],
          ],
          ]); */

        $provider = new SqlDataProvider([
            'db' => 'adisdb',
            'sql' => "select ngod as 'год. №', dprm, concat_ws(' ', ds1, cmpdiagn.name) as diag from archive_calls, cmpdiagn where	
	dprm = '2018/01/01' and	
	(povd in ('04Б', '04Г', '04Д', '11А', '11У', '11Я', '12Г', '12К', '12У', '12Я', '13С') or (povd = '13Я' and convert(vozr,signed)<='17'))
	and time(tprm)>=\"08:00:00\" and time(tprm)<=\"15:00:00\"
	and mest = 1 and ds1 = cmpdiagn.code
        and rezl <90", // запрос на выборку новостей
            'pagination' => [//постраничная разбивка
                'pageSize' => 50, // 10 новостей на странице
            ],
        ]);
        //var_dump($neotlCalls);
        return $this->render('index', ['calls' => $provider]);
    }

}
