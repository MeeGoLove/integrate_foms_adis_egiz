<?php
namespace app\components\resources\request;
 
class getLocations extends BaseRequest
{

/*
 * возвращает массив объектов location
 * clinic Идентификатор организации
 * остальные параметры потом
 */
 public $clinic;
 public $service;
 public $districtId;
 public $departmentId;
    public function rules()
    {
        return [
               ['clinic','required'],               
        ];
    }
}