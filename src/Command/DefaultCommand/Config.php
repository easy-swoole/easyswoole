<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Utility\ArrayToTextTable;

class Config extends AbstractCommand
{
    public function commandName(): string
    {
        return 'config';
    }

    public function help(): array
    {
        return [
            'show [key][.key]',
            'set key value'
        ];
    }

    protected function show($args)
    {
        $key = array_shift($args);
        return $this->bridgeCall(function (Package $package) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            return new ArrayToTextTable($data);
        }, 'info', ['key' => $key]);
    }

    protected function set($args)
    {
        $key = array_shift($args);
        $value = array_shift($args);
        return $this->bridgeCall(function (Package $package) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            return new ArrayToTextTable($data);
        }, 'set', ['key' => $key, 'value' => $value]);
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
