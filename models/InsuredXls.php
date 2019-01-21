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
class InsuredXls extends Model
{
    /**
     * @var xlsFile
     */
    public $xlsFile;

    public function rules()
    {
        return [            
        ];
    }
    
    public function upload()
    {
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
}