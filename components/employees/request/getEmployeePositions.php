<?php

namespace app\components\employees\request;

/**
 * Возвращает должности сотрудника
 * 
 * employee идентификатор сотрудника
 * 
 * returns employeePosition[]
 */

class getEmployeePositions extends BaseRequest {

    public $employee;

    public function rules() {
        return [
            ['employee', 'required'],
        ];
    }

}
