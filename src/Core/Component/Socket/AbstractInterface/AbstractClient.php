<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/21
 * Time: 下午5:46
 */

namespace Core\Component\Socket\AbstractInterface;


use Core\Component\Spl\SplBean;

abstract class AbstractClient extends SplBean
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
}