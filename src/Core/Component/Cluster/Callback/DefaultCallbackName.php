<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 下午12:42
 */

namespace EasySwoole\Core\Component\Cluster\Callback;


class DefaultCallbackName
{
    const CLUSTER_NODE_BROADCAST = 'CLUSTER_NODE_BROADCAST';
    const CLUSTER_NODE_SHUTDOWN = 'CLUSTER_NODE_SHUTDOWN';
    const RPC_SERVICE_BROADCAST = 'RPC_SERVICE_BROADCAST';
}