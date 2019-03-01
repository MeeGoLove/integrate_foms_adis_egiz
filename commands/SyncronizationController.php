<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\commands;

//use yii\console\Controller;
use app\components\individuals\request\searchIndividual;
use app\components\employees\request\getEmployee;
use app\components\employees\request\getEmployeePosition;
use app\components\employees\request\createEmployee;
use app\components\employees\request\employee;
use app\components\employees\request\createEmployeePosition;
use app\components\employees\request\employeePosition;
use app\components\smp1c\request\GetSnils;
use app\components\resources\request\getLocations;
use app\components\resources\request\getLocation;
use app\components\resources\request\createLocation;
use app\components\resources\request\location;
use app\models\Cmpstaff;
use app\models\ArchiveCalls;
use app\models\Sync1cEgisAdis;
use app\models\Temp1c;
use app\models\SavedCalls;
use app\components\adis\request\SendDataTransferCall;
use app\components\adis\request\brigade;
use app\components\adis\request\call;
use app\components\adis\request\patient;
use app\components\adis\request\senior_personnel;
use app\components\adis\request\medical_supplies;
use yii\helpers\BaseConsole;
use \yii2sshconsole\Controller;

/**
 * Консольный контроллер для синхронизации медиков между 1С, АДИС и ЕГИСз
 * 
 * В составе две процедуры
 *
 * @author maimursv
 */
class SyncronizationController extends Controller {

    /**
     * Функция рекомпилирует программу выгрузки вызовов
     */
    public function actionRecompileZotonic() {
        try {
            SyncronizationController::connect('172.30.25.189', [
                'username' => 'zotonic',
                'password' => 'common_server_zotonic', // optional
            ]);

            // Or via private key
            /*
              $this->connect('example.com', [
              'username' => 'myusername',
              'key' => '/path/to/private.key',
              'password' => 'mykeypassword', // optional
              ]);
             */
            $output = SyncronizationController::run(
                            'cd /usr/home/zotonic/zotonic; ./recompile.sh', function($line) {
                        echo $line;
                    });

            echo "<p>Послана команда рекомпиляции на сервер Zabbix, если что-то пойдет "
            . "не так, попросите отдел АСУ перезагрузить Zabbix, а пока можете попить чай (5-10 минут)</p>\r\n";
        } catch (\Exception $ex) {
            echo "<p>При посылке команды рекомпиляции, что-то совсем пошло "
            . "не так. Возникла ошибка: <b>" . $ex->getMessage() . "</b><p>\r\n";
        }
    }

