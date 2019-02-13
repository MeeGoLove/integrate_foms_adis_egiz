<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\controllers\adisIn;

/**
 * Description of Call
 *
 * @author Сергей
 */
class CallDataType {

    /**
     * @var string
     * @soap
     */
    public $ext_id;

    /**
     * @var string
     * @soap
     */
    public $ext_call_number;

    /**
     * @var string
     * @soap
     */
    public $ext_arm;

    /**
     * @var string 
     * @soap
     */
    public $call_number;

    /**
     * @var string 
     * @soap
     */
    public $global_call_number;

    /**
     * @var string 
     * @soap
     */
    public $substation;

    /**
     * @var string 
     * @soap
     */
    public $priority;

    /**
     * @var string 
     * @soap
     */
    public $call_type;

    /**
     * @var string 
     * @soap
     */
    public $desc_call_type;

    /**
     * @var \app\controllers\adisIn\AddressType 
     * @soap
     */
    public $call_address;

    /**
     * @var string
     * @soap
     */
    public $reason;

    /**
     * @var string
     * @soap
     */
    public $desc_reason;

    /**
     * @var \app\controllers\adisIn\PatientType
     * @soap
     */
    public $patient;

    /**
     * @var string
     * @soap
     */
    public $caller;

    /**
     * @var string
     * @soap
     */
    public $phone;

    /**
     * @var string
     * @soap
     */
    public $place;

    /**
     * @var string
     * @soap
     */
    public $info;

    /**
     * @var string
     * @soap
     */
    public $profil;

    /**
     * @var string
     * @soap
     */
    public $call_time;

    /**
     * @var string
     * @soap
     */
    public $line;

    /**
     * @var string
     * @soap
     */
    public $result;

    /**
     * @var string
     * @soap
     */
    public $desc_result;

    /**
     * @var string
     * @soap
     */
    public $region_hospital;

    /**
     * @var string
     * @soap
     */
    public $hospital_name;

    /**
     * @var string
     * @soap
     */
    public $diagnosis;

    /**
     * @var string
     * @soap
     */
    public $desc_diagnosis;

    /**
     * @var string
     * @soap
     */
    public $additional_diagnosis;

    /**
     * @var string
     * @soap
     */
    public $desc_additional_diagnosis;

    /**
     * @var string
     * @soap
     */
    public $trauma_type;

    /**
     * @var bool
     * @soap
     */
    public $alcohol_intoxication;

    /**
     * @var bool
     * @soap
     */
    public $drug_intoxication;

    /**
     * @var \app\controllers\adisIn\StaffData
     * @soap
     */
    public $reception_dispatcher;

    /**
     * @var \app\controllers\adisIn\StaffData
     * @soap
     */
    public $transfer_dispatcher;
    
    /**
     * @var \app\controllers\adisIn\StaffData
     * @soap
     */
    public $destination_dispatcher;
        
    /**
     * @var \app\controllers\adisIn\StaffData
     * @soap
     */
    public $close_dispatcher;
    
    /**
     * @var string
     * @soap
     */
    public $received_by;
        
    /**
     * @var string
     * @soap
     */
    public $transfer_time;
        
    /**
     * @var string
     * @soap
     */
    public $departure_time;
        
    /**
     * @var string
     * @soap
     */
    public $arrival_time;
        
    /**
     * @var string
     * @soap
     */
    public $arrival_hospital_time;
        
    /**
     * @var string
     * @soap
     */
    public $execution_time;
        
    /**
     * @var string
     * @soap
     */
    public $return_time;
            
    /**
     * @var string
     * @soap
     */
    public $close_time;
            
    /**
     * @var string
     * @soap
     */
    public $mileage;
            
    /**
     * @var string
     * @soap
     */
    public $policlinic;
            
    /**
     * @var bool
     * @soap
     */
    public $active;
    
    /**
     * @var \app\controllers\adisIn\NoticeData[]
     * @soap
     */
    public $notice;
                
    /**
     * @var string
     * @soap
     */
    public $diagnosis_hospital;
                
    /**
     * @var string
     * @soap
     */
    public $expert_evaluation;
                
    /**
     * @var string
     * @soap
     */
    public $state;
/*
< xsi:type="xsd:string">код диагноза стационара</diagnosis_hospital>
 * < xsi:type="xsd:string">экспертная оценка</expert_evaluation>
 * < xsi:type="xsd:string">00 решение</state>
  */
}
