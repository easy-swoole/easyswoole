<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/26
 * Time: 下午7:38
 */

namespace EasySwoole\Core\Component\Rpc\Client;


use EasySwoole\Core\Component\Rpc\Common\ServiceNode;

class ServiceResponse extends \EasySwoole\Core\Component\Rpc\Common\ServiceResponse
{
    protected $responseNode = null;

    /**
     * @return null
     */
    public function getResponseNode():?ServiceNode
    {
        return $this->responseNode;
    }

    /**
     * @param null $responseNode
     */
    public function setResponseNode($responseNode): void
    {
        $this->responseNode = $responseNode;
    }

}