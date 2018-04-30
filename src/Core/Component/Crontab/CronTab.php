<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/28
 * Time: 下午8:48
 */

namespace EasySwoole\Core\Component\Crontab;


use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Swoole\Process\ProcessManager;

class CronTab
{
    use Singleton;

    private $task = [];

    public function addRule(string $taskName,$rule,$task):CronTab
    {
        $this->task[$taskName] = [
            'rule'=>$rule,
            'task'=>$task,
            'isStart'=>true
        ];
        return $this;
    }

    function run()
    {
        if(!empty($this->task)){
            $name = Config::getInstance()->getConf('SERVER_NAME');
            $name = "{$name}_CronTab_Process";
            ProcessManager::getInstance()->addProcess($name,Runner::class,$this->task);
        }
    }
}