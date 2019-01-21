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

class MedicalSuppliesType { 
       /**
     * @var string
     * @soap
     */
    public $code;
    
    /**
     * @var string
     * @soap
     */
    public $name;
        
    /**
     * @var string
     * @soap
     */
    public $measure;
    
    /**
     * @var int 
     * @soap
     */
    public $quantity;
    
    /**
     * @var string 
     * @soap
     */
    //public $info;
 
}