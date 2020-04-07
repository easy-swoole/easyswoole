<?php


namespace EasySwoole\EasySwoole\BaseService;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\EasySwoole\Trigger;
use PhpParser\Node\Param;
use Swoole\Coroutine\Socket;
use Swoole\Timer;

//本进程用于做Easyswoole后续的一些基础附加服务
class BaseService extends AbstractProcess
{

    private $processJsonFile;
    private $serverStatusJsonFile;
    private $cronTabJsonFile;
    private $taskJsonFile;
    public static $baseServiceSockFile = EASYSWOOLE_TEMP_DIR . '/baseServiceSockFile.sock';

    const ERROR_PACKAGE_ERROR = -1;
    const ERROR_SERVICE_ERROR = -2;

    protected function run($arg)
    {
        if (file_exists(self::$baseServiceSockFile)) {
            unlink(self::$baseServiceSockFile);
        }
        $socketServer = new Socket(AF_UNIX, SOCK_STREAM, 0);
        try {
            if (!$socketServer->bind(self::$baseServiceSockFile)) {
                throw new Exception(static::class . ' bind ' . self::$baseServiceSockFile . ' fail case ' . $socketServer->errMsg);
            }
            if (!$socketServer->listen(2048)) {
                throw new Exception(static::class . ' listen ' . self::$baseServiceSockFile . ' fail case ' . $socketServer->errMsg);
            }
            while (1) {
                $client = $socketServer->accept(-1);
                if (!$client) {
                    return;
                }
                $this->onAccept($client);
            }
        } catch (\Throwable $throwable) {
            Trigger::getInstance()->throwable($throwable);
        }
    }

    protected function onAccept(Socket $socket)
    {
        // 收取包头4字节计算包长度 收不到4字节包头丢弃该包
        $header = $socket->recvAll(4, 1);
        if (strlen($header) != 4) {
            $socket->sendAll(Protocol::pack(serialize(self::ERROR_PACKAGE_ERROR)));
            $socket->close();
            return;
        }
        // 收包头声明的包长度 包长一致进入命令处理流程
        //多处close是为了快速释放连接
        $allLength = Protocol::packDataLength($header);
        $data = $socket->recvAll($allLength, 1);
        if (strlen($data) != $allLength) {
            $socket->sendAll(Protocol::pack(serialize(self::ERROR_PACKAGE_ERROR)));
            $socket->close();
            return;
        }
        /** @var Package $package */
        $package = unserialize($data);
        if (!$package instanceof Package) {
            $socket->sendAll(Protocol::pack(serialize(self::ERROR_PACKAGE_ERROR)));
            $socket->close();
            return;
        }
        try {
            switch ($package->getOperation()) {
                case $package::OP_PROCESS_INFO:
                    $data = Manager::getInstance()->info();
                    break;
                case $package::OP_SERVER_STATUS_INFO:
                    $data = ServerManager::getInstance()->getSwooleServer()->stats();
                    break;
                case $package::OP_TASK_INFO:
                    $data = TaskManager::getInstance()->status();
                    break;
                case $package::OP_CRON_INFO:
                    $data = $this->getCrontabInfo();
                    break;
                case $package::OP_CRON_STOP:
                    $data = $this->crontabStop($package->getData());
                    break;
                case $package::OP_CRON_RESUME:
                    $data = $this->crontabResume($package->getData());
                    break;
            }
            $socket->sendAll(Protocol::pack(serialize($data)));
            $socket->close();
        } catch (\Throwable $exception) {
            $socket->sendAll(Protocol::pack(serialize(self::ERROR_SERVICE_ERROR)));
            $socket->close();
            throw $exception;
        }
    }

    protected function getCrontabInfo()
    {
        $info = Crontab::getInstance()->infoTable();
        $data = [];
        foreach ($info as $k => $v) {
            $data[$k] = $v;
        }
        return $data;
    }

    protected function crontabStop($contabName){
        $info = Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)){
            return "crontab is not found.";
        }
        if ($crontab['isStop']==1){
            return "crontab is already stop.";
        }

        $info->set($contabName,['isStop'=>1]);
        return "crontab:test is stop suceess.";
    }

    protected function crontabResume($contabName){
        $info = Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)){
            return "crontab is not found.";
        }
        if ($crontab['isStop']==0){
            return "crontab is running.";
        }
        $info->set($contabName,['isStop'=>0]);
        return "crontab:test resume suceess.";
    }

    protected function onShutDown()
    {
        if (is_file(self::$baseServiceSockFile)) {
            unlink(self::$baseServiceSockFile);
        }
    }
}