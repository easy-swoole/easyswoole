<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/30
 * Time: 下午5:44
 */

namespace EasySwoole\Core\Component\Cluster\Common;


use EasySwoole\Core\AbstractInterface\Singleton;

class EventRegister
{
    use Singleton;

    protected $list = [];
    const CLUSTER_START = 'CLUSTER_START';
    const CLUSTER_SHUTDOWN = 'CLUSTER_SHUTDOWN';
    const CLUSTER_ON_COMMAND = 'CLUSTER_ON_COMMAND';

    private $allow = ['CLUSTER_START', 'CLUSTER_SHUTDOWN', 'CLUSTER_ON_COMMAND'];

    public function add($key, callable $item)
    {
        if (in_array($key, $this->allow)) {
            if (is_callable($item)) {
                $this->list[$key] = [$item];
            } else {
                trigger_error("event {$key} is not a callable");
            }
        } else {
            trigger_error("event {$key} is not allow");
        }
    }

    public function withAdd($key,callable $item)
    {
        if (in_array($key, $this->allow)) {
            if (is_callable($item)) {
                if (isset($this->list[$key])) {
                    $old = $this->list[$key];
                } else {
                    $old = [];
                }
                $old[] = $item;
                $this->list[$key] = $old;
            } else {
                trigger_error("event {$key} is not a callable");
            }
        } else {
            trigger_error("event {$key} is not allow");
        }
    }

    public function get($key): ?array
    {
        if (isset($this->list[$key])) {
            return $this->list[$key];
        }
        return null;
    }

    public function hook(string $event,...$args)
    {
        $calls = EventRegister::getInstance()->get($event);
        if (is_array($calls)) {
            foreach ($calls as $call){
                try {
                    call_user_func_array($call,$args);
                } catch (\Throwable $throwable) {
                    trigger_error($throwable->getTraceAsString());
                }
            }
        }
    }
}