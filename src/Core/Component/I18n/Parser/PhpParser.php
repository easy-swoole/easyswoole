<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-10
 * Time: 上午4:39
 */

namespace EasySwoole\Core\Component\I18n\Parser;

use EasySwoole\Core\Component\I18n\AbstractInterface\ParserInterface;

class PhpParser implements ParserInterface
{
    public static function parse(string $file) : ?array
    {
        if (is_readable($file)) {
            $lang = require $file;
            return $lang;
        } else {
            return null;
        }
    }
}