    /**
     * Синхронизация ресурсов, проходит по всем ресурсам, затем находит должность и сотрудника, по его табельному номеру находит запись в таблице синхронизации и выставляет идентификатор ресурса этому работнику 
     * @param type $organisationId Идентификатор организации
     */
    public function actionSyncResource($organisationId = "2052479") {
        try {
            $start = date("H:i:s");
            $synced = 0;
            $not_synced = 0;
            $not_found = 0;
            $locationsRequest = new getLocations();
            $locationsRequest->clinic = $organisationId;
            $locationResponse = \Yii::$app->resources->send($locationsRequest);

            foreach ($locationResponse->location as $location) {
                $locationRequest = new getLocation();
                $locationRequest->location = "$location";
                $locationResponse = \Yii::$app->resources->send($locationRequest);
                //var_dump($response);
                $employeePosRequest = new getEmployeePosition();

                if (@gettype($locationResponse->location->employeePositionList->
                                EmployeePosition->employeePosition) == "string") {
                    $employeePosRequest->id = $locationResponse->location->employeePositionList->
                            EmployeePosition->employeePosition;
                    $employeePosResponse = \Yii::$app->employees->send($employeePosRequest);

                    $employee = new getEmployee();
                    $employee->id = $employeePosResponse->employeePosition->employee;
                    $employeeResponse = \Yii::$app->employees->send($employee);
                    $tabnumber = $employeeResponse->employee->number;
                    $syncedEmployees = Sync1cEgisAdis::findAll(['tab1c' => $tabnumber]);
                    if (!empty($syncedEmployees)) {
                        if (@gettype($syncedEmployees) == "array") {
                            foreach ($syncedEmployees as $syncedEmployee) {
                                $syncedEmployee->egis_id = $location;
                                $syncedEmployee->egis_sync_date = date("Y-m-d H:i:s");
                                $syncedEmployee->update();
                            }
                        } else {
                            $syncedEmployees->egis_id = $location;
                            $syncedEmployees->egis_sync_date = date("Y-m-d H:i:s");
                            $syncedEmployees->update();
                        }
                        $synced++;
                    } else {
                        $not_synced++;
                    }
                } else
                    $not_found++;
            }
            $end = date("H:i:s");
            \Yii::info("Синхронизированно $synced ресурсов, не найдено $not_synced"
                    . " ресурсов!, не найдено $not_found позиций\r\n "
                    . "Время старта $start, время окончания $end\r\n", 'egis_pass');
        } catch (\Exception $ex) {
            \Yii::info("При попытке выполнения задачи cинхронизации ресурсов"
                    . " возникла ошибка<br>Текст ошибки: " . $ex->getMessage(), 'egis_pass');
        }
        return 0;
    }

    /**
     * Функция создает медика, должность и ресурс если в таблице синхронизации нет идентификатора ресурса и дата увольнения не заполнена
     * 
     * 
     * @return int
     */
    public function actionCreateMedics() {
        try {
            \Yii::info("Запущена задача создания медиков для которых нет ресурсов", 'egis_pass');
            $medicsToEgiz = Sync1cEgisAdis::findBySql("select * from sync_1c_egis_adis "
                            . "where (job like '%врач%' or job like '%фельдшер%') and "
                            . "dismissal is NULL and egis_id is NULL "
                            . "and employment is not null")->asArray()->all();
            foreach ($medicsToEgiz as $medic) {
                $surname = $medic["surname"];
                $name = $medic["name"];
                $patrname = $medic["patrname"];
                $birthday = $medic["birthday"];
                $snils = $medic["snils"];
                $tab1c = $medic["tab1c"];
                $fromDate = $medic["employment"];
                $idMedic = SyncronizationController::FindIndividual($surname, $name, $patrname, $birthday, $snils);
                if (@gettype($idMedic) != "array") {
                    if ($idMedic != "notFound") {
                        /*
                          6781 - Врач скорой медицинской помощи
                          6443 - Фельдшер скорой медицинской помощи
                         */
                        $position = 6781;
                        if ($medic["codeadis"] >= 2000)
                            $position = 6443;
                        $positionId = SyncronizationController::CreateEmployeeAndPosition($idMedic, $tab1c, $position, $fromDate);
                        if (($positionId != "Позиция не создана!") && ($positionId != "Сотрудник не создан!")) {
                            $location = SyncronizationController::CreateResource($positionId);
                            if ($location == "Ресурс не создан!")
                                \Yii::info("Для сотрудника с таб. номером $tab1c"
                                        . " $location", 'egis_pass');
                            else {
                                \Yii::info("Для сотрудника с таб. номером $tab1c "
                                        . "создана должность $positionId и ресурс"
                                        . " $location ", 'egis_pass');
                                $syncedEmployee = Sync1cEgisAdis::findOne($tab1c);
                                $syncedEmployee->egis_id = $location;
                                $syncedEmployee->egis_sync_date = date("Y-m-d H:i:s");
                                $syncedEmployee->update();
                            }
                        } else
                            \Yii::info("$positionId $tab1c", 'egis_pass');
                    } else
                        \Yii::info("Для сотрудника с таб. номером $tab1c не найдено "
                                . "физ. лица, создайте его ручками", 'egis_pass');
                } else
                    \Yii::info("Для сотрудника с таб. номером $tab1c "
                            . "найдено более одного физического лица! Выберите его ручками", 'egis_pass');
            }
        } catch (\Exception $ex) {
            \Yii::info("При попытке выполнения задачи создания медиков, "
                    . "для которых нет ресурсов возникла ошибка<br>Текст ошибки: " . $ex->getMessage(), 'egis_pass');
        }
        return 0;
    }

