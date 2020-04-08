<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use Co\Scheduler;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Config implements CommandInterface
{

    public function commandName(): string
    {
        return 'config';
    }

    public function exec(array $args): ?string
    {
        $ret = '';
        $run = new Scheduler();
        $run->add(function () use (&$ret, $args) {
            $action = array_shift($args);
            switch ($action) {
                case 'show':
                    $key = array_shift($args);
                    $result = $this->show($key);
                    break;
                case 'set':
                    $key = array_shift($args);
                    $value = array_shift($args);
                    $result = $this->set($key, $value);
                    break;
                default:
                    $result = $this->help($args);
                    break;
            }
            $ret = $result;
        });
        $run->start();
        return $ret;
    }

    protected function show($key)
    {
        $package = new Package();
        $package->setCommand(BridgeCommand::CONFIG_INFO);
        $package->setArgs(['key' => $key]);
        $package = Bridge::getInstance()->send($package);
        if($package->getStatus() == Package::STATUS_SUCCESS){
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            return new ArrayToTextTable($data);
        }else{
            return $package->getArgs();
        }
    }

    protected function set($key, $value)
    {
        $package = new Package();
        $package->setCommand(BridgeCommand::CONFIG_SET);
        $package->setArgs(['key' => $key, 'value' => $value]);
        $package = Bridge::getInstance()->send($package);
        if($package->getStatus() == Package::STATUS_SUCCESS){
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            return new ArrayToTextTable($data);
        }else{
            return $package->getArgs();
        }
    }

    protected function handelArray($array)
    {
        $temp = [];
        foreach ($array as $key => $value) {
            $temp[] = [
                'key'   => $key,
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

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole config show [key][.key]
php easyswoole config set key value
";
    }
}
