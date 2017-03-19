<?php
	/**
	 * Created by PhpStorm.
	 * User: 一丰
	 * Date: 2016/5/17
	 * Time: 15:25
	 */

	namespace Core\Utility;


	class StringHandler
	{
		//unicode => utf8
		static function unicodeToUtf8($str)
		{
			return preg_replace_callback(
				'/\\\\u([0-9a-f]{4})/i',
				create_function(
					'$matches',
					'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
				),
				$str
			);
		}
		static function unKnownEncodeToUtf8($data) {
			if (! empty ( $data )) {
				$fileType = mb_detect_encoding ( $data, array (
						'UTF-8',
						'GBK',
						'GB2312',
						'LATIN1',
						'BIG5'
				) );
				if ($fileType != 'UTF-8') {
					$data = mb_convert_encoding ( $data, 'UTF-8', $fileType );
				}
			}
			return $data;
		}
		/**
		 * 把字符串转成数组，支持汉字，只能是utf-8格式的
		 * @param $str
		 * @return array
		 */
		static function stringToArray($str)
		{
			$result = array();
			$len = strlen($str);
			$i = 0;
			while($i < $len){
				$chr = ord($str[$i]);
				if($chr == 9 || $chr == 10 || (32 <= $chr && $chr <= 126)) {
					$result[] = substr($str,$i,1);
					$i +=1;
				}elseif(192 <= $chr && $chr <= 223){
					$result[] = substr($str,$i,2);
					$i +=2;
				}elseif(224 <= $chr && $chr <= 239){
					$result[] = substr($str,$i,3);
					$i +=3;
				}elseif(240 <= $chr && $chr <= 247){
					$result[] = substr($str,$i,4);
					$i +=4;
				}elseif(248 <= $chr && $chr <= 251){
					$result[] = substr($str,$i,5);
					$i +=5;
				}elseif(252 <= $chr && $chr <= 253){
					$result[] = substr($str,$i,6);
					$i +=6;
				}
			}
			return $result;
		}

        static  function utf8ToUnicode($raw){
            $raw = iconv('UTF-8', 'UCS-2', $raw);
            $len  = strlen($raw);
            $str  = '';
            for ($i = 0; $i < $len - 1; $i = $i + 2){
                $c  = $raw[$i];
                $c2 = $raw[$i + 1];
                if (ord($c) > 0){   //两个字节的文字
                    $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
                } else {
                    $str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
                }
            }
            $str = strtoupper($str);//转换为大写
            return $str;
        }

	}