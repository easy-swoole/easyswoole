<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/1
 * Time: 下午8:58
 */

namespace Core\Component\Spl;


class SplError extends SplBean
{
    protected $errorCode;
    protected $errorType;
    protected $errorLevel;
    protected $description;
    protected $file;
    protected $line;
    protected $context;

    const ERROR_TYPE_FATAL_ERROR = 'FATAL ERROR';
    const ERROR_TYPE_WARING = 'WARING';
    const ERROR_TYPE_NOTICE = 'NOTICE';
    const ERROR_TYPE_STRICT = 'STRICT';
    const ERROR_TYPE_DEPRECATED = 'DEPRECATED';

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return mixed
     */
    public function getErrorType()
    {
        return $this->errorType;
    }

    /**
     * @param mixed $errorType
     */
    public function setErrorType($errorType)
    {
        $this->errorType = $errorType;
    }

    /**
     * @return mixed
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * @param mixed $errorLevel
     */
    public function setErrorLevel($errorLevel)
    {
        $this->errorLevel = $errorLevel;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param mixed $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
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
        list($this->errorType,$this->errorLevel) = $this->mapErrorCode($this->errorCode);
        return "{$this->errorType} : {$this->description} in file {$this->file} in line {$this->line}";
    }

    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }

}