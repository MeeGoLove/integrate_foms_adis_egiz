<?php
namespace app\components\resources\request;
 
class getLocation extends BaseRequest
{

/*
 * возвращает массив объектов location
 */
 public $location;

    public function rules()
    {
        return [
               ['location','required'],               
        ];
    }
}