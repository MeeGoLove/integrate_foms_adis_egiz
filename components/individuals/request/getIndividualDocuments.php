<?php
namespace app\components\individuals\request;
 /*
  * возвращает объект с полями
  * document  id документа
  * 
  */
class getIndividualDocuments extends BaseRequest
{

    public $param;
    public function rules()
    {
        return [
               ['param','required'],               
        ];
    }
}