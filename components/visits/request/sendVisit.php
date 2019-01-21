<?php

namespace app\components\visits\request;

/**
 * Создает посещение
 * 
 */
class sendVisit extends BaseRequest {

    /**
     *
     * @var type Идентификатор случая 
     */
    public $caseId;

    /**
     *
     * @var type Дата поступления
     */
    public $admissionDate;

    /**
     *
     * @var type Цель обращения
     * справочник mc_case_init_goal
     */
    public $goalId;

    /**
     *
     * @var type Вид посещения: Заболевание, Профилактика/патронаж.:
     * справочник plc_visit_type
     */
    public $typeId;

    /**
     *
     * @var type Место посещения
     * справочник plc_visit_place
     */
    public $placeId;

    /**
     *
     * @var type Медицинский профиль     * 
     * справочник md_profile
     */
    public $profileId;

    /**
     *
     * @var type Время выбытия
     */
    public $outcomeTime;

    /**
     *
     * @var type Дата выбытия
     */
    public $outcomeDate;

    /**
     *
     * @var type Результат лечения основного заболевания по случаю 
     * (выздоровление, улучшение и др.)
     * справочник mc_step_care_result
     */
    public $deseaseResultId;

    /**
     *
     * @var type Результат обращения, т.е. чем закончился случай обслуживания 
     * на данном шаге (выписан, переведен и др.)
     * справочник mc_step_result
     */
    public $visitResultId;

    /**
     *
     * @var type Идентификатор ресурса
     */
    public $resourceGroupId;

    /**
     *
     * @var diagnoses[] Диагнозы
     */
    public $diagnoses;

    public function rules() {
        return [
        ];
    }

}

class diagnoses {

    /**
     *
     * @var type этапов установки диагноза: направительный, предварительный 
     * (первичный, приемного отделения), клинический, заключительный 
     * (клинический заключительный), патологоанатомический
     * справочник mc_stage
     */
    public $stageId;

    /**
     *
     * @var type Тип диагноза
     * справочник md_diagnosis
     */
    public $typeId;

    /**
     *
     * @var type Диагноз
     * справочник md_diagnosis
     */
    public $diagnosId;

    /**
     *
     * @var type Дата установки диагноза
     */
    public $establishmentDate;

    /**
     *
     * @var bool признак основного диагноза
     */
    public $main;

}
