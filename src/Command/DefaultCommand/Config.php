<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Utility\ArrayToTextTable;

class Config extends AbstractCommand
{
    protected $helps = [
        'config show [key][.key]',
        'config set key value'
    ];

    public function commandName(): string
    {
        return 'config';
    }

    protected function show($args)
    {
        $key = array_shift($args);
        $result = new Result();
        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'info', 'key' => $key]);

        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            $result->setMsg(new ArrayToTextTable($data));
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }

    protected function set($args)
    {
        $key = array_shift($args);
        $value = array_shift($args);
        $result = new Result();
        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'set', 'key' => $key, 'value' => $value]);
        if ($package->getStatus() == $package::STATUS_SUCCESS) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            $result->setMsg(new ArrayToTextTable($data));
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }

    protected function handelArray($array)
    {
        $temp = [];
        foreach ($array as $key => $value) {
            $temp[] = [
                'key' => $key,
                'value' => $value
            ];
        }
        return $temp;
    }

    protected function arrayConversion($key, $array)
    {
        $data = [];
        foreach ($array as $k => $value) {
            $keyName = empty($key) ? $k : "{$key}.{$k}";
            if (is_array($value)) {
                $data = array_merge($data, $this->arrayConversion($keyName, $value));
            } else {
                $data[$keyName] = $value;
            }
        }
        return $data;
    }
}
