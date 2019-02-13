<?php

namespace app\components\medservices\request;

/**
 * Создает услугу
 * 
 */
class sendServiceRend extends BaseRequest {

    /**
     *
     * @var type Идентификатор случая
     */
    public $medicalCaseId;

    /**
     *
     * @var bool Признак оказания 
     */
    public $isRendered;

    /**
     *
     * @var type Идентификатор посещения/ЗОГ
     */
    public $stepId;

    /**
     *
     * @var type Источник финансирования
     */
    public $fundingSourceTypeId;

    /**
     *
     * @var type Признак срочности 
     */
    public $isUrgent;

    /**
     *
     * @var type Идентификатор рпциента
     */
    public $patientUid;

    /**
     *
     * @var type Идентификатор организации
     */
    public $orgId;

    /**
     *
     * @var type Дата планирование
     */
    public $plannedDate;

    /**
     *
     * @var type Дата начала оказания
     */
    public $dateFrom;

    /**
     *
     * @var type Количество оказанных услуг
     */
    public $quantity;

    /**
     *
     * @var type идентификатор вида услуги — 
     * для получения списка услуг использовать 
     * метод getServices сервиса services-ws/services 
     */
    public $serviceId;

    public function rules() {
        return [
        ];
    }

}
