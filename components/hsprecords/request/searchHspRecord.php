<?php
namespace app\components\hsprecords\request;
 
class searchHspRecord extends BaseRequest
{


public $medicalOrganizationId;
public $patientUid;
public $medicalCaseId;
public $openedFromDate;
public $openedToDate;
public $closedFromDate;
public $closedToDate;
public $deseaseResultId;
public $specialistId;
public $serviceId;
public $admissionDate;
public $admissionTime;


    public function rules()
    {
        return [
            [['patientUid'], 'required'],
            //[['birthDate','docTypeId','patrName','docSeries','docNumber'],'safe']
        ];
    }
}