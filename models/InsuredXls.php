<?php

namespace app\models;

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

use yii\base\Model;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice;
use app\models\ArchiveCalls;

/**
 * Модель для загрузки файла с незастрахованными
 *
 * @author maimursv
 */
class InsuredXls extends Model {

    /**
     * @var xlsFile
     */
    public $xlsFile;

    public function rules() {
        return [
        ];
    }

    public function upload() {
        if ($this->validate()) {
            $this->xlsFile->saveAs('uploads/insured.xls');
            return true;
        } else {
            return false;
        }
    }

    public function attributeLabels() {
        return [
            'xlsFile' => 'Файл на выгрузку'
        ];
    }

    /**
     * Метод генерации файлов для стола справок и врача-статиста
     * @return boolean Результат генерации файлов
     */
    public static function reportInsured() {
        /* try { */
//открытие исходного файла
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
        $notInsuredXLS = $reader->load("uploads/insured.xls");
//Открытие файлов - шаблонов
//для статиста
        $writeXls = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $statXLS = $writeXls->load("templates/res-stat.xlsx");
//для стола справок
        $writeXls1 = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
        $helpDeskXLS = $writeXls1->load("templates/res-help.xls");
//Установить активный первый лист Excel - документов
        $notInsuredXLS->setActiveSheetIndex(0);
        $statXLS->setActiveSheetIndex(0);
        $helpDeskXLS->setActiveSheetIndex(0);
//Инициализация временных переменных
        $needBreak = false;
        $i = 3;
        $start = 16;
        $cost = "1272.86";
        $checkBreak = "";

        while (true) {
            //В начале каждой итерации проверяем, не пора ли покинуть цикл
            if ($needBreak) {
                break;
            }
            if ($checkBreak == "end") {
                $needBreak = true;
            }
//считать нужные сведения из исходного файла
            $fio = $notInsuredXLS->getActiveSheet()->getCell("B" . $start)->getCalculatedValue();
            $pol = $notInsuredXLS->getActiveSheet()->getCell("C" . $start)->getValue();
            $doc = $notInsuredXLS->getActiveSheet()->getCell("D" . $start)->getCalculatedValue();
            $dr = $notInsuredXLS->getActiveSheet()->getCell("E" . $start)->getValue();
            $adres = $notInsuredXLS->getActiveSheet()->getCell("F" . $start)->getCalculatedValue();
            $ds = $notInsuredXLS->getActiveSheet()->getCell("G" . $start)->getValue();
            $dprm = $notInsuredXLS->getActiveSheet()->getCell("H" . $start)->getValue();
            $neotl = $notInsuredXLS->getActiveSheet()->getCell("I" . $start)->getCalculatedValue();
            $extr = $notInsuredXLS->getActiveSheet()->getCell("J" . $start)->getCalculatedValue();
            $rezl = $notInsuredXLS->getActiveSheet()->getCell("K" . $start)->getValue();
            $ngod = $notInsuredXLS->getActiveSheet()->getCell("AA" . $start)->getValue();
            $checkBreak = $notInsuredXLS->getActiveSheet()->getCell("AB" . $start)->getValue();

//Записать сведения в строку Excel файла для статиста
            $statXLS->getActiveSheet()->getCell("A" . $i)->setValue($i - 2);
            $statXLS->getActiveSheet()->getCell("B" . $i)->setValueExplicit($fio, 's');
            $statXLS->getActiveSheet()->getCell("C" . $i)->setValueExplicit($pol, 's');
            $statXLS->getActiveSheet()->getCell("D" . $i)->setValueExplicit($doc, 's');
            $statXLS->getActiveSheet()->getCell("E" . $i)->setValueExplicit($dr, 's');
            $statXLS->getActiveSheet()->getCell("F" . $i)->setValueExplicit($adres, 's');
            $statXLS->getActiveSheet()->getCell("G" . $i)->setValueExplicit($ds, 's');
            $statXLS->getActiveSheet()->getCell("H" . $i)->setValueExplicit($dprm, 's');
            $statXLS->getActiveSheet()->getCell("I" . $i)->setValueExplicit($neotl, 's');
            $statXLS->getActiveSheet()->getCell("J" . $i)->setValueExplicit($extr, 's');
            $statXLS->getActiveSheet()->getCell("K" . $i)->setValueExplicit($rezl, 's');
            $statXLS->getActiveSheet()->getCell("L" . $i)->setValueExplicit($cost, 's');

//И в строку файла Excel для стола справок            
            $helpDeskXLS->getActiveSheet()->getCell("A" . ($i - 1))->setValue($i - 2);
            $helpDeskXLS->getActiveSheet()->getCell("B" . ($i - 1))->setValueExplicit($fio, 's');
            $helpDeskXLS->getActiveSheet()->getCell("C" . ($i - 1))->setValueExplicit($pol, 's');
            $helpDeskXLS->getActiveSheet()->getCell("D" . ($i - 1))->setValueExplicit($dr, 's');
            $helpDeskXLS->getActiveSheet()->getCell("E" . ($i - 1))->setValueExplicit($dprm, 's');
            $helpDeskXLS->getActiveSheet()->getCell("F" . ($i - 1))->setValueExplicit($ngod, 's');
            $dprmMySql = date('Y-m-d', strtotime($dprm));

//Нашли суточный номер вызова и номер п/с по годовому номеру и преобразованной дате 
            $call = ArchiveCalls::find()->
                                where(['ngod' => $ngod])->
                                andWhere(['>=', 'dprm', $dprmMySql])->
                                andWhere(['<=', 'dprm', $dprmMySql])->limit(1)->one();
            if (@gettype($call->numv) != "NULL") {
            $helpDeskXLS->getActiveSheet()->getCell("G" . ($i - 1))->setValueExplicit($call->numv, 's');
            $helpDeskXLS->getActiveSheet()->getCell("H" . ($i - 1))->setValueExplicit($call->stbr, 's');
            }             
            //$helpDeskXLS->getActiveSheet()->getCell("H" . ($i - 1))->setValueExplicit($dprmMySql, 's');
            $i++;
            $start++;
        }
//Сохранить все файлы
        $writerStat = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($statXLS);
        $writerStat->save("uploads/незастрахованные статист.xlsx");
        $writerHelpDesk = new \PhpOffice\PhpSpreadsheet\Writer\Xls($helpDeskXLS);
        $writerHelpDesk->save("uploads/незастрахованные стол справок.xls");
        return true;
        /* } catch (\Exception $ex) {
          return false;
          } */
    }

}
