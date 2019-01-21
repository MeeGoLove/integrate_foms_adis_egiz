<?php
namespace app\components\individuals\request;
 /*
  * возвращает объект с полями
  * individualUid ид как ни странно из объекта employee
  * type тип документа
  *                     19 - СНИЛС
  *                     13 - паспорт
  *                     26 - полис ОМС
  * 
  * issueDate дата выдачи
  * issuer кто выдал СМО
  * issuerText кто выдал описание
  * series серия документа
  * number номер документа
  * patrName
  * name
  * active типа активный документ или нет?
  */
class getDocument extends BaseRequest
{

    public $param;
    public function rules()
    {
        return [
               ['param','required'],               
        ];
    }
}