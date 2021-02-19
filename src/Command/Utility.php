<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:24
 */

namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\Utility\File;

class Utility
{
    public static function easySwooleLog()
    {
        return <<<LOGO
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/

LOGO;
    }

    static function displayItem($name, $value)
    {
        if ($value === true) {
            $value = 'true';
        } else if ($value === false) {
            $value = 'false';
        } else if ($value === null) {
            $value = 'null';
        } else if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
        return "\e[32m" . str_pad($name, 30, ' ', STR_PAD_RIGHT) . "\e[34m" . $value . "\e[0m";
    }

    public static function releaseResource($source, $destination,$confirm = false)
    {
        $filename = basename($destination);
        clearstatcache();
        $replace = true;
        if (is_file($destination)) {
            echo Color::danger("{$filename} has already existed, do you want to replace it? [ Y / N (default) ] : ");
            $answer = strtolower(trim(strtoupper(fgets(STDIN))));
            if (!in_array($answer, ['y', 'yes'])) {
                $replace = false;
            }
        }
        if ($replace) {
            if($confirm){
                echo Color::danger("do you want to release {$filename}? [ Y / N (default) ] : ");
                $answer = strtolower(trim(strtoupper(fgets(STDIN))));
                if (!in_array($answer, ['y', 'yes'])) {
                    return;
                }
            }
            File::copyFile($source, $destination);
        }
    }

    public static function opCacheClear()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    public static function bridgeCall(string $commandName, callable $function, $action, $params = [], $timeout = 3)
    {
        $arg = ['action' => $action] + $params;
        $package = Bridge::getInstance()->call($commandName, $arg, $timeout);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $result = call_user_func($function, $package);
        } else {
            $result = Color::error($package->getMsg());
        }
        return $result;
    }

}
