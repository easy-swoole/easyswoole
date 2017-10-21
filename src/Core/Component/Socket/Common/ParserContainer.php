<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/21
 * Time: 下午6:14
 */

namespace Core\Component\Socket\Common;


use Core\Component\Socket\AbstractInterface\AbstractCommandParser;

class ParserContainer
{
    private $parserObj;

    /**
     * @return mixed
     */
    public function getParserObj()
    {
        //为了IDE提示
        return $this->parserObj;
    }

    /**
     * @param mixed $parserObj
     */
    public function setParserObj(AbstractCommandParser $parserObj)
    {
        $this->parserObj = $parserObj;
    }

}