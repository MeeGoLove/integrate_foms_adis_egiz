<?php

namespace app\models;

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

use yii\base\Model;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice;
use app\models\ArchiveCalls;
use app\models\ArchiveCallsOld;

/**
 * Модель для работы с экспертизами
 *
 * @author maimursv
 */
class ExpertParser extends Model {

    /**
     * @var xlsFile
     */
    public $xlsFile;

    /**
     *
     * @var \DateTime Дата начала экспертизы 
     */
    public $start;

    /**
     *
     * @var \DateTime Дата окончания экспертизы
     */
    public $end;

    public function rules() {
        parent::rules();
        return [
            [['start'], 'required'],
            [['end'], 'required'],
        ];
    }

    public function upload() {
        if ($this->validate()) {
            $this->xlsFile->saveAs('uploads/expertise.xls');
            return true;
        } else {
            return false;
        }
    }

    public function attributeLabels() {
        parent::attributeLabels();
        return [
            'xlsFile' => 'Файл Excel под названием остаток',
            'start' => 'Дата начала экспертизы',
            'end' => 'Дата окончания экспертизы'];
    }

    public static function parseNumbersOfCalls($start, $end) {
        //Открыть полученный документ
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
        $expertise = $reader->load("uploads/expertise.xls");
        //Установить активным первый лист Excel - документов
        $expertise->setActiveSheetIndex(0);
        $i = 1;
        //Обойти все строки файла, условие выхода пустая строка
        while (true) {
            if (@gettype($expertise->getActiveSheet()->getCell("A" . $i)->getValue()) == "NULL" or $i == 1000) {
                break;
            }
            $ngod = $expertise->getActiveSheet()->getCell("A" . $i)->getValue();
            $res = ArchiveCalls::find()->
                            where(['ngod' => $ngod])->
                            andWhere(['dprm', '>=', $start])->
                            andWhere(['dprm', '<=', $end])->limit(1)->one();
            if (@gettype($res->numv) != "NULL") {
                $expertise->getActiveSheet()->getCell("B" . $i)->setValueExplicit($res->numv, 's');
                $expertise->getActiveSheet()->getCell("C" . $i)->setValueExplicit($res->stbr, 's');
                $expertise->getActiveSheet()->getCell("D" . $i)->setValueExplicit($res->dprm, 's');
            }
            //$expertise->getActiveSheet()->getCell("B" . $i)->setValueExplicit($i . " " . $start, 's');
            $i++;
        }
        //Сохранить документ
        $writerExpertise = new \PhpOffice\PhpSpreadsheet\Writer\Xls($expertise);
        $writerExpertise->save("uploads/Готово.xls");

        return true;
    }

}