    /**
     * функция создает ресурс для заданного идентификатора
     * должности сотрудника 
     * 
     * !!! продумать ошибки
     * @param type $employeePosition
     * @param type $organisationId
     * @param type $departmentId
     * @return string
     */
    public static function CreateResource($employeePosition, $organisationId = "2052479", $departmentId = "1312") {
        $locationRequest = new createLocation();
        $locationRequest->location = new location();
        $locationRequest->location->employeePosition = $employeePosition;
        $locationRequest->location->organization = $organisationId;
        $locationRequest->location->department = $departmentId;
        $locationResponse = \Yii::$app->resources->send($locationRequest);
        if (@gettype($locationResponse->location) == "string") {
            return $locationResponse->location;
        } else
            return "Ресурс не создан!";
    }

    /**
     * Функция находит физическое лицо по ФИО и СНИЛС, и дате рождения
     * 
     */
    public function FindIndividual($surname, $name, $patrname = "", $birthday = "", $snils = "") {
        $searchIndividual = new searchIndividual();
        $searchIndividual->name = $name;
        $searchIndividual->surname = $surname;
        $searchIndividual->patrName = $patrname;
        if ($birthday != "") {
            $searchIndividual->birthDate = $birthday;
        }
        if ($snils != "") {
            $searchIndividual->docTypeId = 19;
            $searchIndividual->docNumber = $snils;
        }
        $searchIndividualResponse = \Yii::$app->individuals->send($searchIndividual);
        switch (@gettype($searchIndividualResponse->individual)) {
            case "NULL":
                return "notFound";
                break;
            case "array":
                return $searchIndividualResponse->individual;
                break;
            case "string":
                return $searchIndividualResponse->individual;
                break;
        };
    }

    /**
     * функция создает сотрудника и позицию должности сотрудника
     * @param type $individualId Ид физического лица
     * @param type $tabnumber Табельный номер
     * @param type $position Позиция (Фельдшер/врач)
     * @param type $fromDate Дата приема на работу
     * @param type $organisationId Идентификатор организации
     * @return string
     */
    public static function CreateEmployeeAndPosition($individualId, $tabnumber, $position, $fromDate, $organisationId = "2052479") {
        $employeeRequest = new createEmployee();
        $employeeRequest->organization = $organisationId;
        $employeeRequest->employee = new employee();
        $employeeRequest->employee->individual = "$individualId";
        $employeeRequest->employee->number = "$tabnumber";
        $employee = \Yii::$app->employees->send($employeeRequest);
        if (@gettype($employee->id) == "string") {
            $idEmployee = $employee->id;
            $positionRequest = new createEmployeePosition();
            $positionRequest->employeePosition = new employeePosition();
            $positionRequest->employeePosition->employee = $idEmployee;
            $positionRequest->employeePosition->position = $position;
            $positionRequest->employeePosition->employmentType = 1;
            $positionRequest->employeePosition->hiringType = 0;
            $positionRequest->employeePosition->rate = 1.0;
            $positionRequest->employeePosition->fromDate = $fromDate;
            $position = \Yii::$app->employees->send($positionRequest);
            if (@gettype($position->employeePosition) == "string") {
                return $position->employeePosition;
            } else
                return "Позиция не создана!";
        } else
            return "Сотрудник не создан!";
    }

