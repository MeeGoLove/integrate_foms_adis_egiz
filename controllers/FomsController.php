<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

namespace app\controllers;

use yii;
use app\controllers\AppController;
use app\models\Weekdays;
use app\models\Dbf;
use app\models\ExpertParser;
use yii\web\UploadedFile;
use app\models\Neotlojka;
use app\models\ArchiveCalls;
use app\models\Sync1cEgisAdis;
use yii\web\Response;
use yii\bootstrap\ActiveForm;

/**
 * Description of FomsController
 *
 * @author maimursv
 */
class FomsController extends AppController {

    /**
     * 
     * Контроллер для первого шага выгрузки в ФОМС
     * @return type
     */
    public function actionFomsFirstStep() {
        $model = new Weekdays();
        $request = Yii::$app->getRequest();
        if ($request->isPost && $request->post('ajax') !== null) {
            $model->load(Yii::$app->request->post());
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = ActiveForm::validate($model);
            return $result;
        }
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validate()) {
                Yii::error('Validation errors: ' . print_r($model->getErrors(), true));
            } else {

                $model->writeDays($model);
            }
        }
        return $this->render
                        ('foms-first-step', ['model' => $model]);
    }

    /**
     * Контроллер для второго шага выгрузки
     * @param type $x
     * @return type
     */
    public function actionFomsSecondStep($x = "") {
        if (Yii::$app->request->isPost) {
            try {
                $remote_file = '/usr/home/zotonic/zotonic/output/101.dbf';
                $local_file = 'reestr/101/101.dbf';
                //удаление из dbf диагнозов на Z, кроме Z33
                $dbf = \dbase_open($local_file, 2);
                if ($dbf) {
                    $x = $x . "<p>Открыли dbf файл</p>";
                    for ($i = 1; $i <= \dbase_numrecords($dbf); $i++) {
                        $rec = \dbase_get_record_with_names($dbf, $i);
                        if (strlen($rec["DS1"]) != 0) {
                            if ($rec["DS1"][0] == "Z") {
                                if ($rec["DS1"][0] . $rec["DS1"][1] . $rec["DS1"][2] != "Z33") {
                                    \dbase_delete_record($dbf, $i);
                                    $x = $x . "<p>Удалена запись с диагнозом <b>" . $rec["DS1"] . "</b></p>";
                                }
                            }
                        }
                    }
                    \dbase_pack($dbf);
                    \dbase_close($dbf);
                }
                //Отправка файла на сервер Zotonic
                $mode = 'FTP_ASCII';
                $asynchronous = false;
                $file = Yii::$app->ftp->put($local_file, $remote_file, $mode, $asynchronous);
                $x = $x . "<p>Отправили файл на удаленный сервер</p>";
                sleep(10);
                $x = $x . "<p>Подождали 10 секунд</p>";
                //Вот тут попытка сделать за пользователя одно важное движение
                try {
                    @shell_exec('wget --timeout=1800 http://adisreports.prog/foms_reports');
                    $x = $x . "<p>Была предпринята попытка сформировать счет, вроде прошло все хорошо</p>";
                } catch (\Exception $ex) {
                    $x = $x . "<p>Была предпринята попытка сформировать счет, прошло"
                            . " все не очень хорошо</p>"
                            . "<p>Возникла ошибка " . $ex->getMessage() . "</p>";
                }
                $x = $x . "<p>Файл отправлен на удаленный сервер, для подстраховки через <b>5</b> минутперейдите "
                        . "по этой <a href='http://adisreports.prog/foms_reports' "
                        . "target='_blank'>ссылке</a> и дождитесь пока не появится"
                        . " <b>Ok</b></p>"
                        . "<p>После этого можно приступать к следующему шагу</p>";
            } catch (\Exception $ex) {
                $x = $x . "<p>При выполнении этого шага возникла фатальная "
                        . "ошибка!</p>"
                        . "<p>Свдения об ошибке:  <b>" . $ex->getMessage() . "</b></p>";
            }
            return $this->render('foms-second-step', ['x' => $x]);
        }
        return $this->render('foms-second-step', ['x' => $x]);
    }

    /**
     * Контроллер для третьего шага выгрузки
     * @param type $message
     * @return type
     */
    public function actionFomsThirdStep($message = "") {
        $start = date("Y-m-01", strtotime("-20 days"));
        $end = date("Y-m-t", strtotime("-20 days"));
        if (Yii::$app->request->isPost) {
            FomsController::generateNeotl($start, $end);
            $message = $message . "<p>Проверили неотложку и записали в файл</p>";
            $mode = 'FTP_ASCII';
            $asynchronous = false;
            $remote_file = "/usr/home/zotonic/zotonic/output/101hm.xml";
            $local_file = "reestr/101hm.xml";
            $file = Yii::$app->ftp->get($remote_file, $local_file, $mode, $asynchronous);
            $remote_file = "/usr/home/zotonic/zotonic/output/101lm.xml";
            $local_file = "reestr/101lm.xml";
            $file = Yii::$app->ftp->get($remote_file, $local_file, $mode, $asynchronous);
            $summ = FomsController::generateXML();
            $message = $message . "<p>Сгенерировали счет реестров</p>";
            FomsController::saveXmlWithHead($summ['sumvHM'], $summ['sumvCM']);
            $message = $message . "<p>Сохранили счет реестров</p>";
            FomsController::generateXmlMedics();
            FomsController::generateZip();
            FomsController::removeWorkFiles();
            $message = $message . "<p>Сгенерировали файл докторов</p>";
            $message = $message . "<p>ZIP архив реестра HM (основной счет) 
                <a href=\"reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) .
                    date("m", time() - 20 * 24 * 3600) . "101.zip\">скачать</a></p>
                        <p>ZIP архив реестра CM (онкологический счет) 
                <a href=\"reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) .
                    date("m", time() - 20 * 24 * 3600) . "101.zip\">скачать</a></p>";
            return $this->render('foms-third-step', ['message' => $message]);
        }
        return $this->render('foms-third-step', ['message' => $message]);
    }

    /**
     * Формирует файл в котором записыны годовые номера карточек, которые нужно 
     * подать как неотложку
     * @param type $start Дата начала
     * @param type $end Дата окончания
     */
    public static function generateNeotl($start, $end) {
        $calls = Neotlojka::findBySql("select * from neotlojka where "
                        . "dprm>=\"" . $start . "\" and dprm<=\"" . $end . "\"")->asArray()->all();
        $queryLine = " and (";
        $i = 0;
        foreach ($calls as $call) {
            if ($i == 0) {
                $queryLine = $queryLine . "(numv = " . $call["numv"] . " and dprm = \""
                        . "" . $call["dprm"] . "\")";
                $i++;
                continue;
            }
            $queryLine = $queryLine . "or (numv = " . $call["numv"] . " and dprm = \""
                    . "" . $call["dprm"] . "\")";
            $i++;
        }
        $queryLine = $queryLine . ")";
        $query = "select ngod from archive_calls where TIME(tprm) >=\"10:00:00\" "
                . "and TIME(tprm) <=\"16:00:00\" and rezl not in ('12', '16', "
                . "'17', '18', '20', '90', '91', '92', '93', '94', '95', '96', "
                . "'97','99') and rezl not in ('14','15') and "
                . "povd not in (\"41Е\", \"41Л\", \"41Н\", \"41П\", \"41Р\", "
                . "\"41Ф\", \"42Е\", \"42Л\", \"42Н\", \"42П\",\"42Р\", \"42Ф\")"
                . " and ds1 in ('I10','G90.9','J06.9','M06.9','M19.9','G64',"
                . "'G97','D84.9','H93.3','J03.9','I20.9','G96.9','I67.9', "
                . "'К31.9', 'К63.9', 'К86.9', 'N39.9', 'N64.9', 'N71.9') "
                . "and prof<>'П' and kod2 not in ('ПРИЕЗЖИЙ', 'БОМЖ') "
                . "and mest='1'" . $queryLine;
        $callsToWrite = "";
        if ($queryLine != " and ()") {
            $neotlCalls = ArchiveCalls::findBySql($query)->asArray()->all();
            foreach ($neotlCalls as $call) {
                $callsToWrite = $callsToWrite . $call["ngod"] . "\r\n";
            }
        }
        file_put_contents('reestr/calls.txt', $callsToWrite);
    }

    /**
     * Генерация счетов реестров
     * @return type
     */
    public static function generateXML() {
        /**
         * Скрипт для формирования счетов-реестров
         * Принимает файлы сформированные в Erlang, с прошлого месяца на добавление и номера карт на исправление 
         * вызова с обычного на неотложный
         * 
         * Баги:
         * 1. Не считает вызова с сумой 0.0 >>> Исправлено, считает
         * 2. Нет проверки на корректность соответствия результатов и исходов
         * 3. ....
         */
        //счетчики
        $medicsArray = [];
        $xMeds = 0;
        $start = date("h:i:s");
        $i = 0;
        $j = 0;
        $vr = 0;
        $feld = 0;
        $vrOnko = 0;
        $feldOnko = 0;
        $res = "";
        $sumv = 0;
        $sumvOnko = 0;
        $not_add = 0;
        $lhm_i = 0;
        $hm_i = 0;
        $hm_iOnko = 0;
        $neotl = 0;
        $vr_j = 0;
        $feld_j = 0;
        $otkl = 0;
        $not_add_lhm = 0;
        $log = "";
        //Стереть старые файлы
        @unlink("reestr/hm101.xml");
        @unlink("reestr/lhm101.xml");
        @unlink("reestr/cm101.xml");
        @unlink("reestr/chm101.xml");
        @unlink("reestr/hm101_add.xml");
        @unlink("reestr/lhm101_add.xml");
        @unlink("reestr/cm101_add.xml");
        @unlink("reestr/lcm101_add.xml");
        //открытие файла со списком неотложки в массив
        $file = "reestr/calls.txt";
        $arr = file($file);
        $count = count($arr);
        //Исправление файлов XML, полученных с сервера Zotonic
        $temp_file = file_get_contents("reestr/101hm.xml");
        $temp_file = str_replace("<SL_ID>", "<SL><SL_ID>", $temp_file);
        $temp_file = str_replace("</Z_SL>", "</SL></Z_SL>", $temp_file);
        //$x = preg_replace('/<\/[A-z_\d]+>/ui',"\\0\r\n", $x);
        file_put_contents("reestr/101hm.xml", $temp_file);
        sleep(2);
        $temp_file = file_get_contents("reestr/101lm.xml");
        $temp_file = str_replace("windows-1251", "utf-8", $temp_file);
        //$temp_file = preg_replace('/<\/[A-z_\d]+>/ui',"\\0\r\n", $temp_file);
        file_put_contents("reestr/101lm.xml", $temp_file);
        //Открытие XML файлов
        $xmlHM = simplexml_load_file("reestr/101hm.xml");
        $xmlLHM = simplexml_load_file("reestr/101lm.xml");
        $xmlHM_add = simplexml_load_file("reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101_add.xml");
        $xmlLHM_add = simplexml_load_file("reestr/LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101_add.xml");
        $xmlCM_add = simplexml_load_file("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101_add.xml");
        $xmlLCM_add = simplexml_load_file("reestr/LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101_add.xml");
        //Инициалиазации кучи переменных для подсчета
        $n_zap = 1; //Номер записи в основном реестре
        $n_zapOnko = 1; //Номер записи в онкологическом реестре
        $temp = 0;
        $temp1 = 0;
        $temp2 = 0;
        //обход основного hm-файла со случаями
        foreach ($xmlHM->ZAP as $hm) {
            //устранение бага с датой вызова (если время приема и приезда совпадает, вторая дата сдвигается на день вперед)
            //косяк Erlang который не осилил
            if ((string) $hm->Z_SL->SL->COMENTSL->TIME_MISSION === (string) $hm->Z_SL->SL->COMENTSL->TIME_CALL) {
                $hm->Z_SL->SL->DATE_2 = $hm->Z_SL->SL->DATE_1;
                $temp++;
                //если вызов неотложный, нужно еще поправить в узле USL
                if ((string) $hm->Z_SL->SUMV === "662.02") {
                    $temp1++;
                    $hm->Z_SL->SL->USL->DATE_OUT = $hm->Z_SL->SL->USL->DATE_IN;

                    //echo "Дата вызова неотложки поправлена, № карты: " . $hm->Z_SL->SL->NHISTORY . "\r\n";
                } else {
                    $temp2++;
                    //echo "Дата вызова поправлена, № карты: " . $hm->Z_SL->SL->NHISTORY . "\r\n";
                }
            }
            //добавить версию справочника специальностей и продублировать дату в случай
            $hm->Z_SL->SL->addChild('VERS_SPEC', 'V021');
            $hm->Z_SL->DATE_Z_1 = $hm->Z_SL->SL->DATE_1;
            $hm->Z_SL->DATE_Z_2 = $hm->Z_SL->SL->DATE_2;
            /**
             * Проверка на нулевое значение, исправляем на корректное
             */
            if ((string) $hm->Z_SL->SUMV === "0.00") {
                $log = $log . "Вызов с нулевой суммой! " . $hm->PACIENT->ID_PAC . "\r\n";
                $query = "SELECT tprm, przd from archive_calls where dprm=\"" .
                        $hm->Z_SL->DATE_Z_1 . "\" and ngod=\"" . $hm->Z_SL->SL->NHISTORY . "\";";
                $call = ArchiveCalls::findBySql($query)->asArray()->one();
                $time1 = date("H:i", strtotime($call['tprm']));
                $time2 = date("H:i", strtotime($call['przd']));


                $hm->Z_SL->SL->addChild('COMENTSL');
                $hm->Z_SL->SL->COMENTSL->addChild('LEVEL', 2);
                $hm->Z_SL->SL->COMENTSL->addChild('TIME_CALL', $time1);
                $hm->Z_SL->SL->COMENTSL->addChild('TIME_MISSION', $time2);
                if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "1") {
                    $hm->Z_SL->SUMV = 3725.53;
                    //echo $hm->Z_SL->SL->IDDOKT . " стоимость вызова 3725.53\r\n";
                }
                if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "2") {
                    $hm->Z_SL->SUMV = 1920.21;
                    //echo $hm->Z_SL->SL->IDDOKT . " стоимость вызова 1920.21\r\n";
                }
            }
            //если нет полиса, то нужно удалить узел VPOLIS, значение 0 недопустимо! 
            if ($hm->PACIENT->VPOLIS == "0") {
                unset($hm->PACIENT->VPOLIS);
            }
            //проверяем что вызов не переходит на следующий месяц
            $time_end = strtotime(date("Y-m") . "-01 00:00");
            //Для отладки!!!
            $time_end = strtotime("2019-01-30 00:00");
            $time_mission = strtotime((string) $hm->Z_SL->SL->DATE_2 . " " . (string) $hm->Z_SL->SL->COMENTSL->TIME_MISSION);
            //Проверка, что окончание вызова не ушли на следующий месяц
            if (($time_end - $time_mission) > 0) {
                //Проверка, что диагноз не входит в онкореестр
                //if (true) {
                if ($hm->Z_SL->SL->DS1 != "C97") {
                    //последовательно увеличиваем номер случая в реестре основном
                    $hm->N_ZAP = $n_zap;
                    $hm->Z_SL->IDCASE = $n_zap;
                    //по значению суммы определяем тип вызова
                    //                     +++НЕОТЛОЖКА+++
                    // до апреля P_CEL был 2 а не 1.1
                    //для неотложки добавляем узел P_CEL со значением 1.1                        
                    //Также для неотложки сверяем время прибытия, оно не должно быть после 19:00
                    if ((string) $hm->Z_SL->SUMV === "662.02") {
                        //Временное решение
                        //Поправить поля по регламенту на 2019 год
                        unset($hm->Z_SL->SL->USL->CODE_USL);
                        $hm->Z_SL->IDSP = 25;
                        //Дальше тоже правка полей 
                        $hm->Z_SL->SUMV = "662.02";
                        $hm->Z_SL->SL->SUM_M = "662.02";
                        $hm->Z_SL->SL->addChild('P_CEL', '1.1');
                        $neotl++; //счетчик неотложных вызовов

                        if ((date("H", $time_mission) >= 19)
                                or (
                                date("H", $time_mission) >= 14
                                and (
                                date("d", $time_mission) == 3
                                or date("d", $time_mission) == 4
                                or date("d", $time_mission) == 8)
                                )) {
                            //echo "<p style=\"color:#ff0000\">Неотложка после 19:00, карта " . $hm->Z_SL->SL->NHISTORY . "</p>";
                            unset($hm->Z_SL->SL->USL);
                            unset($hm->Z_SL->SL->COMENTSL->METHOD);
                            unset($hm->Z_SL->SL->P_CEL);
                            if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) == "1") {
                                $hm->Z_SL->SUMV = "3725.53";
                                $hm->Z_SL->SL->SUM_M = "3725.53";
                            }
                            if (substr($hm->Z_SL->SL->IDDOKT, 0, 1) >= 2) {
                                $hm->Z_SL->SUMV = "1920.21";
                                $hm->Z_SL->SL->SUM_M = "1920.21";
                            }
                            $hm->Z_SL->FOR_POM = 1;
                            $hm->Z_SL->IDSP = 36;
                            $neotl--;
                            $otkl++;
                        }
                    }
                    //                +++ОБЫЧНЫЕ ВЫЗОВЫ+++					
                    if ((string) $hm->Z_SL->SUMV === "1920.21") {
                        $hm->Z_SL->SUMV = "1920.21";
                        $hm->Z_SL->SL->SUM_M = "1920.21";
                        $feld++;
                    } //счетчик фельдшерских вызовов
                    if ((string) $hm->Z_SL->SUMV === "3725.53") {
                        $hm->Z_SL->SUMV = "3725.53";
                        $hm->Z_SL->SL->SUM_M = "3725.53";
                        $vr++;
                    } //счетчик врачебных вызовов
                    //генерируем SL_ID
                    $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);
                    $n_zap++; //увеличим номер в реестре
                    //foreach ($xml2->PERS as $lhm)
                    //{
                    //В файле LHM от Zotonic персональные данные идут в обратном порядке относительно 
                    //случаев, поэтому легче двигаться по индексам в обратном порядке, что и сделано
                    $lhm = $xmlLHM->PERS[$xmlHM->count() - 3 - $hm_i];
                    if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                        $res = $lhm->asXML();
                        file_put_contents("reestr/lhm101.xml", $res, FILE_APPEND);
                        $lhm_i++;
                    }
                    //}
                    //Исправление обычного вызова на неотложного согласно списка номеров карточек
                    $h = 0;
                    for ($h = 0; $h < $count; $h++) {
                        if ((integer) $hm->Z_SL->SL->NHISTORY === (integer) $arr[$h]) {
                            if ((string) $hm->Z_SL->SUMV === "662.02") {
                                $j++;
                            } else {
                                if ((string) $hm->Z_SL->SUMV === "1920.21") {
                                    $vr_j++;
                                };
                                if ((string) $hm->Z_SL->SUMV === "3725.53") {
                                    $feld_j++;
                                };
                                $hm->Z_SL->IDSP = "25";
                                $hm->Z_SL->SUMV = "662.02";
                                $hm->Z_SL->SL->SUM_M = "662.02";
                                $hm->Z_SL->SL->addChild('P_CEL', '1.1');
                                $hm->Z_SL->SL->COMENTSL->addChild('METHOD', '8.1');
                                $hm->Z_SL->SL->addChild('USL');
                                $hm->Z_SL->SL->USL->addChild('IDSERV', '1');
                                $hm->Z_SL->SL->USL->addChild('LPU', '560109');
                                $hm->Z_SL->SL->USL->addChild('PODR', '560109');
                                $hm->Z_SL->SL->USL->addChild('PROFIL', '84');
                                $hm->Z_SL->SL->USL->addChild('DET', $hm->Z_SL->SL->DET);
                                $hm->Z_SL->SL->USL->addChild('DATE_IN', $hm->Z_SL->SL->DATE_1);
                                $hm->Z_SL->SL->USL->addChild('DATE_OUT', $hm->Z_SL->SL->DATE_2);
                                $hm->Z_SL->SL->USL->addChild('DS', $hm->Z_SL->SL->DS1);
                                //$hm->Z_SL->SL->USL->addChild('CODE_USL', '-');
                                $hm->Z_SL->SL->USL->addChild('KOL_USL', '1');
                                $hm->Z_SL->SL->USL->addChild('TARIF', '662.02');
                                $hm->Z_SL->SL->USL->addChild('SUMV_USL', '662.02');
                                $hm->Z_SL->SL->USL->addChild('PRVS', $hm->Z_SL->SL->PRVS);
                                $hm->Z_SL->SL->USL->addChild('CODE_MD', $hm->Z_SL->SL->IDDOKT);
                                $j++;
                            }
                        }
                    }
                    //конец исправления обычных вызовов на НЕОТЛОЖНЫЙ!				
                    //Запись в временный файл
                    $sumv = $sumv + (float) $hm->Z_SL->SUMV;
                    $res = $hm->asXML();
                    file_put_contents("reestr/hm101.xml", $res, FILE_APPEND);
                } else {
                    //Вот тут внесение вызовов с диагнозом C97 в отдельный реестр
                    //последовательно увеличиваем номер случая в реестре основном
                    $hm->N_ZAP = $n_zapOnko;
                    $hm->Z_SL->IDCASE = $n_zapOnko;
                    //                +++ОБЫЧНЫЕ ВЫЗОВЫ+++					
                    if ((string) $hm->Z_SL->SUMV === "1920.21") {
                        $hm->Z_SL->SUMV = "1920.21";
                        $hm->Z_SL->SL->SUM_M = "1920.21";
                        $feldOnko++;
                    } //счетчик фельдшерских вызовов
                    if ((string) $hm->Z_SL->SUMV === "3725.53") {
                        $hm->Z_SL->SUMV = "3725.53";
                        $hm->Z_SL->SL->SUM_M = "3725.53";
                        $vrOnko++;
                    }
                    //генерируем SL_ID
                    $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);
                    $n_zapOnko++; //увеличим номер в реестре
                    $lhm = $xmlLHM->PERS[$xmlHM->count() - 3 - $hm_i];
                    if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                        $res = $lhm->asXML();
                        file_put_contents("reestr/chm101.xml", $res, FILE_APPEND);
                        $lhm_i++;
                    }

                    //Запись нужных полей по регламенту
                    $hm->Z_SL->SL->addChild('DS_ONK', 0);
                    $hm->Z_SL->SL->addChild('CONS');
                    $hm->Z_SL->SL->CONS->addChild('PR_CONS', 0);

                    $hm->Z_SL->SL->addChild('TARIF', $hm->Z_SL->SL->SUM_M);
                    //Запись в временный файл
                    $sumvOnko = $sumvOnko + (float) $hm->Z_SL->SUMV;
                    $res = $hm->asXML();
                    file_put_contents("reestr/cm101.xml", $res, FILE_APPEND);
                    //Окончание онкореестра
                    $hm_iOnko++;
                }
                if (!in_array($hm->Z_SL->SL->IDDOKT, $medicsArray)) {
                    $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
                }
            } else {
                //бригада выехала 1 числа следующего месяца, этот вызов подавать в следующем месяце
                $not_add++;
                //добавить версию справочника специальностей и продублировать дату в случай                
                $hm->Z_SL->DATE_Z_1 = $hm->Z_SL->SL->DATE_1;
                $hm->Z_SL->DATE_Z_2 = $hm->Z_SL->SL->DATE_2;
                //                     +++НЕОТЛОЖКА+++
                //для неотложки добавляем узел P_CEL со значением 2
                //
		if ((string) $hm->Z_SL->SUMV === "662.02") {
                    //Временное решение
                    //Поправить поля по регламенту на 2019 год
                    unset($hm->Z_SL->SL->USL->CODE_USL);
                    $hm->Z_SL->IDSP = 25;
                    //Конецвременного решения
                    $hm->Z_SL->SUMV = "662.02";
                    $hm->Z_SL->SL->SUM_M = "662.02";
                    $hm->Z_SL->SL->addChild('P_CEL', '1.1');
                }
                //                +++ОБЫЧНЫЕ ВЫЗОВЫ+++					
                if ((string) $hm->Z_SL->SUMV === "1920.21") {
                    $hm->Z_SL->SUMV = "1920.21";
                    $hm->Z_SL->SL->SUM_M = "1920.21";
                }
                if ((string) $hm->Z_SL->SUMV === "3725.53") {
                    $hm->Z_SL->SUMV = "3725.53";
                    $hm->Z_SL->SL->SUM_M = "3725.53";
                }
                //генерируем SL_ID
                $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);
                //Проверка, что диагноз не входит в онкореестр
                if ($hm->Z_SL->SL->DS1 != "C97") {
                    $res = $hm->asXML();
                    file_put_contents("reestr/hm101_add.xml", $res, FILE_APPEND);
                    $lhm = $xmlLHM->PERS[$xmlHM->count() - 3 - $hm_i];
                    if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                        $not_add_lhm++;
                        $res = $lhm->asXML();
                        file_put_contents("reestr/lhm101_add.xml", $res, FILE_APPEND);
                    }
                } else {
                    $res = $hm->asXML();
                    file_put_contents("reestr/cm101_add.xml", $res, FILE_APPEND);
                    $lhm = $xmlLHM->PERS[$xmlHM->count() - 3 - $hm_i];
                    if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                        $not_add_lhm++;
                        $res = $lhm->asXML();
                        file_put_contents("reestr/lcm101_add.xml", $res, FILE_APPEND);
                    }
                }
            }
            $hm_i++;
            if (!in_array($hm->Z_SL->SL->IDDOKT, $medicsArray)) {
                $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
            }
        }


        //Добавление случаев с прошлого месяца
        //Основные случаи
        foreach ($xmlHM_add->ZAP as $hm) {
            $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
            $hm->N_ZAP = $n_zap;
            $hm->Z_SL->IDCASE = $n_zap;
            $n_zap++;
            $hm_i++;
            //генерируем SL_ID
            $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);
            if ((string) $hm->Z_SL->SUMV === "662.02") {
                //Временное решение
                //Поправить поля по регламенту на 2019 год
                unset($hm->Z_SL->SL->USL->CODE_USL);
                $hm->Z_SL->IDSP = 25;
                $neotl++;
            }
            if ((string) $hm->Z_SL->SUMV === "3725.53") {
                $vr++;
            }
            if ((string) $hm->Z_SL->SUMV === "1920.21") {
                $feld++;
            }
            $res = $hm->asXML();
            $sumv = $sumv + (float) $hm->Z_SL->SUMV;
            file_put_contents("reestr/hm101.xml", $res, FILE_APPEND);
            foreach ($xmlLHM_add->PERS as $lhm) {
                if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                    $lhm_i++;
                    $res = $lhm->asXML();
                    file_put_contents("reestr/lhm101.xml", $res, FILE_APPEND);
                }
            }
            if (!in_array($hm->Z_SL->SL->IDDOKT, $medicsArray)) {
                $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
            }
        }
        //Онкологические случаи
        foreach ($xmlCM_add->ZAP as $hm) {
            $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
            $hm->N_ZAP = $n_zapOnko;
            $hm->Z_SL->IDCASE = $n_zapOnko;
            $n_zapOnko++;
            $hm_iOnko++;
            //генерируем SL_ID
            $hm->Z_SL->SL->SL_ID = substr(hash_hmac("sha224", $hm->Z_SL->SL->asXML(), "www.orenssmp.ru"), 0, 32);
            //Смешно, но онкологический вызов не может быть неотложным))))
            /* if ((string) $hm->Z_SL->SUMV === "662.02") {
              $neotl++;
              } */
            if ((string) $hm->Z_SL->SUMV === "3725.53") {
                $vr++;
            }
            if ((string) $hm->Z_SL->SUMV === "1920.21") {
                $feld++;
            }
            $res = $hm->asXML();
            $sumvOnko = $sumvOnko + (float) $hm->Z_SL->SUMV;
            file_put_contents("reestr/cm101.xml", $res, FILE_APPEND);
            foreach ($xmlLCM_add->PERS as $lhm) {
                if ((string) $hm->PACIENT->ID_PAC === (string) $lhm->ID_PAC) {
                    $lhm_i++;
                    $res = $lhm->asXML();
                    file_put_contents("reestr/lcm101.xml", $res, FILE_APPEND);
                }
            }
            if (!in_array($hm->Z_SL->SL->IDDOKT, $medicsArray)) {
                $medicsArray[] = $hm->Z_SL->SL->IDDOKT;
            }
        }
        $end = date("h:i:s");
        //отчитываемся о проделанной работе
        $log = $log . "\r\n\r\nПроверено " . $hm_i . " случаев на сумму " . $sumv . " рублей\r\n";
        $log = $log . "на 1920.21 руб: " . $feld . "\r\nна 3725.53 rub: " . $vr . " на 662.02 rub: " . $neotl . "\r\n";
        $log = $log . "Сохранено " . ($n_zap - 1) . " HM-zap " . $lhm_i . " LHM-zap\r\n";
        $log = $log . "Удалено " . $not_add . " HM-zap " . $not_add_lhm . " LHM-zap\r\n";
        $log = $log . "Переделано в неотложку " . $j . " записей. Из врачебного вызова " . $vr_j . " записей. Из фельдшерского вызова " . $feld_j . " LHM-zap\r\n";
        $log = $log . "$temp  $temp1  $temp2\r\n\r\n";
        $log = $log . "Переделано неотложки в обычный вызов из-за прибытия после 19:00 : <b>$otkl</b>\r\n\r\n";
        $log = $log . "Время старта: <b>$start</b> \r\nВремя окончания: <b>$end</b>\r\n\r\n";
        $log = $log . "<hr><h4>Онкореестр:</h4>\r\n";
        $log = $log . "Проверено " . $hm_iOnko . " случаев на сумму " . $sumvOnko . " рублей\r\n";
        $log = $log . "на 1920.21 руб: " . $feldOnko . "\r\nна 3725.53 rub: " . $vrOnko . "\r\n";
        $log = $log . "Время старта: <b>$start</b> \r\nВремя окончания: <b>$end</b>\r\n\r\n";
        \Yii::info("$log", 'foms');
        return ['sumvHM' => $sumv, 'sumvCM' => $sumvOnko];
    }

    /**
     * Окончательное сохранение XML-файлов случаев (HM, CM) и персональных 
     * данных пациентов (LHM, LCM)
     * @param type $sumvHM Сумма за основные случаи
     * @param type $sumvCM Сумма за онкологические случаи
     */
    public static function saveXmlWithHead($sumvHM, $sumvCM) {
        //шапки и закрывающие теги реестров счетов
        //файл случаев HM
        $head_hm = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>";
        $head_hm = $head_hm . "<ZL_LIST>";
        $head_hm = $head_hm . "<ZGLV>";
        $head_hm = $head_hm . "<VERSION>3.1</VERSION>";
        $head_hm = $head_hm . "<DATA>" . date("Y-m-d") . "</DATA>";
        $head_hm = $head_hm . "<FILENAME>HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101</FILENAME>";
        $head_hm = $head_hm . "</ZGLV>";
        $head_hm = $head_hm . "<SCHET>";
        $head_hm = $head_hm . "<CODE>101</CODE>";
        $head_hm = $head_hm . "<CODE_MO>560109</CODE_MO>";
        $head_hm = $head_hm . "<YEAR>" . date("Y", time() - 20 * 24 * 3600) . "</YEAR>";
        $head_hm = $head_hm . "<MONTH>" . date("m", time() - 20 * 24 * 3600) . "</MONTH>";
        $head_hm = $head_hm . "<NSCHET>101</NSCHET>";
        $head_hm = $head_hm . "<DSCHET>" . date("Y-m-d") . "</DSCHET>";

        $head_hmOnko = $head_hm . "<SUMMAV>" . round($sumvCM, 2) . "</SUMMAV>";
        $head_hmOnko = $head_hmOnko . "</SCHET>";
        $head_hmOnko = str_replace("HM560109T56", "CM560109T56", $head_hmOnko);

        $head_hm = $head_hm . "<SUMMAV>" . round($sumvHM, 2) . "</SUMMAV>";
        $head_hm = $head_hm . "</SCHET>";
        $end_hm = "</ZL_LIST>";

        //файл персональных данных пациентов LHM
        $head_lhm_osn = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>";
        $head_lhm_add = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $head_lhm = "<PERS_LIST>";
        $head_lhm = $head_lhm . "<ZGLV>";
        $head_lhm = $head_lhm . "<VERSION>3.1</VERSION>";
        $head_lhm = $head_lhm . "<DATA>" . date("Y-m-d") . "</DATA>";
        $head_lhm = $head_lhm . "<FILENAME>LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101</FILENAME>";
        $head_lhm = $head_lhm . "<FILENAME1>HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101</FILENAME1>";
        $head_lhm = $head_lhm . "</ZGLV>";
        $head_lhmOnko = $head_lhm_osn . str_replace("HM560109T56", "CM560109T56", $head_lhm);
        $end_lhm = "</PERS_LIST>";

        $head_lhm_osn = $head_lhm_osn . $head_lhm;
        $head_lhm_add = $head_lhm_add . $head_lhm;

        //открытие временного файла, конкатенация, перекодирование и сохранение.
        //Основной счет
        $hm_res = $head_hm . file_get_contents("reestr/hm101.xml") . $end_hm;
        file_put_contents("reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", iconv("utf-8", "windows-1251", $hm_res));
        $xml = simplexml_load_file("reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($xml->asXML());
        $domxml->save("reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        unlink("reestr/hm101.xml");
        //Персональные данные пациентов основного счета
        $lhm_res = $head_lhm_osn . file_get_contents("reestr/lhm101.xml") . $end_lhm;
        file_put_contents("reestr/LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", iconv("utf-8", "windows-1251", $lhm_res));
        $xml = simplexml_load_file("reestr/LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($xml->asXML());
        $domxml->save("reestr/LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        unlink("reestr/lhm101.xml");

        //Онкологический счет
        $hm_res = $head_hmOnko . @file_get_contents("reestr/cm101.xml") . $end_hm;
        file_put_contents("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", iconv("utf-8", "windows-1251", $hm_res));
        $xml = simplexml_load_file("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($xml->asXML());
        $domxml->save("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        @unlink("reestr/cm101.xml");
        //Персональные данные пациентов онкологического счета
        $lhm_res = $head_lhmOnko . @file_get_contents("reestr/chm101.xml") . $end_lhm;
        file_put_contents("reestr/LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", iconv("utf-8", "windows-1251", $lhm_res));
        $xml = simplexml_load_file("reestr/LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($xml->asXML());
        $domxml->save("reestr/LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        @unlink("reestr/chm101.xml");

//Файлы на перенос вызовов на следующий месяц
        $hm_res = $head_hm . @file_get_contents("reestr/hm101_add.xml") . $end_hm;
        file_put_contents("reestr/HM560109T56_" . date("ym") . "101_add.xml", $hm_res);
        @unlink("reestr/hm101_add.xml");
        $lhm_res = $head_lhm_add . @file_get_contents("reestr/lhm101_add.xml") . $end_lhm;
        file_put_contents("reestr/LHM560109T56_" . date("ym") . "101_add.xml", $lhm_res);
        @unlink("reestr/lhm101_add.xml");
        $hm_res = $head_hm . @file_get_contents("reestr/cm101_add.xml") . $end_hm;
        file_put_contents("reestr/CM560109T56_" . date("ym") . "101_add.xml", $hm_res);
        @unlink("reestr/cm101_add.xml");
        $lhm_res = $head_lhm_add . @file_get_contents("reestr/lcm101_add.xml") . $end_lhm;
        file_put_contents("reestr/LCM560109T56_" . date("ym") . "101_add.xml", $lhm_res);
        @unlink("reestr/lcm101_add.xml");
    }

    /**
     * Генерация файла с докторами
     * @param type $meddicsToAdd
     */
    public static function generateXmlMedics($meddicsToAdd = []) {
        //Формирование третьего файла (доктора)
        //Раньше использовалось ПО на Delphi, но увы оно не осилило изменения WSDL-файла от 1С
        //Так даже лучше, число точек отказа скратилось с 3 до 2-х
        //Объяснять тут нечего, все предельно просто
        $medics = Sync1cEgisAdis::find()->asArray()->all();
        $vhm = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>";
        $vhm = $vhm . "<DOCT_LIST>";
        $vhm = $vhm . "<ZGLV>";
        $vhm = $vhm . "<VERSION>3.0</VERSION>";
        $vhm = $vhm . "<DATA>" . date("Y-m-d") . "</DATA>";
        $vhm = $vhm . "<FILENAME>VHM560109T56_" . date("y", time() - 20 * 24 * 3600) . "101</FILENAME>";
        $vhm = $vhm . "<FILENAME1>HM560109T56_" . date("y", time() - 20 * 24 * 3600) . "101</FILENAME1>";
        $vhm = $vhm . "</ZGLV>";
        $x = 0;
        $addedArray = array();
        foreach ($medics as $medic) {
            if (mb_strlen($medic['snils']) < 14) {
                continue;
            }
            $medikId = $medic['codeadis'];
            if (!in_array($medikId, $addedArray)) {
                array_push($addedArray, $medikId);
                $vhm = $vhm . "<PERS>";
                $vhm = $vhm . "<CODE_MD>" . $medikId . "</CODE_MD>";
                $vhm = $vhm . "<FAM>" . mb_strtoupper($medic['surname']) . "</FAM>";
                $vhm = $vhm . "<IM>" . mb_strtoupper($medic['name']) . "</IM>";
                $vhm = $vhm . "<OT>" . mb_strtoupper($medic['patrname']) . "</OT>";
                $vhm = $vhm . "<DR>" . $medic['birthday'] . "</DR>";
                $vhm = $vhm . "<SNILS>" . $medic['snils'] . "</SNILS>";
                $vhm = $vhm . "</PERS>";
            }
        }
        $vhm = $vhm . "</DOCT_LIST>";
        file_put_contents("reestr/VHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", iconv("utf-8", "windows-1251", $vhm));
        $xml = simplexml_load_file("reestr/VHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($xml->asXML());
        $domxml->save("reestr/VHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $vhm = str_replace("HM560109T56", "CM560109T56", $vhm);
        file_put_contents("reestr/VCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", iconv("utf-8", "windows-1251", $vhm));
        $xml = simplexml_load_file("reestr/VCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($xml->asXML());
        $domxml->save("reestr/VCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
    }

    /**
     * Генерация ZIP-архива с файлами реестра
     */
    public static function generateZip() {
        //создаем Zip-архив с файлами реестра HM
        //удалить старый архив
        @unlink("reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.zip");
        $zip = new \ZipArchive();
        $zip_name = "reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.zip";

        if ($zip->open($zip_name, \ZIPARCHIVE::CREATE) !== TRUE) {
            $error .= "* Sorry ZIP creation failed at this time";
        }
        $zip->addFile("reestr/HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", "HM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $zip->addFile("reestr/LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", "LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $zip->addFile("reestr/VHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", "VHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $zip->close();


        //создаем Zip-архив с файлами реестра CM
        $zip = new \ZipArchive();
        //удалить старый архив
        @unlink("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.zip");
        $zip_name = "reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.zip";

        if ($zip->open($zip_name, \ZIPARCHIVE::CREATE) !== TRUE) {
            $error .= "* Sorry ZIP creation failed at this time";
        }
        $zip->addFile("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", "CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $zip->addFile("reestr/LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", "LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $zip->addFile("reestr/VCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml", "VCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        $zip->close();
    }

    /**
     * Удаление сгененрированных XML файлов, они все равно хранятся в Zip архиве
     */
    public static function removeWorkFiles() {
        @unlink("reestr/101hm.xml");
        @unlink("reestr/101lm.xml");
        @unlink("reestrHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        //unlink("output/HM560109T56_" . date("Ym") . "101_add.xml");
        //unlink("output/LHM560109T56_" . date("Ym") . "101_add.xml");
        @unlink("reestr/LHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        @unlink("reestr/VHM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        @unlink("reestr/CM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        @unlink("reestr/LCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
        @unlink("reestr/VCM560109T56_" . date("y", time() - 20 * 24 * 3600) . date("m", time() - 20 * 24 * 3600) . "101.xml");
    }

    /**
     * Генерация файла для статиста и стола справок
     * @return type
     */
    public function actionExpertise() {
        $model = new ExpertParser();
        if (Yii::$app->request->isPost) {
            $model->xlsFile = UploadedFile::getInstance($model, 'xlsFile');
            $model->load(Yii::$app->request->post());
            if ($model->upload()) {
                $model->load(Yii::$app->request->post());
                if ($model->parseNumbersOfCalls($model->start, $model->end)) {
                    return Yii::$app->response->sendFile(Yii::getAlias('uploads/Готово.xls'));
                } else {
                    throw new \yii\web\HttpException(500, 'При генерации файла для экспертизы '
                    . 'возникла непредвиденная ошибка, обратитесь '
                    . 'к системному администратору.');
                }
            }
        }
        return $this->render('expertise', ['model' => $model]);
    }

}
