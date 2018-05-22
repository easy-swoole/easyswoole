<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-13
 * Time: 上午2:00
 */

namespace EasySwoole\Core\Component\SMCache\Storage;

class ShMop implements StorageInterface
{
    private $shmop = null;

    /**
     * @param string $file
     * @param int $size
     * @return bool
     */
    public function open(string $file, int $size) : bool
    {
        if (!file_exists($file)) {
            file_put_contents($file, chr(0));
        }
        $sysId = ftok($file, 'a');
        if ($sysId === -1) {
            return false;
        }
        $shmop = shmop_open($sysId, 'c', 0744, $size);
        if ($shmop === false) {
            return false;
        } else {
            $this->shmop = $shmop;
            return true;
        }
    }

    /**
     * @param int $size
     * @param int $offset
     * @return null|string
     */
    public function read(int $size, int $offset = 0) : ?string
    {
        $data = shmop_read($this->shmop, $offset, $size);
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
    public function write(string $data, int $offset = 0) : ?int
    {
        $size = shmop_write($this->shmop, $data, $offset);
        if ($size === false) {
            return null;
        } else {
            return $size;
        }
    }

    /**
     * @return bool
     */
    public function close() : bool
    {
        shmop_close($this->shmop);
        return true;
    }

    /**
     * @return bool
     */
    public function delete() : bool
    {
        return shmop_delete($this->shmop);
    }
}