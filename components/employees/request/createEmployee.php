<?php

namespace app\components\employees\request;

/**
 * создает сотрудника в организации
 * organization идентификатор организации
 */
class createEmployee extends BaseRequest {

    public $organization;

    /**
     *
     * @var employee 
     */
    public $employee;

    public function rules() {
        return [
            ['id', 'required'],
        ];
    }

}

/**
 * individual идентификатор физлица
 * number табельный номер
 */
class employee {

    public $individual;
    public $number;

}
