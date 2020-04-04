<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Process implements CommandInterface
{

    public function commandName(): string
    {
        return 'process';
    }

    public function exec(array $args): ?string
    {
        $action = array_shift($args);
        $file = EASYSWOOLE_TEMP_DIR . '/process.json';
        if (!file_exists($file)) {
            return "there is not process info";
        }
        $json = json_decode(file_get_contents($file), true);
        if (empty($json)) {
            return "process info is abnormal";
        }
        $json = $this->processInfoHandel($json,$args);

        switch ($action) {
            case 'kill';
                $result = $this->kill($json, $args);
                break;
            case 'killAll';
                $result = $this->killAll($json, $args);
                break;
            case 'show';
                $result = $this->show($json, $args);
                break;
            default:
                $result = $this->help($args);
                break;
        }
        return $result;
    }

    protected function killProcess(array $list, $args = null)
    {
        if (empty($list)) {
            return 'not process was kill';
        }
        if (in_array('-f', $args)) {
            $sig = SIGKILL;
            $option = 'SIGKILL';
        } else {
            $sig = SIGTERM;
            $option = 'SIGTERM';
        }
        foreach ($list as $pid => $value) {
            \Swoole\Process::kill($pid, $sig);
            $list[$pid]['option'] = $option;
        }
        return new ArrayToTextTable($list);
    }

    protected function kill($json, $args)
    {
        $pidOrGroupName = array_shift($args);
        $list = [];
        foreach ($json as $pid => $value) {
            if (in_array('-p', $args)){
                if ($value['pid'] == $pidOrGroupName) {
                    $list[$pid] = $value;
                }
            }else{
                if ($value['group'] == $pidOrGroupName) {
                    $list[$pid] = $value;
                }
            }
        }
        return $this->killProcess($list, $args);
    }

    protected function killAll($json, $args)
    {
        $list = $json;
        return $this->killProcess($list, $args);
    }

    protected function show($json, $args)
    {
        return new ArrayToTextTable($json);
    }

    protected function processInfoHandel($json, $args)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        if (in_array('-d', $args)) {
            foreach ($json as $key => $value) {
                $json[$key]['memoryUsage'] = round($value['memoryUsage'] / pow(1024, ($i = floor(log($value['memoryUsage'], 1024)))), 2) . ' ' . $unit[$i];
                $json[$key]['memoryPeakUsage'] = round($value['memoryPeakUsage'] / pow(1024, ($i = floor(log($value['memoryPeakUsage'], 1024)))), 2) . ' ' . $unit[$i];
            }
        }

        return $json;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole process kill PID [-p] [-d]
php easyswoole process kill PID [-f] [-p] [-d]
php easyswoole process kill GroupName [-f] [-d]
php easyswoole process killAll [-d]
php easyswoole process killAll -f [-d]
php easyswoole process show
php easyswoole process show -d
";
    }
}