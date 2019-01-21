<?php
namespace app\components\patient\request;
 
class getPatient extends BaseRequest
{
    public $getPatientRequest;



    public function rules()
    {
        return [
            [['getPatientRequest'], 'required'],
            //[['birthDate','docTypeId','patrName','docSeries','docNumber'],'safe']
        ];
    }
}