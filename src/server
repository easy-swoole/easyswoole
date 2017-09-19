<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午9:46
 */

////////////////////////////////////////////////////////////////////
//                          _ooOoo_                               //
//                         o8888888o                              //
//                         88" . "88                              //
//                         (| ^_^ |)                              //
//                         O\  =  /O                              //
//                      ____/`---'\____                           //
//                    .'  \\|     |//  `.                         //
//                   /  \\|||  :  |||//  \                        //
//                  /  _||||| -:- |||||-  \                       //
//                  |   | \\\  -  /// |   |                       //
//                  | \_|  ''\---/''  |   |                       //
//                  \  .-\__  `-`  ___/-. /                       //
//                ___`. .'  /--.--\  `. . ___                     //
//            \  \ `-.   \_ __\ /__ _/   .-` /  /                 //
//      ========`-.____`-.___\_____/___.-`____.-'========         //
//                           `=---='                              //
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//         佛祖保佑       永无BUG       永不修改                     //
////////////////////////////////////////////////////////////////////


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
            $options[$key] = array_shift($temp) ?: '';
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
    opCacheClear();
    global $server;
    $conf = \Conf\Config::getInstance();
    if(isset($options['d'])){
        $conf->setConf("SERVER.CONFIG.daemonize",true);
    }else{
        \Conf\Config::getInstance()->setConf("SERVER.CONFIG.pid_file",null);
    }
    if(!empty($options['p'])){
        $conf->setConf("SERVER.PORT",$options['p']);
    }
    if(isset($options['ip'])){
        $conf->setConf("SERVER.LISTER",$options['ip']);
    }
    if(!empty($options['pid'])){
        $pidFile = $options['pid'];
        \Conf\Config::getInstance()->setConf("SERVER.CONFIG.pid_file",$pidFile);
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
        $conf->setConf("SERVER.CONFIG.open_cpu_affinity",true);
    }
    if($conf->getConf('SERVER.CONFIG.daemonize')){
        echo "start  easyswoole in daemonize model... \n";
        $server->run();
    }else{
        echo "start  easyswoole in blocking model... \n";
        $server->run();
    }

}

function stopServer($options){
    $pidFile = \Conf\Config::getInstance()->getConf("SERVER.CONFIG.pid_file");
    if(!empty($options['pid'])){
        $pidFile = $options['pid'];
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
    if(isset($options['f'])){
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
            if(is_file($pidFile)){
                unlink($pidFile);
            }
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
    if(isset($options['pid'])){
        if(!empty($options['pid'])){
            $pidFile = $options['pid'];
        }
    }
    if(isset($options['all']) && $options['all'] == false){
        $sig = SIGUSR2;
    }else{
        $sig = SIGUSR1;
    }
    if(!file_exists($pidFile)){
        echo "pid file :{$pidFile} not exist \n";
        return;
    }
    $pid = file_get_contents($pidFile);
    opCacheClear();
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
            echo "--d                       是否以系统守护模式运行\n";
            echo "--p-portNumber            指定服务监听端口\n";
            echo "--pid-fileName            指定服务PID存储文件\n";
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
            echo "--pid-fileName        指定服务PID存储文件\n";
            echo "--f                   强制停止服务\n";
            break;
        }
        case 'reload':{
            echo "------------easyPHP-Swoole 重启命令------------\n";
            echo "执行php server.php reload 即可启动服务。启动可选参数为:\n";
            echo "--pid-fileName        指定服务PID存储文件\n";
            echo "--pid-all             是否重启所有进程，默认true\n";
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
function opCacheClear(){
    if(function_exists('apc_clear_cache')){
        apc_clear_cache();
    }
    if(function_exists('opcache_reset')){
        opcache_reset();
    }
}
evenCheck();
commandHandler();



