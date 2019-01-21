<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

namespace app\models;

use Yii;
use yii\base\Model;
use yii\validators\RequiredValidator;

/**
 * Description of Weekdays
 *
 * @author maimursv
 */
class Weekdays extends Model {

    /**
     * @var array Субботы
     */
    public $saturdays;

    /**
     * @var array Воскресенья
     */
    public $sundays;

    public function rules() {
        return [[['saturdays'], 'required'],
            [['sundays'], 'required']];
    }

    public function attributeLabels() {
        return [
            'saturdays' => 'Субботы',
            'sundays' => 'Воскресенья',
        ];
    }

}
