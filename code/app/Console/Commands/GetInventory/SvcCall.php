<?php
namespace App\Console\Commands\GetInventory;
use SoapClient;

class SvcCall
{
    protected $_appToken;
    protected $_appKey;
    private $wsdl;
    private $_client = null; // SoapClient

    /**
     * SvcCall constructor.
     * @param array $config array(url ,token ,appKey)
     * @param null $appToken
     * @param null $appKey
     */
    public function __construct($config=null,$appToken=null,$appKey=null)
    {
        ini_set("soap.wsdl_cache_enabled", "0");

        $this->wsdl =  $config['appUrl'];
        $this->_appToken = $config['appToken'];
        $this->_appKey = $config['appKey'];
    }

    private function getClient()
    {
        $this->setClient() ;

        return $this->_client;
    }

    private function setClient()
    {
        $omsConfig = array(
            'active' => '1',
            'timeout' => '10'
        );
        libxml_disable_entity_loader(false);
        // 超时
        $timeout = isset($omsConfig['timeout']) && is_numeric($omsConfig['timeout']) ? $omsConfig['timeout'] : 1000;
        
        $options = array(
            "trace" => true,
            "connection_timeout" => $timeout,
            "encoding" => "utf-8" ,
            'ssl'   => array(
                'verify_peer'          => false
            ),
            'https' => array(
                'curl_verify_ssl_peer'  => false,
                'curl_verify_ssl_host'  => false
            )
        );

        $url = $this->wsdl;
        $client = new SoapClient($url, $options);

        $this->_client = $client;
    }

    /**
     * 调用webservice
     * ====================================================================================
     *
     * @param unknown_type $req            
     * @return Ambigous <mixed, NULL, multitype:, multitype:Ambigous <mixed,
     *         NULL> , StdClass, multitype:Ambigous <mixed, multitype:,
     *         multitype:Ambigous <mixed, NULL> , NULL> , boolean, number,
     *         string, unknown>
     *
     */
    private function callService($req)
    {
        $client = $this->getClient();
        $req['appToken'] = $this->_appToken;
        $req['appKey'] = $this->_appKey;
        $result = $client->callService($req);
        $result = Common::objectToArray($result);
        $return = json_decode($result['response']);
        $return = Common::objectToArray($return);
        return $return;
    }

    /**
     * 禁止数组中有null
     * @param array $arr
     * @return unknown string
     */
    private function arrFormat($arr)
    {
        if(! is_array($arr)){
            return $arr;
        }
        foreach($arr as $k => $v){
            if(! isset($v)){
                $arr[$k] = '';
            }
        }
        return $arr;
    }
    /**
     * 通用接口调用命令
     * @param string $command 接口方法名称
     * @param array $params  接口方法参数
     * @param null $config 配置系统参数 谷仓/马尾
     * @return array
     */
    public static function remoteCommand($command,$params,$config=null)
    {
        $return = array(
            'ask' => 'Failure',
            'message' => ''
        );

        $req = array(
            'service' => $command,
            'paramsJson' => json_encode($params)
        );

        $return =(new static($config))->callService($req);

        return $return;
    }

}