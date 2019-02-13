<?php
namespace app\components\individuals\request;
 
class searchIndividual extends BaseRequest
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
 
    public function rules()
    {
        return [
            ['surname', 'required'],
            
        ];
    }
}