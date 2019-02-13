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

class PatientType { 
     
    /**
     * @var string
     * @soap
     */
    public $surname;
    
    /**
     * @var string
     * @soap
     */
    public $name;
        
    /**
     * @var string
     * @soap
     */
    public $patronymic;
    
    /**
     * @var string 
     * @soap
     */
    public $age;
    
    /**
     * @var string 
     * @soap
     */
    public $age_type;
  
        /**
     * @var string 
     * @soap
     */
    public $gender;
    
    /**
     * @var string 
     * @soap
     */
    public $birthday;
    
    /**
     * @var string 
     * @soap
     */
    public $snils;    
    
    /**
     * @var string 
     * @soap
     */
    public $info;  
    
        /**
     * @var string 
     * @soap
     */
    public $document_type;
   
    /**
     * @var string 
     * @soap
     */
    public $document_number;    
    
    /**
     * @var string 
     * @soap
     */
    public $insurance;  
    
        /**
     * @var string 
     * @soap
     */
    public $insurance_company;  
      
   
}