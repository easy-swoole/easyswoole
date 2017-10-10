<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/10
 * Time: 下午10:54
 */

namespace Core\Component\Socket\Client;


use Core\Component\Spl\SplBean;

class Client extends SplBean
{
    protected $clientType;

    /**
     * @return mixed
     */
    public function getClientType()
    {
        return $this->clientType;
    }

    /**
     * @param mixed $clientType
     */
    public function setClientType($clientType)
    {
        $this->clientType = $clientType;
    }



    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }
}