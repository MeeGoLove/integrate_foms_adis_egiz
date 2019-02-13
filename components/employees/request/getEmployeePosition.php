<?php

namespace app\components\employees\request;

/*
 * Возвращает сотрудника на данной должности
 * id Идентификатор позиции должности
 * 
 * Возвращает объект employeePosition с полями:
 * employee
 * position
 * fromDate
 * rate
 * positionType
 * employmentType
 * hiringType
 */

class getEmployeePosition extends BaseRequest {

    public $id;

    public function rules() {
        return [
            ['id', 'required'],
        ];
    }

}
