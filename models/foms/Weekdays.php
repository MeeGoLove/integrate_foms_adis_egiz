<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

namespace app\models\foms;

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

    /**
     *
     * @var \DateTime Дата начала выгрузки реестра 
     */
    public $start;

    /**
     *
     * @var \DateTime Дата окончания выгрузки реестра
     */
    public $end;

    public function rules() {
        return [[['saturdays'], 'required'],
            [['sundays'], 'required'],
            [['start'], 'required'],
            [['end'], 'required']
        ];
    }

    public function attributeLabels() {
        return [
            'saturdays' => 'Субботы',
            'sundays' => 'Воскресенья',
            'start' => 'Дата начала выгрузки реестра',
            'end' => 'Дата окончания выгрузки реестра',
        ];
    }

    /**
     * Первый шаг выгрузки
     * 1. Получить от пользователя даты суббот и воскресений, начала и конца
     * 2. Послать файл с датами на сервер Zotonic
     * 3. Запустить на сервере Zotonic рекомпиляцию бинарных файлов
     * @param type $model
     */
    public static function writeDays($model) {
        try {
            $appendix = file_get_contents("reestr/foms_appendix_template.hrl");
            //$firstday = date("Y-m-01", strtotime("-20 days"));
            //$lastday = date("Y-m-t", strtotime("-20 days"));
            $firstday = $model->start;
            $lastday = $model->end;
            $appendix = str_replace("firstday", $firstday, $appendix);
            $appendix = str_replace("lastday", $lastday, $appendix);
            $i = 0;
            $days = "";
            foreach ($model->sundays as $day) {
                $days = $days . "\t\tfutils:parse_date(<<\"$day\">>),\r\n";
                $i++;
            }
            $daysZ = "";
            $i = 0;
            foreach ($model->saturdays as $day) {
                if ($i == count($model->saturdays) - 1) {
                    $days = $days . "\t\tfutils:parse_date(<<\"$day\">>)";
                    $daysZ = $daysZ . "\t\tfutils:parse_date(<<\"$day\">>)";
                    continue;
                }
                $days = $days . "\t\tfutils:parse_date(<<\"$day\">>),\r\n";
                $daysZ = $daysZ . "\t\tfutils:parse_date(<<\"$day\">>),\r\n";
                $i++;
            }
            $appendix = str_replace("holidayssss", $days, $appendix);
            $appendix = str_replace("saturdaysss", $daysZ, $appendix);
            file_put_contents("reestr/foms_appendix.hrl", $appendix);
            $remote_file = '/usr/home/zotonic/zotonic/user/sites/adisreports/support/foms_appendix.hrl';
            $local_file = 'reestr/foms_appendix.hrl';
            $mode = 'FTP_ASCII';
            $asynchronous = false;
            $file = Yii::$app->ftp->put($local_file, $remote_file, $mode, $asynchronous);
            shell_exec('cd /var/www/egiz; ./yii  syncronization/recompile-zotonic');
        } catch (\Exception $ex) {
            \Yii::info("При попытке сгенировать и записать даты возникла ошибка :" . $ex->getMessage(), 'egis_error');
        }
    }

}
