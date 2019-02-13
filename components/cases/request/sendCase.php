<?php

namespace app\components\cases\request;

/**
 * Создает случай обслуживания пациента
 */
class sendCase extends BaseRequest {

    /**
     *
     * @var string Идентификатор пациента
     */
    public $patientUid;

    /**
     *
     * @var string Номер медицинской карты 
     */
    public $uid;

    /**
     *
     * @var string Идентификатор медицинской организации 
     */
    public $medicalOrganizationId;

    /**
     *
     * @var string Вид случая
     */
    public $caseTypeId;

    /**
     *
     * @var string Вид медицинской помощи 
     */
    public $careLevelId;

    /**
     *
     * @var string Вид финансирования 
     */
    public $fundingSourceTypeId;

    /**
     *
     * @var string Условия оказания
     */
    public $careRegimenId;

    /**
     *
     * @var string Цель первичного обращения 
     */
    public $initGoalId;

    /**
     *
     * @var string Дата вызова
     */
    public $createdDate;

    /**
     *
     * @var string Форма оказания медицинской помощи 
     */
    public $careProvidingFormId;

    public function rules() {
        return [
            ['patientUid', 'required'],
        ];
    }

}