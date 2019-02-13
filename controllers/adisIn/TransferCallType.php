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
class TransferCallType {

    /**
     * @var string
     * @soap
     */
    public $federation_subject_code;

    /**
     * @var string
     * @soap
     */
    public $ext_system_code;

    /**
     * @var string
     * @soap
     */
    public $code_mo;

    /**
     * @var string
     * @soap
     */
    public $destination_arm;

    /**
     * @var int
     * @soap
     */
    public $is_voice;

    /**
     * @var \app\controllers\adisIn\CallDataType
     * @soap
     */
    public $call;

    /**
     * @var \app\controllers\adisIn\patient
     * @soap
     */
    public $patient;
        
    /**
     * @var \app\controllers\adisIn\BrigadeDataType
     * @soap
     */
    public $brigade;
    

    /**
     * @var \app\controllers\adisIn\MedicalSuppliesType[]
     * @soap
     */
    public $medical_supplies;
    
    
    /**
     * @var string
     * @soap
     */
    public $info;
        
    /**
     * @var \app\controllers\adisIn\StaffData
     * @soap
     */
    public $senior_doctor;    
    /**
     * @var \app\controllers\adisIn\StaffData
     * @soap
     */
    public $senior_dispatcher;
    
    /**
     * @var \app\controllers\adisIn\OperationInfo
     * @soap
     */
    public $send_info;
}
