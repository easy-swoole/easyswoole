<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-10
 * Time: 上午4:35
 */

namespace EasySwoole\Core\Component\I18n\AbstractInterface;

interface ParserInterface
{
    public static function parse(string $file) : ?array;
}