<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/7
 * Time: 下午2:36
 */

namespace EasySwoole\Core\Component\Spl;


class SplString extends SplStream
{

	function __construct( string $str = null )
	{
		parent::__construct( $str );
	}

	function setString( string $string ) : SplString
	{
		parent::truncate();
		parent::rewind();
		parent::write( $string );
		$this->rawString = $string;
		return $this;
	}

	function split( int $length = 1 ) : SplArray
	{
		return str_split( $this->__toString(), $length );
	}

	function explode($delimiter):SplArray
    {
        return new SplArray(explode($delimiter,$this->__toString()));
    }

    function subString($start,$length):SplString
    {
        return new SplString(substr($this->__toString(),$start,$length));
    }
}