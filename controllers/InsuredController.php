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
use app\models\Dbf;

/**
 * Description of InsuredController
 *
 * @author msv14
 */
class InsuredController extends AppController {

    /**
     * Генерация файла на идентификацию незастрахованных
     * @return type
     */
    public function actionNotInsured($x = "") {
        $model = new InsuredXls();
        $x = "";
        $emptyFIO = 0;
        $bukvFIO = 0;
        $kinds = 0;
        $noCorrectBirth = 0;
        $hasNumberOMS = 0;
        $aliens = 0;
        $military = 0;
        $repeated = 0;
        $byTypeDoc = 0;
        $typo = 0;
        $noCorrectGenger = 0;
        if (Yii::$app->request->isPost) {
            $model->xlsFile = UploadedFile::getInstance($model, 'xlsFile');
            if ($model->upload()) {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
                $spreadsheet = $reader->load("uploads/insured.xls");
                $i = 10;
                $j = 10;
                $a = 0;
                $writeXls = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
                $writeSpreadsheet = $writeXls->load("templates/res-insured.xls");


                $writeSpreadsheet->setActiveSheetIndex(0);
                $spreadsheet->setActiveSheetIndex(0);


                $writeXlsAdd = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
                $writeQuery = $writeXlsAdd->load("templates/res-query.xls");
                $writeQuery->setActiveSheetIndex(0);

                $calls = array();
                $fams = [];
                $persDN = [];
                while (true) {
                    if ($spreadsheet->getActiveSheet()->getCell("B" . $i)->getValue() == "* Проведите анализ данных случаев, выясните верные персональные данные, документы УДЛ , действующие полисы ОМС и предоставьте данные случаи в доп. счетах с верной информацией") {
                        break;
                    }
                    $ngod = $spreadsheet->getActiveSheet()->getCell("A" . $i)->getValue();
                    $fam = str_replace("Ё", "Е", $spreadsheet->getActiveSheet()->getCell("C" . $i)->getValue());
                    $imya = $spreadsheet->getActiveSheet()->getCell("D" . $i)->getValue();
                    $otch = $spreadsheet->getActiveSheet()->getCell("E" . $i)->getValue();
                    $dr = $spreadsheet->getActiveSheet()->getCell("F" . $i)->getValue();
                    $dprm = $spreadsheet->getActiveSheet()->getCell("G" . $i)->getValue();
                    $npolis = $spreadsheet->getActiveSheet()->getCell("K" . $i)->getValue();
                    $snils = $spreadsheet->getActiveSheet()->getCell("L" . $i)->getValue();
                    $typedoc = $spreadsheet->getActiveSheet()->getCell("M" . $i)->getValue();
                    $serdoc = $spreadsheet->getActiveSheet()->getCell("N" . $i)->getValue();
                    $nomdoc = $spreadsheet->getActiveSheet()->getCell("O" . $i)->getValue();
                    $pol = $spreadsheet->getActiveSheet()->getCell("Q" . $i)->getValue();
                    $city = $spreadsheet->getActiveSheet()->getCell("R" . $i)->getValue();
                    $ulic = $spreadsheet->getActiveSheet()->getCell("S" . $i)->getValue();
                    $dom = $spreadsheet->getActiveSheet()->getCell("T" . $i)->getValue();
                    $kvar = $spreadsheet->getActiveSheet()->getCell("U" . $i)->getValue();
                    $ds = $spreadsheet->getActiveSheet()->getCell("V" . $i)->getValue();
                    $rezl = $spreadsheet->getActiveSheet()->getCell("W" . $i)->getValue();
                    $prty = $spreadsheet->getActiveSheet()->getCell("X" . $i)->getValue();
                    $kod2 = $spreadsheet->getActiveSheet()->getCell("Y" . $i)->getValue();
                    $inf3 = $spreadsheet->getActiveSheet()->getCell("Z" . $i)->getValue();
                    $i++;
//Пополнить массив
//....
//ОТСЕИВАНИЕ                    
//1. ФИО не пустые
                    if ($fam == "" or $imya == "" or $otch == "") {
                        $emptyFIO++;
                        continue;
                    }
//2. Вместо ФИО инициалы
                    if (mb_strlen($fam) == 1 or mb_strlen($imya) == 1 or mb_strlen($otch) == 1) {
                        $bukvFIO++;
                        continue;
                    }
//3. ДР стоит 01.01.1900 или 01.01.2010
                    if ($dr == "01.01.1900" or $dr == "01.01.2010") {
                        $noCorrectBirth++;
                        continue;
                    }
//4. № полиса заполнен
                    if ($npolis != "") {
                        $hasNumberOMS++;
                        continue;
                    }
//5. Иностранцы!
                    if (strtok($kod2, " ") == "ИНОСТРАННЫЙ") {
                        $aliens++;
                        continue;
                    }
//6. Военнослужащие/ФСБ/УИС/Пожарные/МВД
                    $kod2Tok = strtok($inf3, " ");
                    if ($kod2Tok == 8 or $kod2Tok == 13 or $kod2Tok == 14 or
                            $kod2Tok == 15 or $kod2Tok == 16 or $kod2Tok == 17
                            or $kod2Tok == 9) {
                        $military++;
                        continue;
                    }
//7. Дети рожденные в том же месяце/прошлом/позапрошлом месяце
                    $secondsDprm = strtotime($dprm);
                    $secondsDr = strtotime($dr);
                    if ($secondsDprm - $secondsDr <= 3 * 60 * 60 * 24 * 30) {
                        $kinds++;
                        continue;
                    }
//8. Один раз должна оказываться помощь 

                    if (!in_array($fam, $fams)) {
                        array_push($fams, $fam);
                        $persDN += [$fam => [$fam, $imya, $otch, $dr]];
                    }
//8.1 Совпадает ФИО и ДР
                    elseif ($persDN[$fam][1] == $imya and
                            $persDN[$fam][2] == $otch and
                            $persDN[$fam][3] == $dr) {
                        $repeated++;
                        continue;
                    }
//8.2 Совпадает фамилия и ДР
                    elseif ($persDN[$fam][3] == $dr) {
                        $repeated++;
                        continue;
                    }
//8.3 Совпадают ФИО
                    elseif ($persDN[$fam][1] == $imya and
                            $persDN[$fam][2] == $otch) {
                        $repeated++;
                        continue;
                    }
//8.4 Совпадает ФИ
                    elseif ($persDN[$fam][1] == $imya) {
                        $repeated++;
                        continue;
                    } else {
                        $repeated++;
                        continue;
                    }
//8.5 Тип документа 9 - паспорт иностранного гражданина
//                  11 - вид на жительство
                    if ($typedoc == 9
                            or $typedoc == 11
                            or $typedoc == 18
                    /* or $typedoc == 16
                      or $typedoc == 12
                      or $typedoc == 13
                      or $typedoc == 7
                      or $typedoc == 6
                      or $typedoc == 4 */) {
                        $byTypeDoc++;
                        continue;
                    }
//8.6 В Ф опечатка
                    $compare = false;
                    foreach ($fams as $f) {
                        $compares = 0;
                        if (mb_strlen($f) == mb_strlen($fam)) {
                            $pos = "";
                            $length = mb_strlen($fam);
                            for ($z = 0; $z < $length; $z++) {
                                if (mb_substr($f, $z, 1) == mb_substr($fam, $z, 1)) {
                                    $compares++;
                                    $pos = $pos . mb_substr($f, $z, 1) . " = " . mb_substr($fam, $z, 1) . "<br>";
                                } else
                                    $pos = $pos . mb_substr($f, $z, 1) . " != " . mb_substr($fam, $z, 1) . "<br>";
                            }
                            if ($length - $compares == 1) {
//$x = $x . $fam . " почти совпадает с " . $f . "<br>";
                                $compare = true;
                            }
                        }
                    }

                    if ($compare) {
                        $typo++;
                        continue;
                    }
//8.7
                    if (in_array($fam, ["НЕИЗВЕСТНЫЙ", "НЕИЗВЕСТНАЯ", "НЕ НАЗВАЛ", "НЕ НАЗВАЛА"])) {
                        $x = $x . "$fam отброшен!<br>";
                        continue;
                    }
//8.8 Грубейшие опечатки
                    if ($pol == "М") {
                        $pol = 1;
                    } else {
                        $pol = 2;
                    }
                    $checkPol = InsuredController::check_pol($fam, $imya, $otch, $pol, $j);
                    if (!$checkPol["correct"]) {
//$x = $x.$checkPol["descr"];
                        $noCorrectGenger++;
                        continue;
                    }

//9. Попробовать найти информацию в ЕГИЗ
//РАСКОММЕНТИРОВАТЬ!!!!

                    $egizReq = new searchIndividual();
                    $egizReq->surname = $fam;
                    $egizReq->name = $imya;
                    $egizReq->patrName = $otch;
                    $egizReq->birthDate = $dr;
                    try {
                        $docsEgiz = InsuredController::returnDocs($egizReq);
                    } catch (\Exception $ex) {
                        //unset($docsEgiz);
                        $x = $x . "<p>При поиске в ЕГИСЗ пациента № " . ($i - 9) . " возникла подлая ошибка ;-( </p>";
                    }
                    //Если найдено в ЕГИЗ, заполняем паспортные данные из ЕГИЗ
                    if (@gettype($docsEgiz) != "NULL") {
                        //9.1 Самое простое СНИЛС
                        if (count($docsEgiz["snils"]) != 0) {
                            $writeSpreadsheet->getActiveSheet()->
                                    setCellValueExplicit("L" . $j, InsuredController::convertSNILS($docsEgiz["snils"]["nomdoc"]), 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("I" . ($j - 8), InsuredController::convertSNILS($docsEgiz["snils"]["nomdoc"]), 's');
                        } else {
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("L" . $j, $snils, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("I" . ($j - 8), $snils, 's');
                        }

                        //9.2 Далее полис
                        //8.7 Если в ЕГИЗ найден полис, отбрасываем человека
                        if (count($docsEgiz["oms"]) != 0) {
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("K" . $j, $docsEgiz["oms"]["nomdoc"], 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("J" . $j, $docsEgiz["oms"]["seria"], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("M" . ($j - 8), $docsEgiz["oms"]["nomdoc"], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("L" . ($j - 8), $docsEgiz["oms"]["seria"], 's');
                            $x = $x . "Найден полис ОМС в ЕГИСЗ $j<br>";
                            $hasNumberOMS++;
                            continue;
                        }

                        //9.3 Паспорт или свидетельство о рождении
                        //Если есть паспорт
                        if (count($docsEgiz["passport"]) != 0) {
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("M" . $j, "14", 's');
                            $passport = InsuredController::convertPassport($docsEgiz["passport"]["seria"], $docsEgiz["passport"]["nomdoc"]);
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("N" . $j, $passport[1], 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("O" . $j, $passport[0], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("F" . ($j - 8), "14", 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("G" . ($j - 8), $passport[1], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("H" . ($j - 8), $passport[0], 's');
                        } elseif (count($docsEgiz["birthcert"]) != 0) {
                            if (gettype($docsEgiz["birthcert"]["seria"]) == "array")
                                $docsEgiz["birthcert"]["seria"] = "";
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("M" . $j, "3", 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("O" . $j, $docsEgiz["birthcert"]["seria"], 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("N" . $j, $docsEgiz["birthcert"]["nomdoc"], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("F" . ($j - 8), "3", 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("G" . ($j - 8), $docsEgiz["birthcert"]["seria"], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("H" . ($j - 8), $docsEgiz["birthcert"]["nomdoc"], 's');
                        } else {
                            if ($typedoc == 14) {
                                $passport = InsuredController::convertPassport($serdoc, $nomdoc);
                                $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("M" . $j, $typedoc, 's');
                                $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("O" . $j, $passport[0], 's');
                                $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("N" . $j, $passport[1], 's');
                                $writeQuery->getActiveSheet()->setCellValueExplicit("F" . ($j - 8), "14", 's');
                                $writeQuery->getActiveSheet()->setCellValueExplicit("G" . ($j - 8), $passport[1], 's');
                                $writeQuery->getActiveSheet()->setCellValueExplicit("H" . ($j - 8), $passport[0], 's');
                            } else {
                                $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("M" . $j, $typedoc, 's');
                                $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("N" . $j, $serdoc, 's');
                                $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("O" . $j, $nomdoc, 's');
                                if ($typedoc <> 0) {
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("F" . ($j - 8), $typedoc, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("G" . ($j - 8), $serdoc, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("H" . ($j - 8), $nomdoc, 's');
                                }
                            }
                        }

                        if ($docsEgiz["birthday"] != "") {
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("F" . $j, $docsEgiz["birthday"], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("E" . ($j - 8), $docsEgiz["birthday"], 's');
                        } else {
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("F" . $j, $dr, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("E" . ($j - 8), $dr, 's');
                        }
                        $a++;
                    } else {
                        $writeSpreadsheet->getActiveSheet()->setCellValue("F" . $j, $dr);
                        $writeQuery->getActiveSheet()->setCellValue("E" . ($j - 8), $dr);
                        $writeSpreadsheet->getActiveSheet()->setCellValue("K" . $j, $npolis);
                        $writeSpreadsheet->getActiveSheet()->setCellValue("L" . $j, $snils);
                        if ($typedoc == 14) {
                            $passport = InsuredController::convertPassport($serdoc, $nomdoc);
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("M" . $j, $typedoc, 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("O" . $j, $passport[0], 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("N" . $j, $passport[1], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("F" . ($j - 8), "14", 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("G" . ($j - 8), $passport[1], 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("H" . ($j - 8), $passport[0], 's');
                        } else {
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("M" . $j, $typedoc, 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("N" . $j, $serdoc, 's');
                            $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("O" . $j, $nomdoc, 's');
                            if ($typedoc <> 0) {
                                $writeQuery->getActiveSheet()->setCellValueExplicit("F" . ($j - 8), $typedoc, 's');
                                $writeQuery->getActiveSheet()->setCellValueExplicit("G" . ($j - 8), $serdoc, 's');
                                $writeQuery->getActiveSheet()->setCellValueExplicit("H" . ($j - 8), $nomdoc, 's');
                            }
                        }
                    }



// 10 Окончание формирования файлов 
                    $writeSpreadsheet->getActiveSheet()->setCellValue("A" . $j, $ngod);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("C" . $j, $fam);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("D" . $j, $imya);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("E" . $j, $otch);
                    $writeQuery->getActiveSheet()->setCellValue("A" . ($j - 8), $fam);
                    $writeQuery->getActiveSheet()->setCellValue("B" . ($j - 8), $imya);
                    $writeQuery->getActiveSheet()->setCellValue("C" . ($j - 8), $otch);
                    $writeQuery->getActiveSheet()->setCellValue("D" . ($j - 8), $pol);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("G" . $j, $dprm);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("Q" . $j, $pol);
                    $writeSpreadsheet->getActiveSheet()->setCellValueExplicit("R" . $j, $city, 's');
                    $writeSpreadsheet->getActiveSheet()->setCellValue("S" . $j, $ulic);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("T" . $j, $dom);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("U" . $j, $kvar);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("V" . $j, $ds);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("W" . $j, $rezl);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("X" . $j, $prty);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("Y" . $j, $kod2);
                    $writeSpreadsheet->getActiveSheet()->setCellValue("Z" . $j, $inf3);
                    $j++;
//if ($j==125) break;
                }
                $writeQuery->getActiveSheet()->setCellValue("A" . ($j - 8), "end");
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeSpreadsheet);
                $writer->save("uploads/остаток.xls");
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeQuery);
                $writer->save("uploads/запрос на идентификацию.xls");
                sleep(15);
            }
            $check = $noCorrectBirth + $emptyFIO + $bukvFIO + $kinds + $military + $aliens +
                    $hasNumberOMS + $repeated + $typo + $byTypeDoc + $noCorrectGenger;
            $x = "$x<br>"
                    . "Проверенно $i потенциальных незастрахованных<br>"
                    . "На идентификацию будет отправлено " . ($j - 8) . " человек<br>"
                    . "<b>Отброшено:</b><br>"
                    . "$noCorrectBirth - некорректная дата рождения<br>"
                    . "$emptyFIO - пустые инициалы ФИО<br>" .
                    "$bukvFIO - ФИО записано инициалами<br>"
                    . "$kinds - дети, рожденные недавно<br>" .
                    "$military - военнослужащие<br>"
                    . "$aliens - возможные иностранцы<br>" .
                    "$hasNumberOMS - есть № полиса ОМС<br>"
                    . "$repeated - уже есть в списке на идентификаци<br>"
                    . "$typo - опечатка в фамилии<br>"
                    . "$byTypeDoc - тип документа вид на жительство или паспорт иностранца<br>" .
                    "$noCorrectGenger - в ФИО грубейшие опечатки (пол не определяется по инициалам корректно)<br>"
                    . "<br>Всего отброшенно: $check <br>"
                    . "<hr>По ЕГИЗу найдено " . $a . " человек<br>";
            if (InsuredController::splitInsuredQueryAndZip()) {
                $x = "<p>Файлы для идентификации готовы, Вы можете скачать их по этой "
                        . "<a href='uploads/Пакет на идентификацию.zip' "
                        . "target='_blank'>ссылке</a></p>" . $x;
            } else {
                $x = "<p>Файлы для идентификации не готовы, произошла какая-то "
                        . "подлая ошибка </p>" . $x;
            }
            return $this->render('not-insured', ['model' => $model, 'x' => $x]);
        } else {
            return $this->render('not-insured', ['model' => $model, 'x' => $x]);
        }
    }

    public function actionGenerateInsured($x = "") {
        $model = new Dbf();
        if (Yii::$app->request->isPost) {
            $model->xlsFile = UploadedFile::getInstance($model, 'xlsFile');
            $model->dbfFile = UploadedFile::getInstance($model, 'dbfFile');
            $model->load(Yii::$app->request->post());
            if ($model->upload()) {
                $model->load(Yii::$app->request->post());
//$x = $x."<p>" . var_dump($model)."</p>";
                /* try { */
//Открываем файл остаток
                $readXls = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
                $readQuery = $readXls->load("uploads/notinsured.xls");
                $readQuery->setActiveSheetIndex(0);
//Открываем шаблон реестра
                $writeXlsAdd = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
                $writeQuery = $writeXlsAdd->load("templates/res-reestr.xls");
                $writeQuery->setActiveSheetIndex(0);
                $lineRead = 10;
                $lineWrite = 16;
                $dbf = \dbase_open("uploads/answer.dbf", 2);
                $count = 0;
//обходим ответ ТФОМС                
                if ($dbf) {
                    for ($i = 1; $i <= \dbase_numrecords($dbf); $i++) {
                        $rec = \dbase_get_record_with_names($dbf, $i);
                        if (iconv("cp866", "utf-8", substr($rec["COMMENT"], 0, 6)) != "Найден" && substr($rec["COMMENT"], 0, 5) != " dbeg"
                                //&& iconv("cp866", "utf-8", substr($rec["ST"], 0, 8)) == "Find New"
                                && iconv("cp866", "utf-8", substr($rec["COMMENT"], 0, 6)) != "необхо"
                        ) {
//считать нужные значения из остатка
                            $ngod = $readQuery->getActiveSheet()->getCell("A" . $lineRead)->getValue();
                            $fam = $readQuery->getActiveSheet()->getCell("C" . $lineRead)->getValue();
                            //Проверить, совпадает ли фамилия в ответе ТФОМС и файле "остаток",
                            //если не совпадает, значит в ТФОМС немного подправили, 
                            //необходимо уведимить об этом оператора
                            if ($fam != preg_replace('/\s+$/', '', iconv("cp866", "utf-8", $rec["OLDFNAME"]))) {
                                $x = $x . "<p><b>$fam</b> в файле остаток не "
                                        . "совпадает с <b>" . iconv("cp866", "utf-8", $rec["OLDFNAME"]) . "</b>  "
                                        . "в ответе ТФОМС, сверьте данные!</p>";
                            }
                            $imya = $readQuery->getActiveSheet()->getCell("D" . $lineRead)->getValue();
                            $otch = $readQuery->getActiveSheet()->getCell("E" . $lineRead)->getValue();
                            $fio = "$fam $imya $otch";
                            $dr = $readQuery->getActiveSheet()->getCell("F" . $lineRead)->getValue();
                            $dprm = $readQuery->getActiveSheet()->getCell("G" . $lineRead)->getValue();
                            $serdoc = $readQuery->getActiveSheet()->getCell("N" . $lineRead)->getValue();
                            $nomdoc = $readQuery->getActiveSheet()->getCell("O" . $lineRead)->getValue();
                            $pol = $readQuery->getActiveSheet()->getCell("Q" . $lineRead)->getValue();
                            if ($pol == 1) {
                                $pol = "М";
                            } else {
                                $pol = "Ж";
                            }
                            $city = $readQuery->getActiveSheet()->getCell("R" . $lineRead)->getValue();
                            if ($city != '') {
                                $city = str_replace(['=', '-', '*', '+'], '', $city[0]) . substr($city, 1);
                            }
                            $ulic = $readQuery->getActiveSheet()->getCell("S" . $lineRead)->getValue();
                            $dom = $readQuery->getActiveSheet()->getCell("T" . $lineRead)->getValue();
                            $kvar = $readQuery->getActiveSheet()->getCell("U" . $lineRead)->getValue();
                            $adres = "$city $ulic $dom $kvar";
                            $ds = $readQuery->getActiveSheet()->getCell("V" . $lineRead)->getValue();
                            $rezl = $readQuery->getActiveSheet()->getCell("W" . $lineRead)->getValue();
                            $prty = $readQuery->getActiveSheet()->getCell("X" . $lineRead)->getValue();
                            $count++;
//записать в реестр

                            $writeQuery->getActiveSheet()->setCellValueExplicit("A$lineWrite", $count, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("B$lineWrite", $fio, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("C$lineWrite", $pol, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("D$lineWrite", "$serdoc $nomdoc", 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("E$lineWrite", $dr, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("F$lineWrite", "$adres", 's');

                            $writeQuery->getActiveSheet()->setCellValueExplicit("G$lineWrite", $ds, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("H$lineWrite", $dprm, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("K$lineWrite", $rezl, 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("L$lineWrite", "1273,45", 's');
                            $writeQuery->getActiveSheet()->setCellValueExplicit("AA$lineWrite", $ngod, 's');
                            if ($prty < 5) {
                                $writeQuery->getActiveSheet()->setCellValueExplicit("I$lineWrite", "-", 's');
                                $writeQuery->getActiveSheet()->setCellValueExplicit("J$lineWrite", "+", 's');
                            } else {
                                $writeQuery->getActiveSheet()->setCellValueExplicit("I$lineWrite", "+", 's');
                                $writeQuery->getActiveSheet()->setCellValueExplicit("J$lineWrite", "-", 's');
                            }
                            $writeQuery->getActiveSheet()->
                                    duplicateStyle($writeQuery->getActiveSheet()->
                                            getStyle('A16'), "A$lineWrite:L$lineWrite");
                            $writeQuery->getActiveSheet()->getRowDimension($lineWrite)->setRowHeight(-1);
                            $writeQuery->getActiveSheet()->insertNewRowBefore($lineWrite + 1, 1);
                            $lineWrite++;
                        }
                        $lineRead++;
                    }
                    $x = $x . "<p>Без правки количества могло быть/есть <b>$count</b> готовых незастрахованных</p>";
//А теперь магия, добавление/удаление необходимого числа незастрахованных
                    if ($model->needInsuredCount != 0) {
//Идеальный случай имеющееся число совпало с нужным
                        if ($model->needInsuredCount == $count or \dbase_numrecords($dbf) < $model->needInsuredCount) {
                            $writeQuery->getActiveSheet()->removeRow($lineWrite);
                            $sumStr = "Итого: " . str_replace(".", ",", round(1273.45 * $count, 2)) . " рублей";
                            $writeQuery->getActiveSheet()->setCellValue("K$lineWrite", $sumStr);
                            $writeQuery->getActiveSheet()->setCellValue("AB$lineWrite", "end");
                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeQuery);
                            $writer->save("uploads/незастрахованные.xls");
                            if (\dbase_numrecords($dbf) < $model->needInsuredCount) {
                                $x = "<p>Реестр незастрахованных готов, Вы можете скачать их по этой "
                                        . "<a href='uploads/незастрахованные.xls' "
                                        . "target='_blank'>ссылке</a></p>"
                                        . "<p style='color: red;'>Желаемое число незастрахованных сделать не удалось! "
                                        . "Число поданных на идентификацию в ТФОМС меньше нужного числа незастрахованных!!!"
                                        . "</p>"
                                        . "<p>Увы этот скрипт не Брюс Всемогущий, так что не забудьте этот файлик подрихтовать по красоте)))</p>" . $x;
                            } else {
                                $x = "<p>Реестр незастрахованных готов, Вы можете скачать их по этой "
                                        . "<a href='uploads/незастрахованные.xls' "
                                        . "target='_blank'>ссылке</a></p>"
                                        . "<p>Увы этот скрипт не Брюс Всемогущий, так что не забудьте этот файлик подрихтовать по красоте)))</p>" . $x;
                            }
                            \dbase_close($dbf);
                        }
//Ненормальный случай, Нужных меньше Имеющихся

                        if ($model->needInsuredCount < $count) {

//заново обход dbf
                            $lineWrite = 16;
                            for ($i = 1; $i <= \dbase_numrecords($dbf); $i++) {
                                $rec = \dbase_get_record_with_names($dbf, $i);
                                if (iconv("cp866", "utf-8", substr($rec["COMMENT"], 0, 6)) != "Найден" && substr($rec["COMMENT"], 0, 5) != " dbeg"
                                        //&& iconv("cp866", "utf-8", substr($rec["ST"], 0, 8)) == "Find New"
                                        && iconv("cp866", "utf-8", substr($rec["COMMENT"], 0, 6)) != "необхо") {
                                    $writeQuery->getActiveSheet()->removeRow($lineWrite);
                                    $count--;
//Магическая подмена
                                    \dbase_delete_record($dbf, $i);
                                    //выход из цикла по достижении нужного числа незастрахлванных
                                    if ($model->needInsuredCount == $count) {
                                        break;
                                    }
                                }
                            }

                            $lineWrite = $count + 16;
                            //Перенумеровать стрки в эксель файле
                            for ($i = 1; $i <= $count; $i++) {
                                $writeQuery->getActiveSheet()->setCellValueExplicit("A" . ($i + 15), $i, 's');
                            }
                            $writeQuery->getActiveSheet()->removeRow($lineWrite);
                            $sumStr = "Итого: " . str_replace(".", ",", round(1273.45 * $count, 2)) . " рублей";
                            $writeQuery->getActiveSheet()->setCellValue("K$lineWrite", $sumStr);
                            \dbase_pack($dbf);
                            \dbase_close($dbf);
                            $writeQuery->getActiveSheet()->setCellValue("AB$lineWrite", "end");
                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeQuery);
                            $writer->save("uploads/незастрахованные.xls");
                            $x = "<p>Реестр незастрахованных готов, Вы можете скачать их по этой "
                                    . "<a href='uploads/незастрахованные.xls' "
                                    . "target='_blank'>ссылке</a></p>"
                                    . "<p>Скорректированный ответ ТФОМС скачайте по этой "
                                    . "<a href='uploads/answer.dbf' "
                                    . "target='_blank'"
                                    . ">ссылке</a></p>"
                                    . "<p>Сделано $count незастрахованных</p>"
                                    . "<p>Увы этот скрипт не Брюс Всемогущий, "
                                    . "так что не забудьте этот файлик "
                                    . "подрихтовать по красоте)))</p>" . $x;
                        }
//Ненормальный случай, Нужных больше Имеющихся
                        if ($model->needInsuredCount > $count) {
                            $lineRead = 10;
//заново обход dbf                            
                            for ($i = 1; $i <= \dbase_numrecords($dbf); $i++) {

                                $rec = \dbase_get_record_with_names($dbf, $i);
                                if (!(iconv("cp866", "utf-8", substr($rec["COMMENT"], 0, 6)) != "Найден" && substr($rec["COMMENT"], 0, 5) != " dbeg"
                                        //&& iconv("cp866", "utf-8", substr($rec["ST"], 0, 8)) == "Find New"
                                        && iconv("cp866", "utf-8", substr($rec["COMMENT"], 0, 6)) != "необхо" )) {
//считать нужные значения из остатка
                                    $count++;
                                    $ngod = $readQuery->getActiveSheet()->getCell("A" . $lineRead)->getValue();
                                    $fam = $readQuery->getActiveSheet()->getCell("C" . $lineRead)->getValue();
                                    $imya = $readQuery->getActiveSheet()->getCell("D" . $lineRead)->getValue();
                                    $otch = $readQuery->getActiveSheet()->getCell("E" . $lineRead)->getValue();
                                    $fio = "$fam $imya $otch";
                                    $dr = $readQuery->getActiveSheet()->getCell("F" . $lineRead)->getValue();
                                    $dprm = $readQuery->getActiveSheet()->getCell("G" . $lineRead)->getValue();
                                    $serdoc = $readQuery->getActiveSheet()->getCell("N" . $lineRead)->getValue();
                                    $nomdoc = $readQuery->getActiveSheet()->getCell("O" . $lineRead)->getValue();
                                    $pol = $readQuery->getActiveSheet()->getCell("Q" . $lineRead)->getValue();
                                    if ($pol == 1) {
                                        $pol = "М";
                                    } else {
                                        $pol = "Ж";
                                    }
                                    $city = $readQuery->getActiveSheet()->getCell("R" . $lineRead)->getValue();
                                    if ($city != '') {
                                        $city = str_replace(['=', '-', '*', '+'], '', $city[0]) . substr($city, 1);
                                    }
                                    $ulic = $readQuery->getActiveSheet()->getCell("S" . $lineRead)->getValue();
                                    $dom = $readQuery->getActiveSheet()->getCell("T" . $lineRead)->getValue();
                                    $kvar = $readQuery->getActiveSheet()->getCell("U" . $lineRead)->getValue();
                                    $adres = "$city $ulic $dom $kvar";
                                    $ds = $readQuery->getActiveSheet()->getCell("V" . $lineRead)->getValue();
                                    $rezl = $readQuery->getActiveSheet()->getCell("W" . $lineRead)->getValue();
                                    $prty = $readQuery->getActiveSheet()->getCell("X" . $lineRead)->getValue();
//записать в реестр

                                    $writeQuery->getActiveSheet()->setCellValueExplicit("A$lineWrite", $count, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("B$lineWrite", $fio, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("C$lineWrite", $pol, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("D$lineWrite", "$serdoc $nomdoc", 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("E$lineWrite", $dr, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("F$lineWrite", "$adres", 's');

                                    $writeQuery->getActiveSheet()->setCellValueExplicit("G$lineWrite", $ds, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("H$lineWrite", $dprm, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("K$lineWrite", $rezl, 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("L$lineWrite", "1273,45", 's');
                                    $writeQuery->getActiveSheet()->setCellValueExplicit("AA$lineWrite", $ngod, 's');
                                    if ($prty < 5) {
                                        $writeQuery->getActiveSheet()->setCellValueExplicit("I$lineWrite", "-", 's');
                                        $writeQuery->getActiveSheet()->setCellValueExplicit("J$lineWrite", "+", 's');
                                    } else {
                                        $writeQuery->getActiveSheet()->setCellValueExplicit("I$lineWrite", "+", 's');
                                        $writeQuery->getActiveSheet()->setCellValueExplicit("J$lineWrite", "-", 's');
                                    }
                                    $writeQuery->getActiveSheet()->
                                            duplicateStyle($writeQuery->getActiveSheet()->
                                                    getStyle('A16'), "A$lineWrite:L$lineWrite");
                                    $writeQuery->getActiveSheet()->getRowDimension($lineWrite)->setRowHeight(-1);
                                    $writeQuery->getActiveSheet()->insertNewRowBefore($lineWrite + 1, 1);
                                    $lineWrite++;

//Магическая подмена                                    
                                    $rec["COMMENT"] = iconv('utf-8', 'cp866', 'НЕ НАЙДЕНА ДЕЙСТВУЮЩАЯ СТРАХОВКА НА ТЕРРИТОРИИ ОРЕНБУРГСКОЙ ОБЛАСТИ');
                                    $rec["ST"] = iconv('utf-8', 'cp866', 'Find New');
                                    $rec["FAM"] = "";
                                    $rec["IM"] = "";
                                    $rec["OT"] = "";
                                    $rec["DR"] = "";
                                    $rec["DOCTP"] = "";
                                    $rec["DOCS"] = "";
                                    $rec["DOCN"] = "";
                                    $rec["SS"] = "";
                                    $rec["ENP"] = "";
                                    $rec["OPDOC"] = "";
                                    $rec["SPOL"] = "";
                                    $rec["NPOL"] = "";
                                    $rec["DBEG"] = "";
                                    $rec["DEND"] = "";
                                    $rec["DSTOP"] = "";
                                    $rec["OKATO"] = "";
                                    $rec["QOGRN"] = "";
                                    $rec["SMO"] = "";
                                    $rec["CS"] = "";
                                    unset($rec['deleted']);
                                    $rec = array_values($rec);
                                    /* if (true) {
                                      $x = $x . var_dump($rec);
                                      } */
                                    \dbase_replace_record($dbf, $rec, $i);




//выход из цикла по достижении нужного числа незастрахованных
                                    if ($model->needInsuredCount == $count) {
                                        break;
                                    }
                                }
                                $lineRead++;
                            }

                            $lineWrite = $count + 16;
                            $writeQuery->getActiveSheet()->removeRow($lineWrite);
                            $sumStr = "Итого: " . str_replace(".", ",", round(1273.45 * $count, 2)) . " рублей";
                            $writeQuery->getActiveSheet()->setCellValue("K$lineWrite", $sumStr);
                            \dbase_pack($dbf);
                            \dbase_close($dbf);
                            $writeQuery->getActiveSheet()->setCellValue("AB$lineWrite", "end");
                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeQuery);
                            $writer->save("uploads/незастрахованные.xls");
                            $x = "<p>Реестр незастрахованных готов, Вы можете скачать их по этой "
                                    . "<a href='uploads/незастрахованные.xls' "
                                    . "target='_blank'>ссылке</a></p>"
                                    . "<p>Скорректированный ответ ТФОМС скачайте по этой "
                                    . "<a href='uploads/answer.dbf' "
                                    . "target='_blank'"
                                    . ">ссылке</a></p>"
                                    . "<p>Сделано $count незастрахованных</p>"
                                    . "<p>Увы этот скрипт не Брюс Всемогущий, "
                                    . "так что не забудьте этот файлик "
                                    . "подрихтовать по красоте)))</p>" . $x;
                        }
                    }
//Если не важно число незастрахованных
                    else {
                        $writeQuery->getActiveSheet()->removeRow($lineWrite);
                        $sumStr = "Итого: " . str_replace(".", ",", round(1273.45 * $count, 2)) . " рублей";
                        $writeQuery->getActiveSheet()->setCellValue("K$lineWrite", $sumStr);
                        \dbase_close($dbf);
                        $writeQuery->getActiveSheet()->setCellValue("AB$lineWrite", "end");
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeQuery);
                        $writer->save("uploads/незастрахованные.xls");
                        $x = "<p>Реестр незастрахованных готов, Вы можете скачать их по этой "
                                . "<a href='uploads/незастрахованные.xls' "
                                . "target='_blank'>ссылке</a></p>"
                                . "<p>Увы этот скрипт не Брюс Всемогущий, так что не забудьте этот файлик подрихтовать по красоте)))</p>" . $x;
                    }
                }
                /* } catch (\Exception $ex) {
                  $x = $x . "<p>Закралась ошибка, незастрахованных не удалось сгенерировать, работайте ручками(((</p>";
                  // } */
                return $this->render('generate-insured', ['model' => $model, 'x' => $x]);
            }
        }
        return $this->render('generate-insured', ['model' => $model, 'x' => $x]);
    }

    /**
     * Удаляет папку (выдрано с инета)
     * @param type $dir удаляемая папка
     */
    public static function removeDirectory($dir) {
        if ($objs = glob($dir . "/*")) {
            foreach ($objs as $obj) {
                is_dir($obj) ? @removeDirectory($obj) : @unlink($obj);
            }
        }
        @rmdir($dir);
    }

    /**
     * Дробит запрос страховой принадлежности на файлы по 50 человек и 
     * запаковывает файлы в Zip архив
     */
    public static function splitInsuredQueryAndZip() {
        try {
            InsuredController::removeDirectory('Дробь');
            mkdir('Дробь');
//создаем Zip-архив с
            $zip = new \ZipArchive();
            $zip_name = "uploads/Пакет на идентификацию.zip";
            @unlink($zip_name);
            if ($zip->open($zip_name, \ZIPARCHIVE::CREATE) !== TRUE) {
                $error = "* Sorry ZIP creation failed at this time";
            }
//ложим в него нужные файлы
            $zip->addFile("uploads/запрос на идентификацию.xls", "запрос на идентификацию.xls");
            $zip->addEmptyDir("Дробь");
            $zip->addFile("uploads/остаток.xls", "остаток.xls");
//Дробим запрос на части и ложим каждую часть в архив
            $i = 2;
            $j = 2;
            $numberFile = 1;
//Открываем файл запроса
            $readXls = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
            $readQuery = $readXls->load("uploads/запрос на идентификацию.xls");
            $readQuery->setActiveSheetIndex(0);
//Открываем шаблон запроса
            $writeXlsAdd = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
            $writeQuery = $writeXlsAdd->load("templates/res-query.xls");
            $writeQuery->setActiveSheetIndex(0);
//Обходим в бесконечном цикле
            while (true) {
                $fam = $readQuery->getActiveSheet()->getCell("A" . $i)->getValue();
                if ($fam == "end") {
                    break;
                }
                $imya = $readQuery->getActiveSheet()->getCell("B" . $i)->getValue();
                $otch = $readQuery->getActiveSheet()->getCell("C" . $i)->getValue();
                $pol = $readQuery->getActiveSheet()->getCell("D" . $i)->getValue();
                $dr = $readQuery->getActiveSheet()->getCell("E" . $i)->getValue();
                $typedoc = $readQuery->getActiveSheet()->getCell("F" . $i)->getValue();
                $serdoc = $readQuery->getActiveSheet()->getCell("G" . $i)->getValue();
                $nomdoc = $readQuery->getActiveSheet()->getCell("H" . $i)->getValue();
                $snils = $readQuery->getActiveSheet()->getCell("I" . $i)->getValue();
                $writeQuery->getActiveSheet()->setCellValueExplicit("A" . $j, $fam, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("B" . $j, $imya, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("C" . $j, $otch, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("D" . $j, $pol, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("E" . $j, $dr, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("F" . $j, $typedoc, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("G" . $j, $serdoc, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("H" . $j, $nomdoc, 's');
                $writeQuery->getActiveSheet()->setCellValueExplicit("I" . $j, $snils, 's');
                if ($j == 51) {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeQuery);
                    $writer->save("Дробь/запрос на идентификацию $numberFile.xls");
                    $zip->addFile("Дробь/запрос на идентификацию $numberFile.xls", "Дробь/запрос на идентификацию $numberFile.xls");
                    unset($writeQuery);
                    unset($writeXlsAdd);
                    $numberFile++;
                    $j = 2;
                    $i++;
                    $writeXlsAdd = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
                    $writeQuery = $writeXlsAdd->load("templates/res-query.xls");
                    $writeQuery->setActiveSheetIndex(0);
                    continue;
                } else {
                    $j++;
                    $i++;
                }
            }
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writeQuery);
            $writer->save("Дробь/запрос на идентификацию $numberFile.xls");
            $zip->addFile("Дробь/запрос на идентификацию $numberFile.xls", "Дробь/запрос на идентификацию $numberFile.xls");
            $zip->close();
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Генерация файла для статиста и стола справок
     * @return type
     */
    public function actionReportInsured($message = "") {
        $model = new InsuredXls();
        if (Yii::$app->request->isPost) {
            $model->xlsFile = UploadedFile::getInstance($model, 'xlsFile');
            if ($model->upload()) {
                if ($model->reportInsured()) {
                    $message = "<h4>Файлы для статиста "
                            . "и стола справок успешно сгенерированы!</h4>"
                            . "<p><Для статиста файл вы можете "
                            . "скачать по <a href = 'uploads/незастрахованные статист.xlsx'>ссылке</a>/p>"
                            . "<p><Для стола справок файл "
                            . "вы можете скачать по <a href = 'uploads/незастрахованные стол справок.xls'>ссылке</a>/p>";
                } else {
                    $message = "<h4>Ошибка!!! Не удалось сгенерировать файлы!</h4>";
                }
                return $this->render('report-insured', ['model' => $model,
                            'message' => $message]);
            }
        } else
            return $this->render('report-insured', ['model' => $model,
                        'message' => $message]);
    }

    /**
     * Возвращает документы физ. лица в массив
     * @param \app\components\individuals\request\searchIndividual $individual
     */
    public static function returnDocs($individual) {
        $response = [
            'birthcert' => [],
            'passport' => [],
            'snils' => [],
            'oms' => [],
            'birthday' => ''
        ];
        $drNorm = $individual->birthDate;
        $individual->birthDate = date("Y-m-d", strtotime($individual->birthDate));
        if ($individual->validate()) {

            $result = Yii::$app->individuals->send($individual);
            if (@gettype($result->individual) == "NULL") {
                return null;
            } else {
                if (@gettype($result->individual) == "string") {
                    $ind = new getIndividual();
                    $ind->param = $result->individual;
                    $req = Yii::$app->individuals->send_param($ind);
                    $fam = $req->surname;
                    $imya = $req->name;
                    $otch = $req->patrName;
                    $dr = date("d.m.Y", strtotime(str_replace("+05:00", "", $req->birthDate)));
//1. Совпали ФИО и ДР
                    if ($dr == $drNorm) {
                        $docsReq = new getIndividualDocuments();
                        $docsReq->param = $result->individual;
                        $docsResp = Yii::$app->individuals->send_param($docsReq);
                        if (@gettype($docsResp->document) == "array") {
                            foreach ($docsResp->document as $doc) {
                                $docReq = new getDocument();
                                $docReq->param = $doc;
                                $docResp = Yii::$app->individuals->send_param($docReq);
                                switch ($docResp->type) {
                                    case 1:
                                        $response['birthcert'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number];
                                        break;
                                    case 13:
                                        $response['passport'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number];
                                        break;
                                    case 19:
                                        $response['snils'] = ['nomdoc' => $docResp->number];
                                        break;
                                    case 24:
                                        $response['oms'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number,
                                            'type' => 24];
                                        break;
                                    case 25:
                                        $response['oms'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number,
                                            'type' => 25];
                                        break;
                                    case 26:
                                        $response['oms'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number,
                                            'type' => 26];
                                        break;
                                }
                            }
                            $response['birthday'] = $dr;
                            return $response;
                        } elseif (@gettype($docsResp->document) == "string") {
                            $docReq = new getDocument();
                            $docReq->param = $docsResp->document;
                            $docResp = Yii::$app->individuals->send_param($docReq);
                            $response['birthday'] = $dr;
                            switch ($docResp->type) {
                                case 1:
                                    $response['birthcert'] = ['seria' => $docResp->series,
                                        'nomdoc' => $docResp->number];
                                    break;
                                case 13:
                                    $response['passport'] = ['seria' => $docResp->series,
                                        'nomdoc' => $docResp->number];
                                    break;
                                case 19:
                                    $response['snils'] = ['nomdoc' => $docResp->number];
                                    break;
                                case 24:
                                    $response['oms'] = ['seria' => $docResp->series,
                                        'nomdoc' => $docResp->number,
                                        'type' => 24];
                                    break;
                                case 25:
                                    $response['oms'] = ['seria' => $docResp->series,
                                        'nomdoc' => $docResp->number,
                                        'type' => 25];
                                    break;
                                case 26:
                                    $response['oms'] = ['seria' => $docResp->series,
                                        'nomdoc' => $docResp->number,
                                        'type' => 26];
                                    break;
                            }
                            return $response;
                        } elseif (@gettype($docResp->document) == "NULL") {
                            $response['birthday'] = $dr;
                            return $response;
                        }
                    } else {
//2. Совпали ФИО, разница в ДР 1 день/месяц/год
                        if (mb_strtolower($fam) == mb_strtolower($individual->surname) and
                                mb_strtolower($imya) == mb_strtolower($individual->name) and
                                mb_strtolower($otch) == mb_strtolower($individual->patrName)) {
                            $docsReq = new getIndividualDocuments();
                            $docsReq->param = $result->individual;
                            $docsResp = Yii::$app->individuals->send_param($docsReq);

                            if (@gettype($docsResp->document) == "array") {
                                foreach ($docsResp->document as $doc) {
                                    $docReq = new getDocument();
                                    $docReq->param = $doc;
                                    $docResp = Yii::$app->individuals->send_param($docReq);
                                    switch ($docResp->type) {
                                        case 1:
                                            $response['birthcert'] = ['seria' => $docResp->series,
                                                'nomdoc' => $docResp->number];
                                            break;
                                        case 13:
                                            $response['passport'] = ['seria' => $docResp->series,
                                                'nomdoc' => $docResp->number];
                                            break;
                                        case 19:
                                            $response['snils'] = ['nomdoc' => $docResp->number];
                                            break;
                                        case 24:
                                            $response['oms'] = ['seria' => $docResp->series,
                                                'nomdoc' => $docResp->number,
                                                'type' => 24];
                                            break;
                                        case 25:
                                            $response['oms'] = ['seria' => $docResp->series,
                                                'nomdoc' => $docResp->number,
                                                'type' => 25];
                                            break;
                                        case 26:
                                            $response['oms'] = ['seria' => $docResp->series,
                                                'nomdoc' => $docResp->number,
                                                'type' => 26];
                                            break;
                                    }
                                }
                                $response['birthday'] = $dr;
                                return $response;
                            } elseif (@gettype($docsResp->document) == "string") {
                                $docReq = new getDocument();
                                $docReq->param = $docsResp->document;
                                $docResp = Yii::$app->individuals->send_param($docReq);
                                $response['birthday'] = $dr;
                                switch ($docResp->type) {
                                    case 1:
                                        $response['birthcert'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number];
                                        break;
                                    case 13:
                                        $response['passport'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number];
                                        break;
                                    case 19:
                                        $response['snils'] = ['nomdoc' => $docResp->number];
                                        break;
                                    case 24:
                                        $response['oms'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number,
                                            'type' => 24];
                                        break;
                                    case 25:
                                        $response['oms'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number,
                                            'type' => 25];
                                        break;
                                    case 26:
                                        $response['oms'] = ['seria' => $docResp->series,
                                            'nomdoc' => $docResp->number,
                                            'type' => 26];
                                        break;
                                }
                                return $response;
                            } elseif (@gettype($docsResp->document) == "NULL") {
                                $response['birthday'] = $dr;
                                return $response;
                            }
                        }
                    }
                }
//Нашлось много физ. лиц
                else {
                    foreach ($result->individual as $id) {
                        $ind = new getIndividual();
                        $ind->param = $id;
                        $req = Yii::$app->individuals->send_param($ind);
                        $fam = $req->surname;
                        $imya = $req->name;
                        $otch = $req->patrName;
                        $dr = date("d.m.Y", strtotime(str_replace("+05:00", "", $req->birthDate)));
                        Yii::$app->session->setFlash('success', "$fam $imya $otch $dr");                        
//1. Совпали ФИО и ДР
                        if (mb_strtolower($fam) == mb_strtolower($individual->surname) and
                                mb_strtolower($imya) == mb_strtolower($individual->name) and
                                mb_strtolower($otch) == mb_strtolower($individual->patrName)) {
                            Yii::$app->session->setFlash('success', "Из много физ. лиц нашелся подходящий!");
                            $docsReq = new getIndividualDocuments();
                            $docsReq->param = $id;
                            $docsResp = Yii::$app->individuals->send_param($docsReq);
                            if (@gettype($docsResp->document) == "array") {
                                foreach ($docsResp->document as $doc) {
                                    $docReq = new getDocument();
                                    $docReq->param = $doc;
                                    $docResp = Yii::$app->individuals->send_param($docReq);
                                    switch ($docsResp->type) {
                                        case 1:
                                            $response['birthcert'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number];
                                            break;
                                        case 13:
                                            $response['passport'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number];
                                            break;
                                        case 19:
                                            $response['snils'] = ['nomdoc' => $docsResp->number];
                                            break;
                                        case 24:
                                            $response['oms'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number,
                                                'type' => 24];
                                            break;
                                        case 25:
                                            $response['oms'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number,
                                                'type' => 25];
                                            break;
                                        case 26:
                                            $response['oms'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number,
                                                'type' => 26];
                                            break;
                                    }
                                }
                                $response['birthday'] = $dr;
                                return $response;
                            } elseif (@gettype($docsResp->document) == "string") {
                                $docReq = new getDocument();
                                $docReq->param = $docsResp->document;
                                $docResp = Yii::$app->individuals->send_param($docReq);
                                $response['birthday'] = $dr;
                                try {
                                    switch ($docsResp->type) {
                                        case 1:
                                            $response['birthcert'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number];
                                            break;
                                        case 13:
                                            $response['passport'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number];
                                            break;
                                        case 19:
                                            $response['snils'] = ['nomdoc' => $docsResp->number];
                                            break;
                                        case 24:
                                            $response['oms'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number,
                                                'type' => 24];
                                            break;
                                        case 25:
                                            $response['oms'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number,
                                                'type' => 25];
                                            break;
                                        case 26:
                                            $response['oms'] = ['seria' => $docsResp->series,
                                                'nomdoc' => $docsResp->number,
                                                'type' => 26];
                                            break;
                                    }
                                } catch (\Exception $ex) {
                                    print_r($docResp) . "<br><br>";
                                }
                                return $response;
                            } elseif (@gettype($docsResp->document) == "NULL") {
                                $response['birthday'] = $dr;
                                return $response;
                            }
                        }
                    }
                }
            }
            return;
        }
    }

    /**
     * Приводит свидетельство о рождении к виду ХХ-ББ 000000
     * весьма костыльная, НЕ РАБОТАЕТ!!!
     * @param type $ser
     * @param type $number
     * @return type
     */
    public static function convertBirthCert($ser, $number) {
        echo var_dump($ser) . " ";
        echo var_dump($number) . "<br>";
        if (@gettype($ser) == "NULL" or $ser != "") {
            str_replace("-", "", $ser);
            $str = $number;
            preg_match_all('/\d+$/', $str, $number);

            if (@gettype($number[0][0]) == "string")
                $number = $number[0][0];
            else
                $number = $str;

            $seria = $ser;
            preg_match_all('/^([A-z]+)/', $ser, $seria);
            if (@gettype($seria[0][0]) == "string") {
                $seria = $seria[0][0];
                preg_match_all('/([А-я]+)(?=\d+$)/u', $ser, $seriaEnd);
                if (@gettype($seriaEnd[0][0]) == "string") {
                    $seriaEnd = $seriaEnd[0][0];
                    $seria = "$seriaEnd-" . $seria;
                    $seria = mb_strtoupper($seria);
                } else
                    $seria = $ser;
            } else
                $seria = $ser;
            echo var_dump($seria) . " ";
            echo var_dump($number) . "<br><hr>";
            return ["", ""];
        }
        /* try {if ($ser === "") {
          $str = $number;
          preg_match_all('/\d+$/', $str, $number);
          $number = $number[0][0];
          preg_match_all('/^([A-z]+)/', $str, $ser);
          $ser = $ser[0][0];
          preg_match_all('/([А-я]+)(?=\d+$)/u', $str, $nom);
          $seria = "$ser-" . $nom[0][0];
          }}
          finally {
          if (@gettype($seria)!="array" and @gettype($seria)!="NULL")
          $seria = mb_strtoupper($seria);
          else $seria = ""; */

//}
    }

    /**
     * Приводит СНИЛС к виду ххх-ххх-ххх хх
     * @param type $snils
     * @return type
     */
    public static function convertSNILS($snils) {
        if (strlen($snils) == 11) {
            return substr($snils, 0, 3) . "-" . substr($snils, 3, 3) . "-" . substr($snils, 6, 3) . " " . substr($snils, 9);
        }
        return $snils;
    }

    /**
     * Приводит паспорт к виду хх хх хххххх
     * @param string $ser
     * @param string $nom
     * @return type
     */
    public static function convertPassport($ser, $nom) {
        if (strlen($ser) == 4)
            $ser = substr($ser, 0, 2) . " " . substr($ser, 2);
        return [$nom, $ser];
    }

    function str_ends_with($haystack, $needle) {
        return mb_substr($haystack, - mb_strlen($needle), mb_strlen($needle)) === $needle;
    }

    /**
     * Определяет пол по ФИО
     * @param type $fam Фамилия
     * @param type $imya Имя
     * @param type $otch Отчество
     * @return string
     */
    public static function find_pol($fam, $imya, $otch) {
        $man_name = array('абрам', 'аверьян', 'авраам', 'агафон', 'адам', 'азар', 'акакий', 'аким', 'аксён', 'александр', 'алексей', 'альберт', 'анатолий', 'андрей', 'андрон', 'антип', 'антон', 'аполлон', 'аристарх', 'аркадий', 'арнольд', 'арсений', 'арсентий', 'артем', 'артём', 'артемий', 'артур', 'аскольд', 'афанасий', 'богдан', 'борис', 'борислав', 'бронислав', 'вадим', 'валентин', 'валерий', 'варлам', 'василий', 'венедикт', 'вениамин', 'веньямин', 'венцеслав', 'виктор', 'вилен', 'виталий', 'владилен', 'владимир', 'владислав', 'владлен', 'всеволод', 'всеслав', 'вячеслав', 'гавриил', 'геннадий', 'георгий', 'герман', 'глеб', 'григорий', 'давид', 'даниил', 'данил', 'данила', 'демьян', 'денис', 'димитрий', 'дмитрий', 'добрыня', 'евгений', 'евдоким', 'евсей', 'егор', 'емельян', 'еремей', 'ермолай', 'ерофей', 'ефим', 'захар', 'иван', 'игнат', 'игорь', 'илларион', 'иларион', 'илья', 'иосиф', 'казимир', 'касьян', 'кирилл', 'кондрат', 'константин', 'кузьма', 'лавр', 'лаврентий', 'лазарь', 'ларион', 'лев', 'леонард', 'леонид', 'лука', 'максим', 'марат', 'мартын', 'матвей', 'мефодий', 'мирон', 'михаил', 'моисей', 'назар', 'никита', 'николай', 'олег', 'осип', 'остап', 'павел', 'панкрат', 'пантелей', 'парамон', 'пётр', 'петр', 'платон', 'потап', 'прохор', 'роберт', 'ростислав', 'савва', 'савелий', 'семён', 'семен', 'сергей', 'сидор', 'спартак', 'тарас', 'терентий', 'тимофей', 'тимур', 'тихон', 'ульян', 'фёдор', 'федор', 'федот', 'феликс', 'фирс', 'фома', 'харитон', 'харлам', 'эдуард', 'эммануил', 'эраст', 'юлиан', 'юлий', 'юрий', 'яков', 'ян', 'ярослав');
        $woman_name = array('авдотья', 'аврора', 'агата', 'агния', 'агриппина', 'ада', 'аксинья', 'алевтина', 'александра', 'алёна', 'алена', 'алина', 'алиса', 'алла', 'альбина', 'амалия', 'анастасия', 'ангелина', 'анжела', 'анжелика', 'анна', 'антонина', 'анфиса', 'арина', 'белла', 'божена', 'валентина', 'валерия', 'ванда', 'варвара', 'василина', 'василиса', 'вера', 'вероника', 'виктория', 'виола', 'виолетта', 'вита', 'виталия', 'владислава', 'власта', 'галина', 'глафира', 'дарья', 'диана', 'дина', 'ева', 'евгения', 'евдокия', 'евлампия', 'екатерина', 'елена', 'елизавета', 'ефросиния', 'ефросинья', 'жанна', 'зиновия', 'злата', 'зоя', 'ивонна', 'изольда', 'илона', 'инга', 'инесса', 'инна', 'ирина', 'ия', 'капитолина', 'карина', 'каролина', 'кира', 'клавдия', 'клара', 'клеопатра', 'кристина', 'ксения', 'лада', 'лариса', 'лиана', 'лидия', 'лилия', 'лина', 'лия', 'лора', 'любава', 'любовь', 'людмила', 'майя', 'маргарита', 'марианна', 'мариетта', 'марина', 'мария', 'марья', 'марта', 'марфа', 'марьяна', 'матрёна', 'матрена', 'матрона', 'милена', 'милослава', 'мирослава', 'муза', 'надежда', 'настасия', 'настасья', 'наталия', 'наталья', 'нелли', 'ника', 'нина', 'нинель', 'нонна', 'оксана', 'олимпиада', 'ольга', 'пелагея', 'полина', 'прасковья', 'раиса', 'рената', 'римма', 'роза', 'роксана', 'руфь', 'сарра', 'светлана', 'серафима', 'снежана', 'софья', 'софия', 'стелла', 'степанида', 'стефания', 'таисия', 'таисья', 'тамара', 'татьяна', 'ульяна', 'устиния', 'устинья', 'фаина', 'фёкла', 'фекла', 'феодора', 'хаврония', 'христина', 'эвелина', 'эдита', 'элеонора', 'элла', 'эльвира', 'эмилия', 'эмма', 'юдифь', 'юлиана', 'юлия', 'ядвига', 'яна', 'ярослава');
        $man_firstname_end = array('ов', 'ев', 'ин', 'ын', 'ой', 'цкий', 'ский', 'цкой', 'ской');
        $woman_firstname_end = array('ова', 'ева', 'ина', 'ая', 'яя', 'екая', 'цкая');
        $man_surname_end = array('ович', 'евич', 'ич');
        $woman_surname_end = array('овна', 'евна', 'ична');

        $imya = mb_strtolower($imya);
        $fam = mb_strtolower($fam);
        $otch = mb_strtolower($otch);

        $pol_imya = 3;
        $pol_otch = 3;
        $pol_fam = 3;
//начинаем с имени
        if ($imya != "") {
//если имя присуще только М или Ж возврашаем верный пол
            if (in_array($imya, $man_name)) {
                $pol_imya = 1;
            } else if (in_array($imya, $man_name)) {
                $pol_imya = 2;
            }

//в противном случаем пробуем проверим по отчеству если оно есть
        }
        if ($fam != "") {
            foreach ($man_firstname_end as $ot) {
                if (mb_substr($fam, - mb_strlen($ot), mb_strlen($ot)) === $ot) {
                    $pol_fam = 1;
                }
            }
            foreach ($woman_firstname_end as $ot) {
                if (mb_substr($fam, - mb_strlen($ot), mb_strlen($ot)) === $ot) {
                    $pol_fam = 2;
                }
            }
        }
        if ($otch != "") {
            foreach ($man_surname_end as $ot) {
                if (mb_substr($otch, - mb_strlen($ot), mb_strlen($ot)) === $ot) {
                    $pol_otch = 1;
                }
            }
            foreach ($woman_surname_end as $ot) {
                if (mb_substr($otch, - mb_strlen($ot), mb_strlen($ot)) === $ot) {
                    $pol_otch = 2;
                }
            }
        }

        $matrix = $pol_fam . $pol_imya . $pol_otch;

        $res = array(
            "111" => "1%ALL",
            "222" => "2%ALL",
            "311" => "1%NAME&OTCH",
            "322" => "2%NAME&OTCH",
            "113" => "1%NAME&FAM",
            "223" => "2%NAME&FAM",
            "331" => "1%OTCH",
            "332" => "2%OTCH",
            "133" => "1%FAM",
            "233" => "2%FAM",
            "313" => "1%NAME",
            "323" => "2%NAME",
            "131" => "1%OTCH&FAM",
            "232" => "2%OTCH&FAM",
            "333" => "3%NOTFOUND"
        );


        if (array_key_exists($matrix, $res))
            return $res["$matrix"];
        else {
            return "0%WRONG%$matrix";
        }
    }

    /**
     * Проверяет ФИО и пол
     * @param type $fam
     * @param type $imya
     * @param type $otch
     * @param type $pol
     * @param type $id
     * @return type
     */
    public static function check_pol($fam, $imya, $otch, $pol, $id) {
        $dost_descr = array(
            "ALL" => "Определен по ФИО",
            "NAME&OTCH" => "Определен по имени и отчеству",
            "NAME&FAM" => "Определен по имени и фамилии",
            "NAME" => "Определен по имени",
            "OTCH" => "Определен по отчеству",
            "OTCH&FAM" => "Определен по отчеству и фамилии",
            "FAM" => "Определен по фамилии",
            "NOTFOUND" => "Не удалось определить пол!",
            "WRONG" => "очень печально(("
        );

        $result = InsuredController::find_pol($fam, $imya, $otch);
        $res_pol = strtok($result, "%");
        $dost = strtok("%");
        if ($res_pol == "0") {
            return ["correct" => false, "descr" => "У $fam $imya $otch ($id) конкретный косяк!!!;Достоверность:"
                . " {$dost_descr[$dost]} >>>>    $pol != $res_pol ;" . strtok("%") . "<br>"];
            /* return "У $fam $imya $otch ($id) конкретный косяк!!!;Достоверность:"
              . " {$dost_descr[$dost]} >>>>    $pol != $res_pol ;" . strtok("%") . "<br>"; */
        } else
        if ($res_pol != "3") {
            if ($res_pol != $pol) {
                return ["correct" => false, "descr" => "У $fam $imya $otch ($id) возможно не верен пол!"
                    . ";Достоверность: {$dost_descr[$dost]} >>>>    $pol != $res_pol<br>"];
            } else
                return ["correct" => true, "descr" => "У $fam $imya $otch ($id) все хорошо"
                    . ";Достоверность: {$dost_descr[$dost]} >>>>    $pol == $res_pol<br>"];
        } else
            return ["correct" => false, "descr" => "Нет ФИО $fam $imya $otch ($id)"
                . ";Достоверность: {$dost_descr[$dost]} >>>>    $pol == $res_pol<br>"];
    }

}
