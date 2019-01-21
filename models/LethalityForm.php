<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Модель для выбора даты для досуточной летальности LethalityForm
 *
 * @author maimursv
 */
class LethalityForm extends Model {

    public $start;
    public $end;

    /**
     * @return array the validation rules.
     */
    public function rules() {
        return [
            // Дата начала и окончания обязательны для ввода
                [['start', 'end'], 'required'],            
        ];
    }

}
