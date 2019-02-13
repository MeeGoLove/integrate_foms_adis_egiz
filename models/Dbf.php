<?php

namespace app\models;

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Модель для загрузки файла с незастрахованными
 *
 * @author maimursv
 */
class Dbf extends Model {

    /**
     * @var xlsFile
     */
    public $xlsFile;

    /**
     * @var dbfFile
     */
    public $dbfFile;
    
    /**
     * 
     * @var needInsuredCount 
     */
    public $needInsuredCount = 0;

    public function rules() {
        parent::rules();
        return [
                //['dbfFile' ,'file'],
                [['needInsuredCount'], 'required'], 
                //['xlsFile' ,'file']
              ];
    }

    public function upload() {
        if ($this->validate()) {
            $this->dbfFile->saveAs('uploads/answer.dbf');
            $this->xlsFile->saveAs('uploads/notinsured.xls');
            return true;
        } else {
            return false;
        }
    }

    public function attributeLabels() {
        parent::attributeLabels();
        return ['dbfFile' => 'dbf-файл ответа ТФОМС (Склеенный!!!)',
            'xlsFile' => 'Файл Excel под названием остаток',
            'needInsuredCount' => 'Желаемое число незастрахованных (оставьте 0, если Вам нужное любое количество незастрахованных)'];
    }

}
