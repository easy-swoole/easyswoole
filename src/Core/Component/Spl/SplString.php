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
		$this->rawString = $str;
	}

	function setString( string $string ) : SplString
	{
		parent::truncate();
		parent::rewind();
		parent::write( $string );
		$this->rawString = $string;
		return $this;
	}

	function split( int $length = 1 ) : array
	{
		return str_split( $this->rawString, $length );
	}

	function encodingConvert( string $desEncoding, $detectList
	= [
		'UTF-8',
		'ASCII',
		'GBK',
		'GB2312',
		'LATIN1',
		'BIG5',
		"UCS-2",
	] ) : SplString
	{
		$fileType = mb_detect_encoding( $this->rawString, $detectList );
		if( $fileType != $desEncoding ){
			return $this->setString( mb_convert_encoding( $this->rawString, $desEncoding, $fileType ) );
		} else{
			return $this;
		}
	}

	function toUtf8() : SplString
	{
		return $this->encodingConvert( "UTF-8" );
	}

	/*
	 * special function for unicode
	 */
	function unicodeToUtf8() : SplString
	{
		$string = preg_replace_callback( '/\\\\u([0-9a-f]{4})/i', create_function( '$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");' ), $this->rawString );
		return $this->setString( $string );
	}

	function toUnicode() : SplString
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
		$string = strtoupper( $str );//转换为大写
		return $this->setString( $string );
	}

	/*
	 * special function for unicode end
	*/

	function explode( string $separator ) : array
	{
		return explode( $separator, $this->rawString );
	}

	function subString( int $start, int $length ) : SplString
	{
		$string = substr( $this->rawString, $start, $length );
		return $this->setString( $string );
	}

	function compare( string $str, int $ignoreCase = 0 ) : int
	{
		if( $ignoreCase ){
			return strcasecmp( $str, $this->rawString );
		} else{
			return strcmp( $str, $this->rawString );
		}
	}

	function lTrim( string $charList = " \t\n\r\0\x0B" ) : SplString
	{
		return $this->setString( ltrim( $this->rawString, $charList ) );
	}

	function rTrim( string $charList = " \t\n\r\0\x0B" ) : SplString
	{
		return $this->setString( rtrim( $this->rawString, $charList ) );
	}

	function trim( string $charList = " \t\n\r\0\x0B" ) : SplString
	{
		return $this->setString( trim( $this->rawString, $charList ) );
	}

	function pad( int $length, string $padString = null, int $pad_type = STR_PAD_RIGHT ) : SplString
	{
		return $this->setString( str_pad( $this->rawString, $length, $padString, $pad_type ) );
	}

	function repeat( int $times ) : SplString
	{
		return $this->setString( str_repeat( $this->rawString, $times ) );
	}

	function length() : int
	{
		return strlen( $this->rawString );
	}

	function toUpper() : SplString
	{
		return $this->setString( strtoupper( $this->rawString ) );
	}

	function toLower() : SplString
	{
		return $this->setString( strtolower( $this->rawString ) );
	}

	function stripTags( string $allowable_tags = null ) : SplString
	{
		return $this->setString( strip_tags( $this->rawString, $allowable_tags ) );
	}

	function replace( string $find, string $replaceTo ) : SplString
	{
		return $this->setString( str_replace( $find, $replaceTo, $this->rawString ) );
	}


	function betweenInStr( string $startStr, string $endStr ) : SplString
	{
		$st = stripos( $this->rawString, $startStr );
		$ed = stripos( $this->rawString, $endStr );
		if( ($st == false || $ed == false) || $st >= $ed ){
			$string = '';
		} else{
			$string = substr( $this->rawString, ($st + 1), ($ed - $st - 1) );
		}
		return $this->setString( $string );
	}

	function regex( $regex, bool $rawReturn = false )
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

	function exist( string $find, bool $ignoreCase = true ) : bool
	{
		if( $ignoreCase ){
			$label = stripos( $this->rawString, $find );
		} else{
			$label = strpos( $this->rawString, $find );
		}
		return $label === false ? false : true;
	}

	function __toString()
	{
		return $this->rawString;
	}
}