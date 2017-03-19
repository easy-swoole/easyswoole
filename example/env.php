<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 下午1:38
 */

if(phpversion() < 5.6){
    die("php version must >= 5.6");
}
if(phpversion("swoole") < 1.8){
    die("swoole version must >= 1.8.0");
}
echo "env check ok";