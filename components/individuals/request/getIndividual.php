<?php
namespace app\components\individuals\request;
 /*
  * возвращает объект с полями
  * surname фамилия
  * name имя
  * patrName отчество
  * birthDate дата рождения
  * gender пол 1 - мужской, 2 - женский
  */
class getIndividual extends BaseRequest
{

    public $param;
    public function rules()
    {
        return [
               ['param','required'],               
        ];
    }
}