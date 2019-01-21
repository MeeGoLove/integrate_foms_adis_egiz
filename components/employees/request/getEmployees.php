<?php
namespace app\components\employees\request;
/*
 * Возвращает сотрудников организации
 * organization Идентификатор организации
 */ 
class getEmployees extends BaseRequest
{


 public $organization;
    public function rules()
    {
        return [
               ['organization','required'],               
        ];
    }
}