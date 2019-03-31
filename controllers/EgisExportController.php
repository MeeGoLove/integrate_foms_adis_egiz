<?php

namespace app\controllers;

use app\components\individuals\request\searchIndividual;
use app\components\cases\request\sendCase;
use app\components\visits\request\sendVisit;
use app\components\medservices\request\sendServiceRend;
use app\components\visits\request\diagnoses;
use app\models\Sync1cEgisAdis;
use app\components\patient\request\createPatient;
use app\components\patient\request\patientData;
use Yii;
use app\models\MdDiagnosis;
use app\controllers\AppController;
use app\models\SavedCalls;
use app\components\cases\request\searchCase;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EgisExport
 *
 * @auapthor maimursv
 */
class EgisExportController extends AppController {

    /**
     * Возвращает исход заболевания пригодный для ЕГИСЗ
     * @param string $code Код исхода заболевания
     * @return int
     */
    public static function getEgisOut($code) {
        switch ($code) {
            case "8003":
                return 17;
                break;
            case "8001":
                return 15;
                break;
            case "8002":
                return 16;
                break;
            default :
                return 16;
                break;
        }
    }

    /**
     * Возвращает результат обращения, пригодный для ЕГИСЗ
     * @param int $code
     * @return int
     */
    public static function getEgisResults($code) {
        $results = [
            1 => 41,
            2 => 39,
            3 => 40,
            4 => 45,
            5 => 13,
            6 => 43,
            7 => 47,
            8 => 39,
            9 => 45,
            11 => 68,
            12 => 68,
            13 => 104,
            14 => 68,
            15 => 68,
            16 => 68,
            19 => 107,
            21 => 103,
            22 => 69,
            23 => 103,
            28 => 42,
            29 => 106,
            31 => 105,
            32 => 105,
            41 => 103,
            42 => 69,
            51 => 68
        ];
        if (array_key_exists((int) $code, $results)) {
            return $results[(int) $code];
        } else
            return 103;
    }

    /**
     * Возвращает тип вызова неотложный или экстренный
     * Точнее соответствующие три поля для создания случая sendcase
     * Неотложка: это
     * - с 8:00 до 19:00, с пн по пт игнорируя с 10:00 до 16:00
     * - суббота с 8:00 до 19:00
     * - место оказания помощи квартира
     * - соответствующий диагноз
     * - не имеет результат 14 экстренная перевозка
     * - не имеет результат 15 плановая перевозка
     * Экстренные вызовы:
     * - все остальные
     * 
     * @param string $tprm дата время приема вызова
     * @param string $ds1 диагноз
     * @param string $mesto диагноз
     * @return array 
     */
    public static function getTypeCase($tprm, $ds1, $mesto, $rezl) {
        $start = "08:00:00";
        $endMorning = "09:59:59";
        $startEvening = "16:00:00";
        $end = "18:59:59";
        $endSaturday = "18:59:59";
        $diagnoses = ["I10", "G90.9", "J06.9", "M06.9", "M19.9", "G64", "G97", "D84.9", "H93.3", "J03.9"
            , "I20.9", "G96.9", "I67.9", "K31.9", "K63.9", "K86.9", "N39.9", "N64.9", "N71.9"];
        $tprmTimestamp = strtotime($tprm);
        $dayOfWeek = date("N", $tprmTimestamp);
        $day = date("Y-m-d", $tprmTimestamp);
        $neotlojka = false;
//Собственно проверка на неотложку
        if (($dayOfWeek >= 1) && ($dayOfWeek <= 5)) {//если рабочие дни
            if ($dayOfWeek == 6) {//если день суббота
                //Субботы не выгружаются!!! Оставил код на запас
                if (($mesto == "1") && ($rezl != "14") && ($rezl != "15")) {
                    $startDay = strtotime("$day $start");
                    $endDay = strtotime("$day $endSaturday");
                    if (($startDay <= $tprmTimestamp) && ($tprmTimestamp <= $endDay) && (in_array($ds1, $diagnoses))) {
                        $neotlojka = true;
                        $neotlojka = false;
//echo "В квартире/в нужное время/с нужным диагнозом<br>";
                    }//в квартире/в нужное время/с нужным диагнозом в субботу
                    else {
                        $neotlojka = FALSE; //неподходящие время/диагноз
//echo "Неподходящие время/диагноз в субботу<br>";
                    }
                } else {//не в квартире
                    $neotlojka = FALSE;
//echo "Не в квартире в субботу<br>";
                }
            } else {//если несуббота 
                if (($mesto == "1") && ($rezl != "14") && ($rezl != "15")) {
                    $startDay = strtotime("$day $start");
                    $endDayMorning = strtotime("$day $endMorning");
                    $startDayEvening = strtotime("$day $startEvening");
                    $endDay = strtotime("$day $end");
                    if (((($startDay <= $tprmTimestamp) && ($endDayMorning >= $tprmTimestamp)) || ((($startDayEvening <= $tprmTimestamp) && ($endDay >= $tprmTimestamp)))) && (in_array($ds1, $diagnoses))) {
                        $neotlojka = TRUE;
//echo "В квартире/в нужное время/с нужным диагнозом <u>рабочий день</u><br>";
                    } else {
                        $neotlojka = FALSE;
//echo "Неподходящие время/диагноз <u>рабочий день</u><br>";
                    }
                } else {//не в квартире
                    $neotlojka = FALSE;
//echo "Не в квартире в <u>рабочий день</u><br>";
                }
            }
        } else {
            $neotlojka = false; //если воскресенье
//echo "Воскресенье ;-)<br>";
        }

        if ($neotlojka)
            return array(
                'initGoalId' => 44,
                'careProvidingFormId' => 2,
                'caseTypeId' => 3
            );
        else
            return array(
                'initGoalId' => 100,
                'careProvidingFormId' => 1,
                'caseTypeId' => 7
            );
    }

