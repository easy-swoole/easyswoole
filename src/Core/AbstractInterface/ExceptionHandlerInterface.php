<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/5
 * Time: 下午7:33
 */

namespace Core\AbstractInterface;


interface ExceptionHandlerInterface
{
    public function handler(\Exception $exception);
}