<?php

namespace app\components\patient;

use app\components\patient\request\BaseRequest;
use yii\base\Component;
use SoapClient;
use Yii;

class Patient extends Component {

    public $wsdl = '';
    public $username = '';
    public $password = '';

    /**
     * @var SoapClient
     */
    private $client;

    public function init() {
        $this->createSoapClient();
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
