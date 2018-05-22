<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-11
 * Time: 下午6:46
 */
namespace EasySwoole\Core\Component\SMCache;

class Hash
{

    /**
     * Same as JavaScript charCodeAt
     *
     * @param $str
     * @param $index
     * @return int
     */
    private static function charCodeAt(string $str, int $index) : int
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');
        $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
        return hexdec(bin2hex($ret));
    }

    /**
     * time33
     *
     * @param $key
     * @return int
     */
    private static function time33(string $key) : int
    {
        for($i = 0, $len = mb_strlen($key, 'UTF-8'), $hash = 5381; $i < $len; ++$i){
            $hash += ($hash << 5) + self::charCodeAt($key, $i);
        };
        return $hash & 0x7fffffff;
    }

    /**
     * murmurHash
     *
     * @param string $key
     * @param int $seed
     * @return int
     */
    private static function murmur(string $key, int $seed = 0) : int
    {
        $key  = array_values(unpack('C*', $key));
        $klen = count($key);
        $h1   = $seed < 0 ? -$seed : $seed;
        $remainder = $i = 0;
        for ($bytes=$klen-($remainder=$klen&3) ; $i < $bytes ; ) {
            $k1 = $key[$i]
                | ($key[++$i] << 8)
                | ($key[++$i] << 16)
                | ($key[++$i] << 24);
            ++$i;
            $k1  = (((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16))) & 0xffffffff;
            $k1  = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
            $k1  = (((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16))) & 0xffffffff;
            $h1 ^= $k1;
            $h1  = $h1 << 13 | ($h1 >= 0 ? $h1 >> 19 : (($h1 & 0x7fffffff) >> 19) | 0x1000);
            $h1b = (((($h1 & 0xffff) * 5) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 5) & 0xffff) << 16))) & 0xffffffff;
            $h1  = ((($h1b & 0xffff) + 0x6b64) + ((((($h1b >= 0 ? $h1b >> 16 : (($h1b & 0x7fffffff) >> 16) | 0x8000)) + 0xe654) & 0xffff) << 16));
        }
        $k1 = 0;
        switch ($remainder) {
            case 3: $k1 ^= $key[$i + 2] << 16;
            case 2: $k1 ^= $key[$i + 1] << 8;
            case 1: $k1 ^= $key[$i];
                $k1  = ((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16)) & 0xffffffff;
                $k1  = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
                $k1  = ((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16)) & 0xffffffff;
                $h1 ^= $k1;
        }
        $h1 ^= $klen;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);
        $h1  = ((($h1 & 0xffff) * 0x85ebca6b) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
        $h1 ^= ($h1 >= 0 ? $h1 >> 13 : (($h1 & 0x7fffffff) >> 13) | 0x40000);
        $h1  = (((($h1 & 0xffff) * 0xc2b2ae35) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);
        return $h1;
    }

    /**
     * make a hash
     *
     * @param string $key
     * @param int $times
     * @param int $hash1
     * @param int $hash2
     * @return int
     */
    public static function make(string $key, int $times, int $hash1 = 0, int $hash2 = 0) : int
    {
        if ($times === 0) {
            return self::murmur($key);
        } else {
            if ($hash1 === 0) {
                $hash1 = self::murmur($key);
            }
            if ($hash2 === 0) {
                $hash2 = self::time33($key);
            }
            return $hash1 + $times * $hash2;
        }
    }
}