    public static function ConvertBirthdayAdis($birthday) {
        $day = $birthday[0] . $birthday[1];
        $mounth = $birthday[2] . $birthday[3];
        $year = $birthday[4] . $birthday[5] . $birthday[6] . $birthday[7];
        return "$year-$mounth-$day";
    }

    public static function ConvertBirthday1C($birthday) {
        $day = $birthday[0] . $birthday[1];
        $mounth = $birthday[3] . $birthday[4];
        $year = $birthday[6] . $birthday[7] . $birthday[8] . $birthday[9];
        return "$year-$mounth-$day";
    }

    public static function ConvertSnils($snils) {
        return substr($snils, 0, 3) . "-" . substr($snils, 3, 3) . "-" .
                substr($snils, 6, 3) . " " . substr($snils, 9);
    }

    /**
     * Функция сопоставляет медиков из кадровой базы АДИС и кадровой базы 1С
     * @return int
     */
    public function actionUpdateFromAdisAnd1C() {
        try {
            $cmpstaff = Cmpstaff::findBySql("select * from cmpstaff "
                                    . "where code<>'0000' and name not like '%не исп%';")
                            ->asArray()->all();

            //обошли в цикле всех сотрудников из справочника кадровая база
            foreach ($cmpstaff as $rab) {
                //в справочнике кадровая база
                //поле инфо должно быть из 12 символов
                //поле имя не должно содержать слова "НЕ ИСПОЛЬЗОВАТЬ"
                //поле имя не должно быть из цифр
                if (mb_strlen($rab["info"]) == 12 and ! ((mb_strpos($rab["name"], 'НЕ ИС')
                        or preg_match_all('/\d/', $rab["name"])))) {
                    //табельный номер 1С в адисе это последние 4 цифры поля инфо
                    $tab1c = substr($rab["info"], 8);

                    $medic = Temp1c::findAll(['tabnum' => $tab1c]);
                    if (count($medic) != 0) {
                        $syncmedic = new Sync1cEgisAdis();
                        foreach ($medic as $job) {
                            $syncmedic->tab1c = $job->tabnum;
                            $syncmedic->surname = strtok($job->fullname, " ");
                            $syncmedic->name = strtok(" ");
                            $syncmedic->patrname = strtok(" ");
                            $syncmedic->snils = $job->snils;
                            $syncmedic->job = $job->job;
                            $syncmedic->birthday = SyncronizationController::ConvertBirthday1C($job->dr);
                            $syncmedic->codeadis = $rab["code"];
                            $syncmedic->nameadis = $rab["name"];
                            $syncmedic->dradis = SyncronizationController::ConvertBirthdayAdis(substr($rab["info"], 0, 8));
                            $syncmedic->tab1cadis = $tab1c;
                            $syncmedic->adis_to_1c_syncdate = date("Y-m-d H:i:s");
                            $syncmedic->pol = $job->pol;
                            $temp = strtok($job->reg, " ");
                            preg_match_all('/(\d+\.\d+\.\d+)/', $job->reg, $res);
                            switch ($temp) {
                                case "Прием":
                                    $tempDay = strtotime(SyncronizationController::ConvertBirthday1C($res[0][0]));
                                    if ($syncmedic->employment == "")
                                        $tempDay2 = strtotime(date("Y-m-d"));
                                    else
                                        $tempDay2 = strtotime($syncmedic->employment);
                                    if ($tempDay2 >= $tempDay) {
                                        $syncmedic->employment = SyncronizationController::ConvertBirthday1C($res[0][0]);
                                    }
                                    break;
                                case "Кадровое":
                                    $tempDay = strtotime(SyncronizationController::ConvertBirthday1C($res[0][0]));
                                    if ($syncmedic->employment == "")
                                        $tempDay2 = strtotime(date("Y-m-d"));
                                    else
                                        $tempDay2 = strtotime($syncmedic->employment);
                                    if ($tempDay2 >= $tempDay) {
                                        $syncmedic->employment = SyncronizationController::ConvertBirthday1C($res[0][0]);
                                    }
                                    break;
                                case "Увольнение":
                                    $syncmedic->dismissal = SyncronizationController::ConvertBirthday1C($res[0][0]);
                                    break;
                            }
                        }
                        $syncmedic->save();
                    } else {
                        if ($rab["code"] <= 3000) {
                            \Yii::info("Для сотрудника табельный 1с: $tab1c, табельный АДИС:" . $rab["code"] . " не найдена запись в 1С", 'egis_pass');
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            \Yii::info("При попытке выполнения задачи cинхронизации данных АДИС и 1С"
                    . " возникла ошибка<br>Текст ошибки: " . $ex->getMessage(), 'egis_pass');
        }
        return 0;
    }

    /**
     * Очищает и заново заполняет временную БД с сотрудниками из 1С
     */
    public function actionFillTempDb() {
        $clearTempDB = Temp1c::deleteAll();
        $request = new GetSnils();
        $response = \Yii::$app->smp1c->send_param($request)->return->el;
        foreach ($response as $el) {
            $addMedics = new Temp1c();
            $addMedics->tabnum = $el->tabnum;
            $addMedics->fullname = $el->fullname;
            $addMedics->job = $el->job;
            $addMedics->snils = $el->snils;
            $addMedics->pol = $el->pol;
            $addMedics->dr = $el->dr;
            $addMedics->reg = $el->reg;
            $addMedics->save();
        }
        return 0;
    }

    /**
     * Функция преобразует массив манипуляций из БД Адис 
     * в массив, пригодный для передачи по SOAP
     * @param type $param
     * @return type
     */
    public static function parseMeds($param) {
        $kr = str_replace('";s:3:"izm";N;s:3:"cnt";i:', "", $param);
        preg_match_all('/\d{5}/', $kr, $med);
        $meds = [];
        foreach ($med[0] as $medc) {
            $id_medsr = substr($medc, 0, 4);
            // if ($id_medsr[0] . $id_medsr[1] . $id_medsr[2] == 800)
            $meds[] = $id_medsr;
        }
        return $meds;
    }

    /**
     * Отправляет вызовы в ЕГИСЗ из БД АДИС по протоколу SOAP, два параметра: дата начала и дата окончания (ГГГГ/ММ/ДД)
     * @param type $start
     * @param type $end
     * @return int
     */
    public function actionSendCalls($start, $end) {
        \Yii::info("Получена задача ручной выгрузки вызовов за период с " .
                $start . " по " .
                $end, 'egis_pass');
        \Yii::info("Время запуска задачи " .
                date("H:i:s", time() + 5 * 3600), 'egis_pass');
        try {
            $calls = ArchiveCalls::findBySql("SELECT ngod, tprm, fam, imya, "
                            . "otch, pol, rezl, tabn, medc_str, mest, ds1, spol, "
                            . "datr, snils, "
                            . " inf2, inf5, stbr FROM archive_calls WHERE "
                            . "dprm>=\"$start\" and dprm<=\"$end\""
                            . ";")->asArray()->all();
            $countSended = 0;
            foreach ($calls as $call) {
                $trCallResp = new SendDataTransferCall();
                $trCallResp->ext_system_code = 9999;
                $trCallResp->call = new call();
                $trCallResp->call->global_call_number = $call["ngod"];
                $trCallResp->call->call_time = $call["tprm"] . "+05:00";
                $trCallResp->call->diagnosis = $call["ds1"];
                $trCallResp->call->place = $call["mest"];
                $trCallResp->call->result = $call["rezl"];
                $trCallResp->brigade = new brigade();
                $trCallResp->brigade->senior_personnel = new senior_personnel();
                $trCallResp->brigade->senior_personnel->code = $call["tabn"];
                $trCallResp->medical_supplies[] = new medical_supplies();
                $meds = SyncronizationController::parseMeds($call["medc_str"]);
                $medicSupplies = [];
                foreach ($meds as $med) {
                    $medicSup = new medical_supplies();
                    $medicSup->code = $med;
                    $medicSupplies[] = $medicSup;
                }
                $trCallResp->medical_supplies = $medicSupplies;
                //var_dump($trCallResp->medical_supplies);
                $trCallResp->call->patient = new patient();
                $trCallResp->call->patient->surname = $call["fam"];
                $trCallResp->call->patient->name = $call["imya"];
                $trCallResp->call->patient->patronymic = $call["otch"];
                $trCallResp->call->patient->gender = $call["pol"];
                $trCallResp->call->patient->insurance = $call["spol"];
                $trCallResp->call->patient->snils = $call["snils"];
                $trCallResp->call->patient->document_type = $call["inf2"];
                $trCallResp->call->patient->document_number = $call["inf5"];
                $trCallResp->brigade->substation_control=$call["stbr"];
                if ($call["datr"] != "") {
                    $day = $call["datr"][0] . $call["datr"][1];
                    $mounth = $call["datr"][3] . $call["datr"][4];
                    $year = $call["datr"][6] . $call["datr"][7] . $call["datr"][8] . $call["datr"][9];
                    $trCallResp->call->patient->birthday = $year . "-" . $mounth . "-" . $day;
                }
                try {

                    $trCallReq = \Yii::$app->adis->send($trCallResp);
                    //$trCallReq = \app\controllers\EgisExportController::loadCalls($trCallResp);
                } catch (\Exception $ex) {
                    if ($ex->getMessage() == "Error Fetching http headers") {

                        sleep(60);
                        try {
                            $trCallReq = \Yii::$app->adis->send($trCallResp);
                            //$trCallReq = \app\controllers\EgisExportController::loadCalls($trCallResp);
                        } catch (\Exception $ex1) {
                            if ($ex1->getMessage() == "Error Fetching http headers")
                                \Yii::info("Вызов " . $call["ngod"] . ", дата " . $call["tprm"] . " не удалось послать, "
                                        . "возможно из-за проблем на сервере ЕГИЗ", 'egis_pass');
                            else
                                \Yii::info("Вызов " . $call["ngod"] . ", дата " . $call["tprm"] . " не удалось послать, "
                                        . "по неведомым причинам, вот текст"
                                        . " исключения в помощь:" . $ex1->getMessage(), 'egis_pass');
                        }
                    } else
                        \Yii::info("Вызов " . $call["ngod"] . ", дата " . $call["tprm"] . " не удалось послать, "
                                . "по неведомым причинам, вот текст"
                                . " исключения в помощь:" . $ex->getMessage(), 'egis_pass');
                }

                //$trCallReq = \app\controllers\EgisExportController::loadCalls($trCallResp);
                //sleep(1);
                $countSended++;
                $name = $this->ansiFormat("$countSended из " . count($calls), BaseConsole::FG_GREEN);
                echo "Отправлен вызов " . $trCallResp->call->global_call_number . "               "
                . "$name\r\n";
            }

            $savedRecords = SavedCalls::findBySql("select count(*) as saved from savedCalls "
                            . "where dprm>=\"$start\" and dprm<=\"$end\" and isFromAdis=0")->asArray()->all();
            $count = $savedRecords[0]["saved"];
            \Yii::info("Отправлено " . count($calls) . " записей"
                    , 'egis_pass');
            \Yii::info("Сохранено в ЕГИСЗ " . $count . " записей"
                    , 'egis_pass');
            \Yii::info("Время завершения задачи " .
                    date("H:i:s", time() + 5 * 3600), 'egis_pass');
        } catch (\Exception $ex) {
            \Yii::info("При попытке выполнения задачи ручной выгрузки вызовов в ЕГИСЗ "
                    . " возникла ошибка<br>Текст ошибки: " . $ex->getMessage(), 'egis_pass');
        }
        return 0;
    }

}
