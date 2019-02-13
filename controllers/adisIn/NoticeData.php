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

class NoticeData { 
     
    /**
     * @var string
     * @soap
     */
    public $message;
    
    /**
     * @var string
     * @soap
     */
    public $transfer_time;
        
    /**
     * @var string
     * @soap
     */
    public $name;
}