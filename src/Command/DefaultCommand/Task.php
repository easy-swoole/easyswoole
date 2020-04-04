<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Task implements CommandInterface
{

    public function commandName(): string
    {
        return 'task';
    }

    public function exec(array $args): ?string
    {
        $action = array_shift($args);
        switch ($action) {
            case 'status':
                $result = $this->status();
                break;
            default:
                $result = $this->help($args);
                break;
        }
        return $result;
    }

    protected function status()
    {
        $file = EASYSWOOLE_TEMP_DIR . '/task.json';
        if (!file_exists($file)) {
            return "there is not task status info";
        }
        $json = json_decode(file_get_contents($file, true));
        if (empty($json)) {
            return "task status info is abnormal";
        }
        $result = new  ArrayToTextTable($json);

        return $result;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole task status
";
    }
}