<?php
namespace app\components\patient\request;
 
class searchPatient extends BaseRequest
{
    public $surname;
    public $name;
    public $patrName;
    public $birthDate;
    public $gender;
    public $deathDate;
    public $searchCode;
    public $searchDocument;
    public $docTypeId;
    public $docSeries;
    public $docNumber;
    public $page;
    public $socialGroup;
    public $citizenship;
    public $regClinicId;
    public $regClinicCode;
    public $regClinicCodeType;
    public $modifiedSince;


    public function rules()
    {
        return [
            [['surname', 'name'], 'required'],
            //[['birthDate','docTypeId','patrName','docSeries','docNumber'],'safe']
        ];
    }
}