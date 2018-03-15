<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/3/12
 * Time: 20:09
 */

namespace App\Utility;

/**
 * 拦截器, 拦截特定需要登录才能访问一些接口
 * Class HoldUp
 * @package App\Utility
 */
class HoldUp
{
    /**
     * @var array 拦截的所有uri, 全部小写
     */
    static $uri = array(
        "/api/test/holdup" => 1, //为测试服务的接口
    );
}