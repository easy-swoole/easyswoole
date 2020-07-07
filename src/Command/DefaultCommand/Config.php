<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\Result;
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

    protected function show(CallerInterface $caller)
    {
        $key = key($caller->getOneParam());
        return $this->bridgeCall(function (Package $package, Result $result) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            $result->setMsg(new ArrayToTextTable($data));
        }, 'info', ['key' => $key]);
    }

    protected function set(CallerInterface $caller)
    {
        $param = $caller->getOneParam();
        $key   = key($param);
        $value = current($param);
        return $this->bridgeCall(function (Package $package, Result $result) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            $result->setMsg(new ArrayToTextTable($data));
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
