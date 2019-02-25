<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-25
 * Time: 11:16
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Console\Client;
use EasySwoole\EasySwoole\Core;

class Console implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'console';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        $conf = Config::getInstance()->getConf('CONSOLE');
        go(function ()use($conf){
            $client = new Client($conf['HOST'],$conf['PORT']);
            if($client->connect()){
                echo "connect to  tcp://".$conf['HOST'].":".$conf['PORT']." success \n";
                go(function ()use($client){
                    while (1){
                        $data = $client->recv(-1);
                        if(!empty($data)){
                            echo $data."\n";
                        }else if(!$client->getClient()->isConnected()){
                            exit();
                        }
                    };
                });
                swoole_event_add(STDIN,function()use($client){
                    $ret = trim(fgets(STDIN));
                    if(!empty($ret)){
                        go(function ()use($client,$ret){
                            $client->sendCommand($ret);
                        });
                    }
                });
            }else{
                echo "connect to  tcp://".$conf['HOST'].":".$conf['PORT']." fail \n";
            }
        });
        return null;
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        $logo = Utility::easySwooleLog();
        return $logo.<<<HELP_START
\e[33mOperation:\e[0m
\e[31m  php easyswoole console [arg1] \e[0m
\e[33mIntro:\e[0m
\e[36m  to run easyswoole remote console \e[0m
\e[33mArg:\e[0m
\e[32m  produce \e[0m                   load produce.php
HELP_START;
    }
}