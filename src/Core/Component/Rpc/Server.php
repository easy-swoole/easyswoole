<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午11:38
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Rpc\Client\ResponseObj;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;
use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\EventHelper;
use EasySwoole\Core\Swoole\ServerManager;

class Server
{
    use Singleton;

    private $encrypt;
    private $token;
    private $list = [];

    /**
     * @param string $name
     * @param string $serviceClass
     * @return $this|bool
     */
    function addService(string $name, string $serviceClass)
    {
        //一个EasySwoole服务上不允许同名服务
        $this->list[$name] = $serviceClass;
        return $this;
    }

    /*
     * * @param mixed $encrypt false,or openssl method ,like DES-EDE3
     */
    public function attach(int $port,$encrypt = false,$token = null,string $address = '0.0.0.0')
    {
        if(!empty($encrypt) && empty($token)){
            Trigger::error("Rpc Server auto disable because encrypt token is empty");
            $encrypt = false;
        }
        $this->encrypt = $encrypt;
        $this->token = $token;
        foreach ($this->list as $name => $item){
            $node = new ServiceNode();
            $node->setPort($port);
            $node->setServiceName($name);
            ServiceManager::getInstance()->addServiceNode($node);
        }

        $sub = ServerManager::getInstance()->addServer('RPC',$port,SWOOLE_TCP,$address,[
            'open_length_check' => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
            'package_max_length'    => 1024*64,
            'heartbeat_idle_time' => 15,
            'heartbeat_check_interval' => 2,
        ]);

        EventHelper::registerDefaultOnReceive($sub,new Parser($this->list),function ($err){
            $bean = new ResponseObj();
            $bean->setError($err);
            $bean->setStatus(Status::ACTION_NOT_FOUND);
            return $bean->__toString();
        });
    }

    function encrypt()
    {
        return $this->encrypt;
    }

    function token()
    {
        return $this->token;
    }
}