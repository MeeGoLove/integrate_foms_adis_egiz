<?php

namespace app\components\individuals;

use app\components\individuals\request\BaseRequest;
use yii\base\Component;
use SoapClient;
use Yii;

class Individuals extends Component {

    public $wsdl = '';
    public $username = '';
    public $password = '';

    /**
     * @var SoapClient
     */
    private $client;

    public function init() {
        try{
        $this->createSoapClient();}
        catch (SoapFault $proxy){
    var_dump(libxml_get_last_error());
    var_dump($proxy);
        }
        parent::init();
    }

    public function send(BaseRequest $request) {
        $method = pathinfo(str_replace('\\', '/', get_class($request)), PATHINFO_BASENAME);
        return @call_user_func_array([$this->client, $method], [$request]);
    }

    public function send_param(BaseRequest $request) {
        $method = pathinfo(str_replace('\\', '/', get_class($request)), PATHINFO_BASENAME);
        return @call_user_func([$this->client, $method], $request->param);
    }

    protected function createSoapClient() {
       
        
        $wsdl = Yii::getAlias($this->wsdl);
        
        $this->client = new SoapClient($wsdl, [
            'encoding' => 'UTF-8',
            'verifypeer' => false,
            'verifyhost' => false,
            'trace' => 1,
            'compression' => SOAP_COMPRESSION_ACCEPT,
            'login' => $this->username,
            'password' => $this->password,
            'exceptions' => 1,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_MEMORY,            
        ]);
    
    

}
}