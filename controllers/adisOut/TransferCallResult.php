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

class TransferCallResult { 

     /**
     * @var string
     * @soap
     */
    public $adis_id;

    /**
     * @var string
     * @soap
     */
    public $send_result;

    /**
     * @var \app\controllers\adisOut\OperationInfo
     * @soap
     */
    public $send_info;
    
}