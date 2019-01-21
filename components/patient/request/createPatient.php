<?php
namespace app\components\patient\request;
 
class createPatient extends BaseRequest
{
    /**
     *
     * @var string
     */
    public $patientId;
    /**
     *
     * @var patientData
     */
    public $patientData;
    public function rules()
    {
        return [
            [['$patientId'], 'required'],
            
        ];
    }
}

class patientData
{
    /**
     *
     * @var bool
     */
    public $vip;
}