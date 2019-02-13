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
class BrigadeDataType {
    /**
     * @var string
     * @soap
     */
    public $brigade_number;

    /**
     * @var string
     * @soap
     */
    public $brigade_smp;

    /**
     * @var string
     * @soap
     */
    public $substation_based;

    /**
     * @var string 
     * @soap
     */
    public $substation_control;

    /**
     * @var string 
     * @soap
     */
    public $profile;

    /**
     * @var string 
     * @soap
     */
    //public $desc_profile;

    /**
     * @var string 
     * @soap
     */
    public $car_number;

    /**
     * @var string 
     * @soap
     */
    public $radio_code;

    /**
     * @var \app\controllers\adisIn\StaffData
     * @soap
     */
    public $senior_personnel;

    /**
     * @var \app\controllers\adisIn\StaffData 
     * @soap
     */
    public $first_assistant;

    /**
     * @var  \app\controllers\adisIn\StaffData
     * @soap
     */
    public $second_assistant;

    /**
     * @var \app\controllers\adisIn\StaffData 
     * @soap
     */
    public $driver;

    /**
     * @var string 
     * @soap
     */
    //public $shift_number;

    /**
     * @var \app\controllers\adisIn\AddressType
     * @soap
     */
    //public $address;
    
    
    /**
     * @var string 
     * @soap
     */
    //public $info;
      
    /**
     * @var string 
     * @soap
     */
    //public $longitude;
          
    /**
     * @var string 
     * @soap
     */
    //public $latitude;      
    
    /**
     * @var string 
     * @soap
     */
    //public $tracker_id;      
   
    /**
     * @var string 
     * @soap
     */
    //public $state;      
    
    /**
     * @var string 
     * @soap
     */
    //public $start_event_time;      
    
    /**
     * @var string 
     * @soap
     */
    //public $finish_event_time;      
    
/*
    <xs:element name = "" type = "xs:string" minOccurs = "1" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "1" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "1" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "1" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "1" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "tns:StaffData" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "tns:StaffData" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "tns:StaffData" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "tns:StaffData" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "tns:AddressType" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "tns:StateType" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:dateTime" minOccurs = "0" maxOccurs = "1">
    </xs:element><xs:element name = "" type = "xs:dateTime" minOccurs = "0" maxOccurs = "1">
    </xs:element>*/
}
