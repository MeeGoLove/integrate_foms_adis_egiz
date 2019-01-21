<?php

namespace app\components\individuals\request;

/**
 * создает физическое лицо
 * 
 * возвращает объект с полем employeePosition - идентификатор позиции сотрудника
 */

class createIndividual extends BaseRequest {

    /**
     *
     * @var employeePosition
     */
    public $employeePosition;

    public function rules() {
        return [
            ['employeePosition', 'required'],
        ];
    }

}


    /**
     * employee идентификатор сотрудника
     * position идентификатор должности из  справочника
     * fromDate с какой даты
     * rate ставка 
     * positionType     1
     * employmentType       1
     * hiringType       0
     */
class employeePosition {

    public $employee;
    public $position;
    public $fromDate;
    public $rate;
    public $positionType;
    public $employmentType;
    public $hiringType;

}
