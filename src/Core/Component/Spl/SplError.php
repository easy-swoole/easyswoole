<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/1
 * Time: 下午8:58
 */

namespace Core\Component\Spl;


class SplError
{
    protected $errorCode;
    protected $errorType;
    protected $errorLevel;
    protected $description;
    protected $file;
    protected $line;
    protected $context;
    protected $trace;
    const ERROR_TYPE_FATAL_ERROR = 'FATAL ERROR';
    const ERROR_TYPE_WARING = 'WARING';
    const ERROR_TYPE_NOTICE = 'NOTICE';
    const ERROR_TYPE_STRICT = 'STRICT';
    const ERROR_TYPE_DEPRECATED = 'DEPRECATED';
    /**
     * @param $errorCode
     * @param string $description 错误描述
     * @param null $file
     * @param null $line
     * @param null $context
     */
    function __construct($errorCode, $description, $file = null, $line = null, $context = null)
    {
        $this->errorCode = $errorCode;
        $this->description = $description;
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
        list($this->errorType,$this->errorLevel) = $this->mapErrorCode($this->errorCode);
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return mixed
     */
    public function getErrorType()
    {
        return $this->errorType;
    }

    /**
     * @return mixed
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return null
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param int $code 错误代码
     * @return array
     */
    private function mapErrorCode($code) {
        $errorType = $errorLevel = null;
        switch ($code) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $errorType = self::ERROR_TYPE_FATAL_ERROR;
                $errorLevel = LOG_ERR;
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $errorType = self::ERROR_TYPE_WARING;
                $errorLevel = LOG_WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $errorType = self::ERROR_TYPE_NOTICE;
                $errorLevel = LOG_NOTICE;
                break;
            case E_STRICT:
                $errorType = self::ERROR_TYPE_STRICT;
                $errorLevel = LOG_NOTICE;
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $errorType = self::ERROR_TYPE_DEPRECATED;
                $errorLevel = LOG_NOTICE;
                break;
            default :
                break;
        }
        return array($errorType, $errorLevel);
    }

    function __toString()
    {
        // TODO: Implement __toString() method.
        return "{$this->errorType} : {$this->description} in file {$this->file} in line {$this->line}";
    }
}