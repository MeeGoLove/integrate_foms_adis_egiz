<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\controllers;

use yii;
use app\controllers\AppController;
use app\components\individuals\request\searchIndividual;
use app\components\individuals\request\getIndividual;
use app\components\individuals\request\getIndividualDocuments;
use app\components\individuals\request\getDocument;
use app\components\visits\request\getVisitById;
use app\models\InsuredXls;
use app\models\LethalityForm;
use app\models\SavedCalls;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice;

/**
 * Description of IdentificationController
 *
 * @author maimursv
 */
class IdentificationController extends AppController {

    /**
     * Идентификация человека, НЕ ДОРАБОТАНА!!!
     * @return string
     */
    public function actionIndex() {
        $x = "";
        $model = new searchIndividual();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
//$model->searchCode=""; 
            $result = Yii::$app->individuals->send($model);
//return var_dump($result);
            $pers = "";
            if (@gettype($result->individual) == "NULL") {

                $x = "No Found :-(<br>";
            } else
                foreach ($result->individual as $id) {
                    $ind = new getIndividual();
                    $ind->param = $id;
                    $req = Yii::$app->individuals->send_param($ind);
                    $fam = $req->surname;
                    $imya = $req->name;
                    $otch = $req->patrName;
                    $dr = date("d.m.Y", strtotime(str_replace("+05:00", "", $req->birthDate)));
                    $x = $x . "$fam $imya $otch $dr<br>";
                    /* foreach ($req->document as $docs)
                      {

                      } */
                }
            return $x;
        } else
            return $this->render('index', ['model' => $model]);
    }   

    
    
    /**
     * Досуточная летальность, проверяет, что человек не умер в течение суток
     * @return type
     */
    public function actionLethality() {
        $model = new LethalityForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $calls = SavedCalls::find()->where([">=", 'dprm', $model->start])
                    ->andWhere(["<=", 'dprm', $model->end])
                    ->asArray()
                    ->all();
            foreach ($calls as $call) {
//Чтобы сервер ЕГИЗ не лег от большого числа запросов
                usleep(250);
                $visitReq = new getVisitById();
                $visitReq->id = $call["visitId"];
                $visitResp = Yii::$app->visits->send($visitReq);
                if ($visitResp->visitResultId == 68) {
                    $ind = new getIndividual();
                    $ind->param = $call["patientId"];
                    if ($ind->validate()) {
                        $req = Yii::$app->individuals->send_param($ind);
//var_dump($req);
                        if (@gettype($req->deathDate) == "string") {
// echo date("d-m-Y", strtotime($req->deathDate));
                            $dead = strtotime(str_replace("+05:00", "", $req->deathDate));
                            $dprm = strtotime($call["dprm"]);
                            if ($dead - $dprm <= 24 * 60 * 60) {
                                echo "Пациент <b>" . $req->surname . " " . $req->name . " "
                                . $req->patrName . "</b> умер меньше чем за "
                                . "сутки после оказания скорой помощи "
                                . " <br>Дата смерти: " . date("d-m-Y", $dead)
                                . " <br>Дата вызова: " . date("d-m-Y", $dprm)
                                . " <br>№ карты вызова " . $call["ngod"]
                                . "<br><hr>";
                            }
                        }
                    }
                };
//var_dump($call["ngod"]);
            }
        } else {
            return $this->render('lethality', ['model' => $model]);
        }
    }
}
