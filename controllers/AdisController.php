<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\controllers;

use \yii\base\Controller;
use app\controllers\EgisExportController;

/**
 * Description of ApiController
 *
 * @author Сергей
 */
class AdisController extends Controller {
    //put your code here

    /**
     * @inheritdoc
     */
    public function actions() {

        return array(
            'calls' => array(
                'class' => 'mongosoft\soapserver\Action',
                'serviceOptions' => [
                    'disableWsdlMode' => false, 'soapVersion' => '1.1',
                ]
            ),
        ); /* старый вариант
          return [
          'hello' => 'mongosoft\soapserver\Action',
          ]; */
    }

    /**
     * @param \app\controllers\adisIn\TransferCallType $TransferCallType
     * @return \app\controllers\adisOut\TransferCallResult
     * @soap
     */
    public function SendDataTransferCall($TransferCallType) {
        //полученные данные от сервера АДИС хранятся в объекте $TransferCallType 
        //тут идет ответ сервера АДИС
        
        //пишем в лог от кого получили данные
        switch ($TransferCallType->ext_system_code) {
            case 9115:
                \Yii::info("От сервера АДИС получен вызов " . $TransferCallType->
                        call->global_call_number, 'egis_pass');
                break;
            case 9999:
                \Yii::info("От внутренней системы получен вызов " . $TransferCallType->
                        call->global_call_number, 'egis_pass');
                break;
            default :
                \Yii::info("От хрен пойми какой системы получен вызов " . $TransferCallType->
                        call->global_call_number, 'egis_pass');
                break;
        }

        try {
            EgisExportController::loadCalls($TransferCallType);
        } catch (\Exception $ex) {
            \Yii::info("Возникла ошибка в контроллере Adis: ".$ex->getMessage(), 'egis_error');
        } 
        //Даже если у нас возникла ошибка, мы все равно должны ответить внешней 
        //системе, что мы получили от неё данные)))
        finally {


            $response = new adisOut\TransferCallResult();
            //переменная adis_id взята наобум, это неверно
            $response->adis_id = 44;
            $response->send_result = "Ok";
            $response->send_info = new adisOut\OperationInfo();
            $response->send_info->info_state = "ПЕРЕДАЧА ДАННЫХ";
            $response->send_info->lpu_name = "560109";
            $response->send_info->info_time = date("Y-m-d", time() + 5 * 60 * 60) . "T" . date("H:i:s+05:00", time() + 5 * 60 * 60);
            return $response;
        }
    }

}
