<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午9:46
 */
require_once 'Core/Core.php';
$server = \Core\Core::getInstance()->frameWorkInitialize();
function commandParser(){
    global $argv;
    $command = '';
    $options = array();
    if(isset($argv[1])){
        $command = $argv[1];
    }
    foreach ($argv as $item){
        if(substr($item,0,2) === '--'){
            $temp = trim($item,"--");
            $temp = explode("-",$temp);
            $key = array_shift($temp);
            $options[$key] = array_shift($temp);
        }
    }
    return array(
        "command"=>$command,
        "options"=>$options
    );
}

function commandHandler(){
    $command = commandParser();
    switch ($command['command']){
        case "start":{
            startServer($command['options']);
            break;
        }
        case 'stop':{
            stopServer($command['options']);
            break;
        }
        case 'reload':{
            reloadServer($command['options']);
            break;
        }
        case 'help':
        default:{
            help($command['options']);
        }

    }
}

function startServer($options){
    global $server;
    $conf = \Conf\Config::getInstance();
    if(isset($options['daemonize'])){
        $boolean = $options['daemonize'] ? true : false;
        $conf->setConf("SERVER.CONFIG.daemonize",$boolean);
    }
    if(isset($options['port'])){
        $conf->setConf("SERVER.PORT",$options['port']);
    }
    if(isset($options['listen'])){
        $conf->setConf("SERVER.LISTER",$options['listen']);
    }
    if(isset($options['pidFile'])){
        if(!empty($options['pidFile'])){
            $pidFile = $options['pidFile'];
        }
    }
    if(isset($options['SwooleLog'])){
        if(!empty($options['SwooleLog'])){
            $conf->setConf("SERVER.CONFIG.log_file",$options['SwooleLog']);
        }
    }
    if(isset($options['workerNum'])){
        $conf->setConf("SERVER.CONFIG.worker_num",$options['workerNum']);
    }
    if(isset($options['taskWorkerNum'])){
        $conf->setConf("SERVER.CONFIG.task_worker_num",$options['taskWorkerNum']);
    }
    if(isset($options['user'])){
        $conf->setConf("SERVER.CONFIG.user",$options['user']);
    }
    if(isset($options['group'])){
        $conf->setConf("SERVER.CONFIG.group",$options['group']);
    }
    if(isset($options['cpuAffinity'])){
        $boolean = $options['cpuAffinity'] ? true : false;
        $conf->setConf("SERVER.CONFIG.open_cpu_affinity",$boolean);
    }
    echo "try start easyPHP-Swoole Server... \n";
    $server->run();
}

function stopServer($options){
    $pidFile = \Conf\Config::getInstance()->getConf("SERVER.CONFIG.pid_file");
    if(isset($options['pidFile'])){
        if(!empty($options['pidFile'])){
            $pidFile = $options['pidFile'];
        }
    }
    if(!file_exists($pidFile)){
        echo "pid file :{$pidFile} not exist \n";
        return;
    }
    $pid = file_get_contents($pidFile);
    if(!swoole_process::kill($pid,0)){
        echo "pid :{$pid} not exist \n";
        return;
    }
    if(isset($options['force'])){
        swoole_process::kill($pid,SIGKILL);
    }else{
        swoole_process::kill($pid);
    }
    //等待两秒
    $time = time();
    while (true){
        usleep(1000);
        if(swoole_process::kill($pid,0)){
            echo "server stop at ".date("y-m-d h:i:s")."\n";
            unlink($pidFile);
            break;
        }else{
            if(time() - $time > 2){
                echo "stop server fail.try --force again \n";
                break;
            }
        }
    }
}

function reloadServer($options){
    $pidFile = \Conf\Config::getInstance()->getConf("SERVER.CONFIG.pid_file");
    if(isset($options['pidFile'])){
        if(!empty($options['pidFile'])){
            $pidFile = $options['pidFile'];
        }
    }
    if(isset($options['reloadAll'])){
       $sig = SIGUSR1;
    }else{
        $sig = SIGUSR2;
    }
    if(!file_exists($pidFile)){
        echo "pid file :{$pidFile} not exist \n";
        return;
    }
    $pid = file_get_contents($pidFile);
    if(!swoole_process::kill($pid,0)){
        echo "pid :{$pid} not exist \n";
        return;
    }
    swoole_process::kill($pid,$sig);
    echo "send server reload command at ".date("y-m-d h:i:s")."\n";
}

function help($options){
    $opName = '';
    $args = array_keys($options);
    if(isset($args[0])){
        $opName = $args[0];
    }
    switch ($opName){
        case 'start':{
            echo "------------easyPHP-Swoole 启动命令------------\n";
            echo "执行php server.php start 即可启动服务。启动可选参数为:\n";
            echo "--daemonize-boolean       是否以系统守护模式运行\n";
            echo "--port-portNumber         指定服务监听端口\n";
            echo "--pidFile-fileName        指定服务PID存储文件\n";
            echo "--SwooleLog-fileName      指定Swoole日志文件\n";
            echo "--workerNum-num           设置worker进程数\n";
            echo "--taskWorkerNum-num       设置Task进程数\n";
            echo "--user-userName           指定以某个用户身份执行\n";
            echo "--group-groupName         指定以某个用户组身份执行\n";
            echo "--taskWorkerNum-num       设置Task进程数\n";
            echo "--cpuAffinity-boolean     是否开启CPU亲和\n";
            break;
        }
        case 'stop':{
            echo "------------easyPHP-Swoole 停止命令------------\n";
            echo "执行php server.php stop 即可启动服务。启动可选参数为:\n";
            echo "--pidFile-fileName        指定服务PID存储文件\n";
            echo "--force       强制停止服务\n";
            break;
        }
        case 'reload':{
            echo "------------easyPHP-Swoole 重启命令------------\n";
            echo "执行php server.php reload 即可启动服务。启动可选参数为:\n";
            echo "--pidFile-fileName        指定服务PID存储文件\n";
            echo "--pidFile-reloadAll       重启所有进程，默认仅重启Task进程\n";
            break;
        }
        default:{
            echo "------------欢迎使用easyPHP-Swoole------------\n";
            echo "有关某个命令的详细信息，请键入 help 命令,可选参数为:\n";
            echo "--start            启动easyPHP-Swoole\n";
            echo "--stop             停止easyPHP-Swoole\n";
            echo "--reload           重启easyPHP-Swoole\n";
        }
    }
}

function evenCheck(){
    if(phpversion() < 5.6){
        die("php version must >= 5.6");
    }
    if(phpversion('swoole') < 1.9){
        die("swoole version must >= 1.9.5");
    }

}

evenCheck();
commandHandler();



