<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-11
 * Time: 下午8:20
 */
namespace EasySwoole\Core\Component\SMCache\Storage;

class MMap implements StorageInterface
{
    private $fp = null;

    /**
     * @param string $file
     * @param int $size
     * @return bool
     */
    public function open(string $file, int $size) : bool
    {
        if (!file_exists($file) || filesize($file) != $size) {
            $initChr = chr(0);
            file_put_contents($file, str_repeat($initChr, $size));
        }
        $fp = \swoole\mmap::open($file, $size);
        if ($fp) {
            $this->fp = $fp;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function seek(int $offset, int $whence = SEEK_SET) : bool
    {
        return fseek($this->fp, $offset, $whence) === 0;
    }

    /**
     * @param int $size
     * @param int $offset
     * @return null|string
     */
    public function read(int $size, int $offset = -1) : ?string
    {
        $data = stream_get_contents($this->fp, $size, $offset);
        if ($data === false) {
            return null;
        } else {
            return $data;
        }
    }

    /**
     * @param string $data
     * @param int $offset
     * @return int|null
     */
    public function write(string $data, int $offset = -1) : ?int
    {
        if ($offset > -1 && !$this->seek($offset)) {
            return null;
        }
        $size = fwrite($this->fp, $data);
        if ($size === false) {
            return null;
        } else {
            return $size;
        }
    }

    /**
     * @return bool
     */
    public function flush() : bool
    {
        return fflush($this->fp);
    }

    /**
     * @return bool
     */
    public function close() : bool
    {
        return fclose($this->fp);
    }
}