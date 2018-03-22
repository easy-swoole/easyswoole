<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 3/22/18
 * Time: 3:35 AM
 */

namespace EasySwoole\Core\Component\Cluster\Common;

use EasySwoole\Core\Component\Cluster\Communicate\CommandBean;
use EasySwoole\Core\Component\Rpc\Server\ServiceManager;

class ServerManager
{
    static private $node;

    static public function exists($serverId)
    {
        return isset(self::$node[$serverId]);
    }

    static public function addNode(CommandBean $commandBean)
    {
        $args = $commandBean->getArgs();
        if (!self::exists($args['serverId'])) {
            $args['service'] = [];
            self::$node[$args['serverId']] = $args;
        }
        self::$node[$args['serverId']]['broadcastTime'] = time();
    }

    static public function delNode(CommandBean $commandBean)
    {
        $args = $commandBean->getArgs();
        $serviceManager = ServiceManager::getInstance();
        foreach (self::$node[$args['serverId']]['service'] as $nodeBean) {
            $serviceManager->deleteServiceNode($nodeBean);
        }
        unset(self::$node[$args['serverId']]);
    }

    static public function addNodeServices(CommandBean $commandBean)
    {
        $args = $commandBean->getArgs();
        if (self::exists($args['serverId'])) {
            $nodeService = self::$node[$args['serverId']]['service'];
            $del = [];
            $add = [];
            foreach ($args['service'] as $k => $v) {
                if (!isset($nodeService[$k])) {
                    $add[$k] = $v;
                }
            }
            foreach ($nodeService as $k => $v) {
                if (!isset($args['service'][$k])) {
                    $del[$k] = $v;
                }
            }
            self::$node[$args['serverId']]['service'] = $args['service'];
            $serviceManager = ServiceManager::getInstance();
            foreach ($add as $nodeBean) {
                $serviceManager->addServiceNode($nodeBean);
            }
            foreach ($del as $nodeBean) {
                $serviceManager->deleteServiceNode($nodeBean);
            }
        }
    }

    static public function getAllNodes()
    {
        return self::$node;
    }
}
