<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\controllers\adisOut;

/**
 * Description of Call
 *
 * @author Сергей
 */
class OperationInfo {

    /**
     * @var string
     * @soap
     */
    public $lpu_name;

    /**
     * @var string
     * @soap
     */
    //public $lpu_short_name;

    /**
     * @var string
     * @soap
     */
    //public $branch_name;
    
    /**
     * @var string
     * @soap
     */
    //public $branch_address;

    /**
     * @var string
     * @soap
     */
    //public $registry_phones;

    /**
     * @var string
     * @soap
     */
    //public $registrar;
    
    /**
     * @var string
     * @soap
     */
    public $info_time;
        
    /**
     * @var string
     * @soap
     */
    public $info_state;
        
    /**
     * @var string
     * @soap
     */
    //public $info;


}
/*
<xs:element name = "lpu_name" type = "xs:string" minOccurs = "0" maxOccurs = "1">
</xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
</xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
</xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
</xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
</xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
</xs:element><xs:element name = "" type = "xs:dateTime" minOccurs = "1" maxOccurs = "1">
</xs:element><xs:element name = "" type = "tns:OperationStateType" minOccurs = "1" maxOccurs = "1">
</xs:element><xs:element name = "" type = "xs:string" minOccurs = "0" maxOccurs = "1">
</xs:element>
  