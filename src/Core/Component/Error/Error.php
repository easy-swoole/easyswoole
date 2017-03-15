<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/5
 * Time: 下午7:40
 */

namespace Core\Component\Error;


class Error
{
    protected $errorCode;
    protected $errorTypeInString;
    protected $errorLevel;
    protected $description;
    protected $file;
    protected $line;
    protected $context;
    protected $trace;
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
        list($this->errorTypeInString,$this->errorLevel) = $this->mapErrorCode($this->errorCode);
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
    public function getErrorTypeInString()
    {
        return $this->errorTypeInString;
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
        $errorType = $log = null;
        switch ($code) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $errorType = 'Fatal Error';
                $log = LOG_ERR;
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $errorType = 'Warning';
                $log = LOG_WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $errorType = 'Notice';
                $log = LOG_NOTICE;
                break;
            case E_STRICT:
                $errorType = 'Strict';
                $log = LOG_NOTICE;
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $errorType = 'Deprecated';
                $log = LOG_NOTICE;
                break;
            default :
                break;
        }
        return array($errorType, $log);
    }
    function __toString()
    {
        // TODO: Implement __toString() method.
        return "errorType:{$this->errorTypeInString} \nerrorCode:{$this->errorCode} \nmessage:{$this->description} \nfile:{$this->file} \nline:{$this->line} \ntrace:{$this->traceToString()}";
    }
    function setTrace($trace = null){
        $this->trace = $trace;
    }
    function getTrace(){
        if(empty($this->trace)){
            $this->trace = debug_backtrace();
        }
        return $this->trace;
    }
    function traceToString($delimiter = null){
        if($delimiter === null){
            $delimiter = "\n";
        }
        $trace = $this->getTrace();
        $log = '';
        foreach ($trace as $i => $t)
        {
            if (!isset($t['file']))
            {
                $t['file'] = 'unknown';
            }
            if (!isset($t['line']))
            {
                $t['line'] = 0;
            }
            if (!isset($t['function']))
            {
                $t['function'] = 'unknown';
            }
            $log .= "#$i {$t['file']}({$t['line']}): ";
            if (isset($t['object']) and is_object($t['object']))
            {
                $log .= get_class($t['object']) . '->';
            }
            $log .= "{$t['function']}(){$delimiter}";
        }
        return $log;
    }
}