    /**
     * Ищет физическое лицо по ФИО, д/р и СНИЛС
     * Возвращает идентификатор физического лица, либо массив 
     * идентификаторов, либо ничего
     * @param type $surname Фамилия
     * @param type $name Имя
     * @param type $patrname Отчество
     * @param type $birthday Дата рождения
     * @param type $snils СНИЛС
     * @return string
     */
    public static function findPatientbySnils($surname, $name = "", $patrname = "", $birthday = "", $snils = "") {
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
                return null;
            case "array":
                return $searchIndividualResponse->individual;
            case "string":
                return $searchIndividualResponse->individual;
        };
    }

    /**
     * Ищет физическое лицо по ФИО, д/р и любому имеющемуся документу
     * Возвращает идентификатор физического лица, либо массив 
     * идентификаторов, либо ничего
     * @param type $surname Фамилия
     * @param type $name Имя
     * @param type $patrname Отчество
     * @param type $birthday Дата рождения
     * @param type $typeDoc Тип документа
     * @param type $nomDoc № документа
     * @return string
     */
    public static function findPatientbyOtherDocument($surname, $name, $patrname = "", $birthday = "", $typeDoc = "", $nomDoc = "") {
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
                return null;
            case "array":
                return $searchIndividualResponse->individual;
            case "string":
                return $searchIndividualResponse->individual;
        };
    }

    /**
     * Создает случай обслуживания
     * @param type $patientUid Идентификатор пациента
     * @param type $ngod Номер карты вызова
     * @param type $medicalOrganizationId Идентификатор организации
     * @param type $caseTypeId Вид случая
     * @param type $careLevelId Вид медицинской помощи
     * @param type $fundingSourceTypeId Источник финансирования
     * @param type $careRegimenId Условия оказания 
     * @param type $initGoalId Цель первичного обращения
     * @param type $dprm Дата вызова
     * @param type $careProvidingFormId Форма оказания      
     * @return int
     */
    public static function createCase($patientUid, $ngod, $caseTypeId, $initGoalId, $dprm, $careProvidingFormId, $medicalOrganizationId = 2052479, $careLevelId = 21, $fundingSourceTypeId = 1, $careRegimenId = 22, $connectError = false, $addPatient = false) {
        $createCaseRequest = new sendCase();
        $createCaseRequest->patientUid = $patientUid;
        $createCaseRequest->uid = $ngod;
        $createCaseRequest->createdDate = $dprm;
        /*
          caseTypeId Вид случая (mc_case_type)
          ID 3   Вызов бригады неотложной помощи в часы работы поликлиник DEFAULT_REGIMEN_ID 6
          ID 7    Случай выезда СНМП DEFAULT_REGIMEN_ID 22
         */
        $createCaseRequest->caseTypeId = $caseTypeId;
        /*
          initGoalId Цель первичного обращения (mc_case_init_goal)
          ID 44 8.1 Оказание неотложной помощи бригадами СМП в часы работы поликлиник
          ID 100 03 Оказание скорой помощи */
        $createCaseRequest->initGoalId = $initGoalId;
        /*
          careProvidingFormId Условия оказания(md_care_providing_form)
          ID 1    Экстренная
          ID 2    Неотложная
          ID 3    Плановая
          ID 4    неотложная, экстренная
          ID 5    плановая, неотложная
          ID 6    плановая, экстренная, неотложная
          ID 7     плановая, экстренная
          ID 8    экстренная, неотложная
          ID 9    экстренная, неотложная, плановая
          ID 10   экстренная, плановая
         */

        $createCaseRequest->careProvidingFormId = $careProvidingFormId;
        $createCaseRequest->medicalOrganizationId = $medicalOrganizationId;
        /* careLevelId     Вид медицинской помощи(mc_care_level)
          ID 21 скорая специализированная медицинская помощь */
        $createCaseRequest->careLevelId = $careLevelId;
        $createCaseRequest->fundingSourceTypeId = $fundingSourceTypeId;
        /*
          careRegimenId Условия оказания (mc_care_regimen)
          ID 22 Скорая медицинская помощь */
        $createCaseRequest->careRegimenId = $careRegimenId;
        try {
            $createCaseResponse = Yii::$app->cases->send($createCaseRequest);
            Yii::info("Создали случай {" . $createCaseResponse->id . "} "
                    . "для пациента $patientUid "
                    , 'egis_pass');
            return $createCaseResponse->id;
        } catch (\Exception $ex) {
            switch ($ex->getMessage()) {
                //Проработать момент дублирования случаев!
                case "Вы пытаетесь сохранить дублирующиеся данные (МО, uid, тип случая, дата создания, пациент)":
                    Yii::info("При создании случая для вызова $ngod "
                            . " возникла проблема <b>дубликации</b> случаев" . "\t код ошибки: " .
                            $ex->getCode(), 'egis_pass');
                    return null;
                //При ошибке соединения попробовать еще раз через 30 секунд
                case "Could not connect to host":
                    if ($connectError) {
                        Yii::info("При создании случая для пациента $patientUid "
                                . "возникла ошибка:" . $ex->getMessage() . "\t код ошибки: " .
                                $ex->getCode(), 'egis_error');
                        return "connect_error";
                    } else {
                        sleep(30);
                        return EgisExportController::createCase($patientUid, $ngod, $caseTypeId, $initGoalId, $dprm, $careProvidingFormId, $medicalOrganizationId, $careLevelId, $fundingSourceTypeId, $careRegimenId, true);
                    }
                default :
                    $pattern = '/(Пациент с уникальным идентификатором [A-z0-9]+ не найден)/mu';
                    $noPatient = preg_match_all($pattern, $ex->getMessage());
                    if ($noPatient == 1) {
                        if (!$addPatient) {
                            Yii::info("При создании случая для вызова $ngod найдено"
                                    . " физ. лицо, но не существует пациента, "
                                    . "предпринята попытка создания пациента<br>", 'egis_pass');
                            try {
                                $patRequest = new createPatient();
                                $patRequest->patientId = $patientUid;
                                $patRequest->patientData = new patientData();
                                $patRequest->patientData->vip = false;
                                $patResponse = Yii::$app->patient->send($patRequest);
                            } catch (\Exception $ex1) {
                                Yii::info("При создании случая для вызова $ngod для пациента $patientUid "
                                        . "возникла ошибка:" . $ex1->getMessage(), 'egis_error');
                            }
                            sleep(10);
                            return EgisExportController::createCase($patientUid, $ngod, $caseTypeId, $initGoalId, $dprm, $careProvidingFormId, $medicalOrganizationId, $careLevelId, $fundingSourceTypeId, $careRegimenId, true, true);
                        } else {
                            Yii::info("При создании случая для вызова $ngod найдено"
                                    . " физ. лицо, но не существует пациента, и "
                                    . "создать пациента не удалось", 'egis_error');
                            return null;
                        }
                    } else {
                        Yii::info("При создании случая для вызова $ngod для пациента $patientUid "
                                . "возникла ошибка:" . $ex->getMessage(), 'egis_error');
                        return null;
                    }
            }
        }
    }

    /**
     * Возвращает диагноз пригодный для ЕГИЗ
     * @param type $ds1 Диагноз АДИС
     * @return array
     */
    public static function getDiagnos($ds1) {
        /*
          stageId Классификатор этапов установки диагноза (mc_stage)
          ID 1     Направительный
          ID 2     Предварительный (первичный, приемного отделения)
          ID 3     Клинический
          ID 4     Заключительный (клинический заключительный)
          ID 5     Патологоанатомический


          typeId Тип диагноза (mc_diagnosis_type)
          ID 1    Основной
          ID 2    Сопутствующий
          ID 3    Осложнение основного

         */
        $diagnosId = MdDiagnosis::findBySql("select md_diagnosis.id from "
                        . "md_diagnosis where code = \"$ds1\" limit 1")->asArray()->all();

        if (@gettype($diagnosId["0"]["id"]) != "NULL")
            return ['stageId' => 4,
                'typeId' => 2,
                'diagnosId' => $diagnosId["0"]["id"],
                'main' => true];
        else {
            Yii::info("$ds1 не найден в справочнике диагнозов ЕГИСЗ!", 'egis_error');
            return ['stageId' => 4,
                'typeId' => 2,
                'diagnosId' => null,
                'main' => true];
        }
    }

    /**
     * Создает визит
     * @param type $caseId Идентификатор случая
     * @param type $stageId Этап установки диагноза (направит./заключит. и пр.)
     * @param type $typeIdDiagnos Тип диагноза (Основной, сопутствующий, осложнение)
     * @param type $diagnosId Идентификатор диагноза
     * @param type $dprm Дата вызова
     * @param type $tprm Время вызова
     * @param type $goalId
     * @param type $typeId Вид посещения: Заболевание, Профилактика/патронаж.
     * @param type $placeId Идентификатор места вызова
     * @param type $profileId Медицинский профиль
     * @param type $deseaseId Исход заболевания
     * @param type $visitResultId Результат посещения
     */
    public static function createVisit($caseId, $stageId, $typeIdDiagnos, $diagnosId, $dprm, $tprm, $goalId, $typeId, $profileId, $deseaseId, $visitResultId, $resourceGroupId = null, $connectError = true) {
        $visitRequest = new sendVisit();
        $visitRequest->admissionDate = $dprm;
        $visitRequest->caseId = $caseId;
        $visitRequest->deseaseResultId = $deseaseId;
        $visitRequest->goalId = $goalId;
        $visitRequest->outcomeDate = $dprm;
        $tprm = date("H:i:s", strtotime($tprm));
        $visitRequest->outcomeTime = $tprm;
        $visitRequest->placeId = 2;
        $visitRequest->profileId = $profileId;
        $visitRequest->resourceGroupId = $resourceGroupId;
        $visitRequest->typeId = $typeId;
        $visitRequest->visitResultId = $visitResultId;
        $visitRequest->diagnoses = new diagnoses();
        $visitRequest->diagnoses->diagnosId = $diagnosId;
        $visitRequest->diagnoses->establishmentDate = $dprm;
        $visitRequest->diagnoses->main = true;
        $visitRequest->diagnoses->stageId = $stageId;
        $visitRequest->diagnoses->typeId = $typeIdDiagnos;
        try {
            $visitRequest = Yii::$app->visits->send($visitRequest);
            Yii::info("Создали посещение {" . $visitRequest->id . "} "
                    . "для случая $caseId", 'egis_pass');
            return $visitRequest->id;
        } catch (\Exception $ex) {
            if ($ex->getMessage() == 'Could not connect to host') {
                if ($connectError) {
                    Yii::info("При создании посещения для случая $caseId возникла "
                            . "ошибка:" . $ex->getMessage(), 'egis_error');
                    return null;
                } else {
                    sleep(30);
                    return EgisExportController::createVisit($caseId, $stageId, $typeIdDiagnos, $diagnosId, $dprm, $tprm, $goalId, $typeId, $profileId, $deseaseId, $visitResultId, $resourceGroupId, true);
                }
            } else {
                Yii::info("При создании посещения для случая $caseId возникла "
                        . "ошибка:" . $ex->getMessage(), 'egis_error');
                return null;
            }
        }
    }

    /**
     * Возвращает идентификатор ресурса ЕГИЗ по коду сотрудника АДИС (если он есть)
     * @param type $tabAdis Код сотрудника в АДИС 
     * @return type
     */
    public static function findResource($tabAdis) {
        $resource = Sync1cEgisAdis::findBySql("select sync_1c_egis_adis.egis_id from "
                                . "sync_1c_egis_adis where codeadis = '$tabAdis' limit 1")
                        ->asArray()->all();
        if (@gettype($resource["0"]["egis_id"]) != "NULL")
            return $resource["0"]["egis_id"];
        else {
            Yii::info("Сотрудник $tabAdis не найден в справочнике синхронизации 1С/АДИС/ЕГИСЗ!", 'egis_error');
            return null;
        }
    }

    /**
     * Возвращает идентификатор услуги по коду сотрудника АДИС
     * @param type $tabAdis Код сотрудника АДИС
     * @return int
     */
    public static function findService($tabAdis) {
        if ($tabAdis != "") {
            switch ($tabAdis[0]) {
                case "1":
                    return 506976;
                case "2":
                    return 506977;
                default :
                    return 506977;
            }
        } else {
            return 506976;
        }
    }

    /**
     * Проверяет полученные в SOAP данные на NULL (то есть когда поле не пришло)
     * возвращает либо пустую строку, либо исходные данные
     * @param type $param
     * @return string
     */
    public static function checkData($param) {
        if (@gettype($param) == "NULL") {
            return "";
        } else {
            return $param;
        }
    }

    /**
     * Переводит время из АДИС в нужный формат
     * @param type $callTime время в фомате гггг-мм-ддТчч:мм:сс+ЧЧ:ПП
     * @return type
     */
    public static function converCallTime($callTime) {
        $callTime = str_replace("T", " ", $callTime);
        return substr($callTime, 0, strlen($callTime) - 6);
    }

    /**
     * Создает оказанную услугу
     * @param type $patientUid Идентификатор пациента
     * @param type $medicalCaseId Идентификатор случая
     * @param type $stepId Идентификатор посещения/ЗОГ
     * @param type $dprm Дата вызова
     * @param type $serviceId Идентификатор типа услуги
     * @param type $orgId Идентификатор организации
     * @param type $fundingSourceTypeId Тип источника финансирования
     */
    public static function createServiceRend($patientUid, $medicalCaseId, $stepId, $dprm, $serviceId, $resourceGroupId = "", $orgId = 2052479, $fundingSourceTypeId = 1, $connectError = false) {
        $rendRequest = new sendServiceRend();
        $rendRequest->dateFrom = $dprm;
        $rendRequest->plannedDate = $dprm;
        $rendRequest->fundingSourceTypeId = $fundingSourceTypeId;
        $rendRequest->isRendered = true;
        $rendRequest->isUrgent = true;
        $rendRequest->medicalCaseId = $medicalCaseId;
        $rendRequest->orgId = $orgId;
        $rendRequest->patientUid = $patientUid;
        $rendRequest->quantity = 1;
        $rendRequest->serviceId = $serviceId;
        $rendRequest->stepId = $stepId;
        if ($resourceGroupId != "") {
            $rendRequest->resourceGroupId = $resourceGroupId;
        }
        else {
          Yii::info("При создании услуги для случая $medicalCaseId  не найден ресурс!"
                        , 'egis_error');  
        }
        try {
            $rendResponse = Yii::$app->medservices->send($rendRequest);
            Yii::info("Создали оказаннную услугу {" . $rendResponse->id . "} для пациента $patientUid, "
                    . "случая $medicalCaseId и посещения $stepId"
                    , 'egis_pass');
            return $rendResponse->id;
        } catch (\Exception $ex) {
            if ($ex->getMessage() == 'Could not connect to host') {
                if ($connectError) {
                    Yii::info("При создании услуги для пациента $patientUid,"
                            . "случая $medicalCaseId и посещения $stepId  возникла "
                            . "ошибка:" . $ex->getMessage(), 'egis_error');
                    return null;
                } else {
                    sleep(30);
                    return EgisExportController::createServiceRend($patientUid, $medicalCaseId, $stepId, $dprm, $serviceId, $resourceGroupId, $orgId, $fundingSourceTypeId, true);
                }
            } else {
                Yii::info("При создании услуги для пациента $patientUid,"
                        . "случая $medicalCaseId и посещения $stepId  возникла "
                        . "ошибка:" . $ex->getMessage(), 'egis_error');
                return null;
            }
        }
    }

    /**
     * Проверяет наличие случая в ЕГИЗ по номеру и дате вызова
     * @param type $uid Номер вызова
     * @param type $dprm Дата вызова
     * @param type $orgId Ид организации
     * @return boolean
     */
    public static function caseExists($uid, $dprm, $orgId = 2052479) {
        $caseReq = new searchCase();
        $caseReq->uid = $uid;
        $caseReq->openedFromDate = $dprm;
        $caseReq->medicalOrganizationId = 2052479;
        $caseReq->caseTypeId = 3;
        $caseResp = Yii::$app->cases->send($caseReq);
        $id = 0;
        if (@gettype($caseResp->ids) == "string")
            return true;
        $caseReq->caseTypeId = 7;
        $caseResp = Yii::$app->cases->send($caseReq);
        if (@gettype($caseResp->ids) == "string")
            return true;
        return false;
    }

    /**
     * ПЕРЕКОММЕНТИРОВАТЬ!
     * поломка здесь или в следующей функции ломает выгрузку в ЕГИЗ, 
     * данные от АДИС будут приходить в пустую
     * @param \app\controllers\adisIn\TransferCallType $exportcall
     */
    public static function loadCalls($exportcall) {
        switch ($exportcall->ext_system_code) {
            case 9115:
                EgisExportController::exportCalls($exportcall);
                break;
            case 9999:
                $ngod = $exportcall->call->global_call_number;
                $dprm = $exportcall->call->call_time;
                $dprm = EgisExportController::converCallTime($dprm);
                $dprm = date("Y-m-d", strtotime($dprm));
                if (EgisExportController::caseExists($ngod, $dprm)) {
                    Yii::info("Полученный вызов уже есть в ЕГИЗ!"
                            . " Ничего не делаю!", 'egis_pass');
                    break;
                } else {
                    EgisExportController::exportCalls($exportcall);
                    break;
                }

            default:
                EgisExportController::exportCalls($exportcall);
                break;
        }
    }

    /**
     * Отправка вызовов в ЕГИЗ
     * @param \app\controllers\adisIn\TransferCallType $exportcall
     */
    public static function exportCalls($exportcall) {
        try {
            /**
             * ВАЖНО! Полученнные данные лучше проверять на @gettype =="NULL"
             * Логика:
             * 1. Проверить результат вызова, если
             * он должен попадать в выгрузку -> продолжить работу с ним,
             * в противном случае просто отбросить
             * 2. Проверить от кого прилетел вызов 
             * От АДИСА: Стандартная логика
             * От самого модуля интеграции: Проверить наличие вызова в ЕГИЗ,
             * если его нет стандартная логика, если есть то мы его отбрасываем
             * 3. Найти пациента
             * ...
             * 
             */
            //проверяем, что вызов не обслужен ЦРБ
            if ($exportcall->brigade->substation_control != 7) {
                if (@gettype($exportcall->call->result) != "NULL")
                    $result = $exportcall->call->result;
                else
                    $result = "";
                $exlResult = ['04', '06', '07', '4', '6', '7', '10', '17', '18', '20', '90', '91', '92', '93', '94', '95',
                    '41', '42', '96', '97', '99', '9<', '9>', '9?', '9[', '9]', '9~', '>>', ''];
                Yii::info("На выгрузку получен вызов " .
                        $exportcall->call->global_call_number, 'egis_pass');

                if (@gettype($exportcall->call->call_time) != "NULL")
                    $dprm = $exportcall->call->call_time;
                else
                    $dprm = "";
                $tprm = EgisExportController::converCallTime($dprm);
                $dprmCheck = date("Y-m-d", strtotime($tprm));
                //проверяем, что вызов не приходит к нам повторно
                $loadedCall = SavedCalls::findAll(['ngod' => $exportcall->call->global_call_number,
                            'dprm' => $dprmCheck]);
                if (count($loadedCall) == 0) {
                    //проверяем, что у нас подходящий результат вызова
                    if (!in_array($result, $exlResult)) {


                        $call_number = $exportcall->call->global_call_number;


                        //поправь остальные
                        if (@gettype($exportcall->call->patient->surname) != "NULL")
                            $surname = $exportcall->call->patient->surname;
                        else
                            $surname = "";

                        if (@gettype($exportcall->call->patient->name) != "NULL")
                            $name = $exportcall->call->patient->name;
                        else
                            $name = "";

                        if (@gettype($exportcall->call->patient->patronymic) != "NULL")
                            $patrName = $exportcall->call->patient->patronymic;
                        else
                            $patrName = "";

                        if (@gettype($exportcall->call->patient->snils) != "NULL")
                            $snils = $exportcall->call->patient->snils;
                        else
                            $snils = "";

                        if (@gettype($exportcall->call->patient->birthday) != "NULL")
                            $birthday = $exportcall->call->patient->birthday;
                        else
                            $birthday = "";

                        if (@gettype($exportcall->call->patient->insurance) != "NULL")
                            $insurance = $exportcall->call->patient->insurance;
                        else
                            $insurance = "";

                        if (@gettype($exportcall->call->patient->document_type) != "NULL")
                            $typeDoc = $exportcall->call->patient->document_type;
                        else
                            $typeDoc = "";

                        if (@gettype($exportcall->call->patient->document_number) != "NULL")
                            $nomDoc = $exportcall->call->patient->document_number;
                        else
                            $nomDoc = "";

                        if (@gettype($exportcall->call->patient->info) != "NULL")
                            $info = $exportcall->call->patient->info;
                        else
                            $info = "";

                        if (@gettype($exportcall->call->patient->gender) != "NULL")
                            $gender = $exportcall->call->patient->gender;
                        else
                            $gender = "";
//Сначала ищем пациента по СНИЛС
                        if ($surname != "") {
                            $tempPatientId = EgisExportController::findPatientbySnils($surname, $name, $patrName, $birthday, $snils);
//Если не найден, ...
                            switch (@gettype($tempPatientId)) {
                                case "NULL":
                                    $patientId = "";
                                    break;
                                case "array":
                                    Yii::info("Найдено большего одного физ.лица!", 'egis_pass');
                                    $patientId = "";
//findPatientbyOtherDocument
                                    break;
                                default :
                                    $patientId = $tempPatientId;
                            }
                        } else {
                            $patientId = "";
                        }
//если есть id пациента идем дальше, в противном случае
//ничего не отправляем
                        if ($patientId != "") {
//забираем остальные данные
//$ds1 диагноз, $place место вызова, $dprm дата/время приема, 
                            if (@gettype($exportcall->call->diagnosis) != "NULL")
                                $ds1 = $exportcall->call->diagnosis;
                            else
                                $ds1 = "";
                            if (@gettype($exportcall->call->place) != "NULL")
                                $place = $exportcall->call->place;
                            else
                                $place = "";
                            if (@gettype($exportcall->brigade->senior_personnel->code) != "NULL")
                                $tabAdis = $exportcall->brigade->senior_personnel->code;
                            else
                                $tabAdis = "";



                            //проверим тип вызова(неотложка/экстренный)
                            $typeCase = EgisExportController::getTypeCase($tprm, $ds1, $place, $result);
                            //Приведем результат вызова к результату ЕГИЗ
                            $egisResult = EgisExportController::getEgisResults($result);
                            //Приведем исход вызова к исходу ЕГИЗ
                            //Мед. средств может и не быть!                        
                            if (@gettype($exportcall->medical_supplies) != "NULL")
                                $supllies = $exportcall->medical_supplies;
                            else
                                $supllies = [];
                            $code = "";
                            foreach ($supllies as $suplie) {
                                $code = $suplie->code;
                                if ($code[0] . $code[1] . $code[2] == 800)
                                    break;
                            }
                            $egisOut = EgisExportController::getEgisOut($code);
                            //привели диагноз в нужный вид
                            $diagnosis = EgisExportController::getDiagnos($ds1);
                            //отказ от осмотра приводим к диагнозу Z53.2
                            if ($egisResult == 13) {
                                Yii::info("<b>Отказ от осмотра привели к диагнозу Z53.2</b>", 'egis_pass');
                                $diagnosis["diagnosId"] = 13659;
                            }

                            //не найден на месте приводим к диагнозу Z53.8
                            if ($egisResult == 39) {
                                Yii::info("<b>Не найден на месте привели к диагнозу Z53.8</b>", 'egis_pass');
                                $diagnosis["diagnosId"] = 13656;
                            }

                            $dprm = date("Y-m-d", strtotime($tprm));
                            $resourceGroupId = EgisExportController::findResource($tabAdis);
                            $serviceTypeId = EgisExportController::findService($tabAdis);
                            $caseId = EgisExportController::createCase($patientId, $call_number, $typeCase["caseTypeId"], $typeCase["initGoalId"], "$dprm", $typeCase["careProvidingFormId"]);
                            $serviceRendId = null;
                            if (@gettype($caseId) != "NULL") {
                                if ($caseId != "connect_error") {
                                    $savedCall = new SavedCalls();
                                    $savedCall->ngod = $call_number;
                                    $savedCall->dprm = $dprm;
                                    $savedCall->tprm = $tprm;
                                    $savedCall->patientId = $patientId;
                                    $savedCall->caseId = $caseId;
                                    $savedCall->serviceId = $serviceTypeId;
                                    if (@gettype($resourceGroupId) != "NULL") {
                                        $savedCall->resourceId = $resourceGroupId;
                                        $visiId = EgisExportController::createVisit($caseId, $diagnosis["stageId"], $diagnosis["typeId"], $diagnosis["diagnosId"], "$dprm", "$tprm", $typeCase["initGoalId"], 2, 121, $egisOut, $egisResult, $resourceGroupId);
                                    } else {
                                        $visiId = EgisExportController::createVisit($caseId, $diagnosis["stageId"], $diagnosis["typeId"], $diagnosis["diagnosId"], "$dprm", "$tprm", $typeCase["initGoalId"], 2, 121, $egisOut, $egisResult);
                                    }
                                    if (@gettype($visiId) != "NULL") {
                                        $savedCall->visitId = $visiId;
                                        if (@gettype($resourceGroupId) != "NULL") {
                                            $serviceRendId = EgisExportController::createServiceRend($patientId, $caseId, $visiId, $dprm, $serviceTypeId, $resourceGroupId);
                                        } else {
                                            $serviceRendId = EgisExportController::createServiceRend($patientId, $caseId, $visiId, $dprm, $serviceTypeId);
                                        }
                                    }
                                    if (@gettype($serviceRendId) != "NULL") {
                                        $savedCall->serviceRendId = $serviceRendId;
                                    }
                                    $savedCall->dateSync = date("Y-m-d H:i:s");
                                    if ($exportcall->ext_system_code == 9115) {
                                        $savedCall->isFromAdis = true;
                                    } else
                                        $savedCall->isFromAdis = false;
                                    $savedCall->save();
                                } else {
                                    //вот тут сохранение вызова на повторную попытку
                                }
                            }
                        } else {
                            Yii::info("Вызов отброшен, не найден пациент!", 'egis_pass');
                        }
                    } else {
                        Yii::info("На выгрузку получен вызов " .
                                $exportcall->call->global_call_number . " вызов "
                                . "отброшен, неподходящий результат вызова: $result", 'egis_pass');
                    }
                } else {
                    Yii::info("Вызов " .
                            $exportcall->call->global_call_number . " "
                            . "отброшен, он уже был получен от сервера АДИС ранее", 'egis_pass');
                }
            } else {
                Yii::info("Вызов " .
                        $exportcall->call->global_call_number . " "
                        . "отброшен, он принадлежит ЦРБ", 'egis_pass');
            }
        } catch (\Exception $ex) {
            Yii::info("Исключение прискакало:" . $ex->getMessage(), 'egis_error');
        }
    }

}
