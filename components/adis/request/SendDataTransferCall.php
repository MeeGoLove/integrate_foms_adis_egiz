<?php

namespace app\components\adis\request;

/**
 * Модель для ручной передачи вызовов
 */
class SendDataTransferCall extends BaseRequest {

    /**
     *
     * @var type Ид внешней системы
     */
    public $ext_system_code;

    /**
     *
     * @var call Данные вызова
     */
    public $call;

    /**
     *
     * @var medical_supplies[] Массив кодов манипуляций
     */
    public $medical_supplies;

    /**
     *
     * @var brigade Бригада
     */
    public $brigade;

    public function rules() {
        return [
            [],
        ];
    }

}

class call {

    public $global_call_number;
    public $result;
    public $call_time;
    public $diagnosis;
    public $place;

    /**
     *
     * @var patient Данные пациента
     */
    public $patient;

}

class patient {

    public $surname;
    public $name;
    public $patronymic;
    public $snils;
    public $birthday;
    public $insurance;
    public $document_type;
    public $document_number;
    public $info;
    public $gender;

}

class medical_supplies {

    public $code;

}

class brigade {

    /**
     *
     * @var senior_personnel Старший бригады
     */
    public $senior_personnel;

}

class senior_personnel {

    public $code;

}
