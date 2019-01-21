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

class AddressTypePatient { 
       /**
     * @var string
     * @soap
     */
    public $region;
    
        
    /**
     * @var string
     * @soap
     */
    public $area;
    
    /**
     * @var string 
     * @soap
     */
    public $locality;
    
    /**
     * @var string 
     * @soap
     */
    public $street;
  
        /**
     * @var string 
     * @soap
     */
    public $house;
    
    /**
     * @var string 
     * @soap
     */
    public $building;
    
    /**
     * @var string 
     * @soap
     */
    public $porch;
   
    /**
     * @var string 
     * @soap
     */
    public $porch_сode;    
    
    /**
     * @var string 
     * @soap
     */
    public $longitude;  
    
        /**
     * @var string 
     * @soap
     */
    public $latitude;  
    
            /**
     * @var string 
     * @soap
     */
    public $info;  
   
   
}