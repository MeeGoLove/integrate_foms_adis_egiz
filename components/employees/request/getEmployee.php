<?php

namespace app\components\employees\request;

/*
 * возвращает объект employee
 * individual id физ-лица
 * number табельный номер
 * note примечание?
 * dismissed уволен?
 */

class getEmployee extends BaseRequest {

    public $id;

    public function rules() {
        return [
            ['id', 'required'],
        ];
    }

}
