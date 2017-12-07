<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/7
 * Time: 下午2:36
 */

namespace EasySwoole\Core\Component\Spl;


use EasySwoole\Core\Component\Lib\Stream;

class SplString extends Stream
{
	private $rawString;

	function __construct( string $str = null )
	{
		parent::__construct( $str );
		$this->rawString = (string)$str;
	}

	function setString( $string ):SplString
	{
		$this->rawString = (String)$string;
		return $this;
	}

	function split( $length = 1 ):SplString
	{
		$this->rawString = str_split( $this->rawString, $length );
		return $this;
	}

	function encodingConvert( $desEncoding, $detectList
	= [
		'UTF-8',
		'ASCII',
		'GBK',
		'GB2312',
		'LATIN1',
		'BIG5',
		"UCS-2",
	] ):SplString
	{
		$fileType = mb_detect_encoding( $this->rawString, $detectList );
		if( $fileType != $desEncoding ){
			$this->rawString = mb_convert_encoding( $this->rawString, $desEncoding, $fileType );
			return $this;
		} else{
			return $this;
		}
	}

	function toUtf8():string
	{
		return $this->encodingConvert( "UTF-8" );
	}

	/*
	 * special function for unicode
	 */
	function unicodeToUtf8():SplString
	{
		$this->rawString = preg_replace_callback( '/\\\\u([0-9a-f]{4})/i', create_function( '$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");' ), $this->rawString );
		return $this;
	}

	function toUnicode():SplString
	{
		$raw = (string)$this->encodingConvert( "UCS-2" );
		$len = strlen( $raw );
		$str = '';
		for( $i = 0 ; $i < $len - 1 ; $i = $i + 2 ){
			$c  = $raw[$i];
			$c2 = $raw[$i + 1];
			if( ord( $c ) > 0 ){   //两个字节的文字
				$str .= '\u'.base_convert( ord( $c ), 10, 16 ).str_pad( base_convert( ord( $c2 ), 10, 16 ), 2, 0, STR_PAD_LEFT );
			} else{
				$str .= '\u'.str_pad( base_convert( ord( $c2 ), 10, 16 ), 4, 0, STR_PAD_LEFT );
			}
		}
		$this->rawString = strtoupper( $str );//转换为大写
		return $this;
	}

	/*
	 * special function for unicode end
	*/

	function explode( $separator )
	{
		return new SplArray( explode( $separator, $this->rawString ) );
	}

	function subString( $start, $length ):SplString
	{
		$this->rawString = substr( $this->rawString, $start, $length );
		return $this;
	}

	function compare( $str, $ignoreCase = 0 )
	{
		if( $ignoreCase ){
			return strcasecmp( $str, $this->rawString );
		} else{
			return strcmp( $str, $this->rawString );
		}
	}

	function lTrim( $charList = " \t\n\r\0\x0B" ):SplString
	{
		$this->rawString = ltrim( $this->rawString, $charList );
		return $this;
	}

	function rTrim( $charList = " \t\n\r\0\x0B" ):SplString
	{
		$this->rawString = rtrim( $this->rawString, $charList );
		return $this;
	}

	function trim( $charList = " \t\n\r\0\x0B" ):SplString
	{
		$this->rawString = trim( $this->rawString, $charList );
		return $this;
	}

	function pad( $length, $padString = null, $pad_type = STR_PAD_RIGHT ):SplString
	{
		$this->rawString = str_pad( $this->rawString, $length, $padString, $pad_type );
		return $this;
	}

	function repeat( $times ):SplString
	{
		$this->rawString = str_repeat( $this->rawString, $times );
		return $this;
	}

	function length():string
	{
		return strlen( $this->rawString );
	}

	function toUpper():SplString
	{
		$this->rawString = strtoupper( $this->rawString );
		return $this;
	}

	function toLower():SplString
	{
		$this->rawString = strtolower( $this->rawString );
		return $this;
	}

	function stripTags( $allowable_tags = null ):SplString
	{
		$this->rawString = strip_tags( $this->rawString, $allowable_tags );
		return $this;
	}

	function replace( $find, $replaceTo ):SplString
	{
		$this->rawString = str_replace( $find, $replaceTo, $this->rawString );
		return $this;
	}


	function betweenInStr( $startStr, $endStr ):SplString
	{
		$st = stripos( $this->rawString, $startStr );
		$ed = stripos( $this->rawString, $endStr );
		if( ($st == false || $ed == false) || $st >= $ed ){
			$this->rawString = '';
		} else{
			$this->rawString = substr( $this->rawString, ($st + 1), ($ed - $st - 1) );
		}
		return $this;
	}

	function regex( $regex, $rawReturn = false )
	{
		preg_match( $regex, $this->rawString, $result );
		if( !empty( $result ) ){
			if( $rawReturn ){
				return $result;
			} else{
				return $result[0];
			}
		} else{
			return null;
		}
	}

	function exist( $find, $ignoreCase = true ):bool
	{
		if( $ignoreCase ){
			$label = stripos( $this->rawString, $find );
		} else{
			$label = strpos( $this->rawString, $find );
		}
		return $label === false ? false : true;
	}

	function __toString():string
	{
		return (String)$this->rawString;
	}
}