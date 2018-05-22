<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-11
 * Time: 下午10:10
 */
namespace EasySwoole\Core\Component\SMCache;

use EasySwoole\Config;
use EasySwoole\Core\Component\SMCache\Storage;
use EasySwoole\Core\Component\Trigger;

class Cache
{
    /**
     * key: 4bytes key length + 48bytes key + 4bytes last get time + 4bytes cache start
     *      + 4bytes cache length + 4bytes crc32 + 4bytes value real length + 4bytes expire
     */
    const KEY_SIZE = 76;

    private static $instance = null;
    private $keySlotsMem = null;
    private $keySlotsNum = 0;
    private $maxCacheMem = null;
    private $cacheSlotsMem = null;
    private $cacheMemNum = 0;
    private $maxQueueMem = 0;
    private $queueLock = [];
    private $queueMem = [];
    private $keyMem = null;
    private $cacheMem = [];
    private $cacheDir = '';
    private $initChr = '';
    private $keyInitial = '';
    private $storage = '';
    private $compressLevel = 0;

    public static function getInstance(...$args)
    {
        if(self::$instance === null){
            $instance = new static(...$args);
            if ($instance->keyMem === null) {
                return null;
            } else {
                self::$instance = $instance;
            }
        }
        return self::$instance;
    }

    /**
     * @param mixed ...$args
     * @return Storage\StorageInterface
     */
    private function getStorage(...$args) : Storage\StorageInterface
    {
        return new $this->storage(...$args);
    }

    private function __construct()
    {
        $config = Config::getInstance()->getConf('SMCACHE');
        $this->compressLevel = $config['compress_level'];
        $this->keySlotsMem = $config['key_slots_mem'];
        $this->maxCacheMem = $config['max_cache_mem'];
        $this->cacheSlotsMem = $config['cache_slots_mem'];
        $this->maxQueueMem = $config['max_queue_mem'];
        $this->storage = __namespace__ . '\Storage\\' . $config['storage'];
        $this->cacheDir = $config['cache_dir'];
        if (substr($this->cacheDir, -1) != '/') {
            $this->cacheDir .= '/';
        }
        if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0744)) {
            $this->throw('mkdir failed');
            return;
        }
        $this->keySlotsMem -= $this->keySlotsMem % $this::KEY_SIZE;
        $this->maxCacheMem -= $this->maxCacheMem % $this->cacheSlotsMem;
        $this->cacheMemNum = $this->maxCacheMem / $this->cacheSlotsMem;
        $this->keySlotsNum = $this->keySlotsMem / $this::KEY_SIZE;
        $this->initChr = chr(0);
        $this->keyInitial = str_repeat($this->initChr, $this::KEY_SIZE);
        $keyMem = $this->getStorage();
        if ($keyMem->open($this->cacheDir . 'keySlots', $this->keySlotsMem)) {
            $this->keyMem = $keyMem;
        } else {
            $this->throw('key slots memory open failed');
        }
    }

    /**
     * @param $message
     */
    private function throw($message) : void
    {
        Trigger::throwable(new \Exception($message));
    }

    /**
     * @param string $key
     * @return array
     */
    public function realKey(string $key) : array
    {
        $len = strlen($key);
        if ($len > 48) {
            return [md5($key), 32];
        } else {
            return [$key, $len];
        }
    }

    /**
     * @param $v
     * @return string
     */
    public function pack($v) : string
    {
        $v = \swoole_serialize::pack($v);
        if ($this->compressLevel > 0) {
            $v = gzcompress($v, $this->compressLevel);
        }
        return $v;
    }

    /**
     * @param string $v
     * @return mixed
     */
    public function unpack(string $v)
    {
        if ($this->compressLevel > 0) {
            $v = gzuncompress($v);
        }
        $v = \swoole_serialize::unpack($v);
        return $v;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expire
     * @return bool
     */
    public function set(string $key, $value, int $expire = 0) : bool
    {
        [$key, $setKeyLen] = $this->realKey($key);
        $value = $this->pack($value);
        $i = $hash = $hash1
            = $hash2 = $lru = $lruHash
            = $lastGetTime = $keyPos = 0;
        $kickOut = $exists = true;
        $data = '';
        while ($i < 5) {
            $hash = Hash::make($key, $i, $hash1, $hash2);
            if ($hash1 === 0) {
                $hash1 = $hash;
            } else if ($hash2 === 0) {
                $hash2 = $hash - $hash1;
            }
            $keyPos = ($hash % $this->keySlotsNum) * $this::KEY_SIZE;
            $data = $this->keyMem->read($this::KEY_SIZE, $keyPos);
            if ($data === null) {
                return false;
            }
            if ($data === $this->keyInitial) {
                $kickOut = false;
                $exists = false;
                break;
            }
            $keyLen = (unpack('I', substr($data, 0, 4)))[1];
            if ($keyLen == $setKeyLen && substr($data, 4, $keyLen) === $key) {
                $kickOut = false;
                break;
            }
            $last = (unpack('I', substr($data, 52, 4)))[1];
            if ($last < $lastGetTime) {
                $lastGetTime = $last;
                $lru = $keyPos;
                $lruHash = $hash;
            }
            ++$i;
        }
        if ($kickOut) {
            $keyPos = $lru;
            $hash = $lruHash;
        }
        if ($expire > 0) {
            $expire += time();
        } else {
            $expire = 0;
        }
        if ($exists) {
            return $this->update($hash, $keyPos, $data, $value, $kickOut, $key, $expire);
        } else {
            return $this->insert($hash, $keyPos, $key, $value, $expire);
        }
    }

    /**
     * @param string $key
     * @return array|null
     */
    private function find(string $key) : ?array
    {
        [$key, $setKeyLen] = $this->realKey($key);
        $i = $hash = $hash1 = $hash2 = 0;
        $value = null;
        while ($i < 5) {
            $hash = Hash::make($key, $i, $hash1, $hash2);
            if ($hash1 === 0) {
                $hash1 = $hash;
            } else if ($hash2 === 0) {
                $hash2 = $hash - $hash1;
            }
            $keyPos = ($hash % $this->keySlotsNum) * $this::KEY_SIZE;
            $data = $this->keyMem->read($this::KEY_SIZE, $keyPos);
            if ($data === null) {
                return null;
            }
            $keyLen = (unpack('I', substr($data, 0, 4)))[1];
            if ($keyLen == $setKeyLen && substr($data, 4, $keyLen) === $key) {
                $value = [
                    'keyPos' => $keyPos,
                    'data' => $data,
                    'hash' => $hash
                ];
                break;
            }
            ++$i;
        }
        return $value;
    }

    /**
     * @param int $hash
     * @return Storage\StorageInterface|null
     */
    private function getCacheMem(int $hash) : ?Storage\StorageInterface
    {
        $cacheSlot = $hash % $this->cacheMemNum;
        if (!isset($this->cacheMem[$cacheSlot])) {
            $cacheMem = $this->getStorage();
            $dir = $this->cacheDir . intval($cacheSlot / 100) . '/';
            if (!is_dir($dir) && !mkdir($dir, 0744)) {
                $this->throw('mkdir failed');
                return null;
            }
            if ($cacheMem->open($dir . 'cache_' . $cacheSlot, $this->cacheSlotsMem) === false) {
                $this->throw('open cache memory failed');
                return null;
            }
            $pos = $cacheMem->read(4, 0);
            if ($pos === null) {
                return null;
            } else if ($pos === str_repeat($this->initChr, 4)) {
                if ($cacheMem->write(pack('I', 4), 0) === null) {
                    return null;
                }
            }
            $this->cacheMem[$cacheSlot] = $cacheMem;
        } else {
            $cacheMem = $this->cacheMem[$cacheSlot];
        }
        return $cacheMem;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $k = $this->find($key);
        if ($k === null) {
            return null;
        }
        $keyPos = $k['keyPos'];
        $data = $k['data'];
        $hash = $k['hash'];
        $cacheMem = $this->getCacheMem($hash);
        if ($cacheMem === null) {
            return null;
        }
        $nData = unpack('I5', substr($data, 56));
        if ($nData[5] > 0 && $nData[5] < time()) {
            $this->keyMem->write($this->keyInitial, $keyPos); // delete this key
            return null;
        }
        $value = $cacheMem->read($nData[4], $nData[1]);
        if (crc32($value) != $nData[3]) {
            $this->keyMem->write($this->keyInitial, $keyPos);
            return null;
        }
        $this->keyMem->write(pack('I', time()), $keyPos + 52);
        return $this->unpack($value);
    }

    /**
     * @param int $hash
     * @param int $keyPos
     * @param string $data
     * @param string $value
     * @param bool $kickOut
     * @param string $key
     * @param int $expire
     * @return bool
     */
    private function update(int $hash, int $keyPos, string $data, string $value, bool $kickOut, string $key, int $expire) : bool
    {
        $realLength = strlen($value);
        $cacheMem = $this->getCacheMem($hash);
        if ($cacheMem === null) {
            return false;
        }
        $cLength = (unpack('I', substr($data, 60, 4)))[1];
        if ($cLength >= $realLength) {
            $needSize = $cLength;
            $pos = (unpack('I', substr($data, 56, 4)))[1];
        } else {
            $needSize = floor($realLength * 1.1);
            $currentPos = $cacheMem->read(4, 0);
            if ($currentPos === null) {
                return false;
            }
            $pos = (unpack('I', $currentPos))[1];
            if (($this->cacheSlotsMem - $pos) < $needSize) {
                if (($this->cacheSlotsMem - 4) >= $needSize) {
                    $pos = 4;
                } else {
                    return false;
                }
            }
            $end = $pos + $needSize;
            if ($cacheMem->write(pack('I', $end), 0) === null) {
                return false;
            }
        }
        if ($cacheMem->write($value, $pos) === null) {
            return false;
        }
        if ($kickOut) {
            $keyData = pack('I', strlen($key)) . str_pad($key, 48, $this->initChr, STR_PAD_RIGHT)
                . pack('I6', 0, $pos, $needSize, crc32($value), $realLength, $expire);
        } else {
            $keyData = pack('I4', $pos, $needSize, crc32($value), $realLength);
            if ($expire > 0) {
                $keyData .= pack('I', $expire);
            }
            $keyPos += 56;
        }
        if ($this->keyMem->write($keyData, $keyPos) === null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param int $hash
     * @param int $keyPos
     * @param string $key
     * @param string $value
     * @param int $expire
     * @return bool
     */
    private function insert(int $hash, int $keyPos, string $key, string $value, int $expire) : bool
    {
        $realLength = strlen($value);
        $needSize = floor($realLength * 1.1); // pre alloc 10%
        $cacheMem = $this->getCacheMem($hash);
        if ($cacheMem === null) {
            return false;
        }
        $currentPos = $cacheMem->read(4, 0);
        if ($currentPos === null) {
            return false;
        }
        $pos = (unpack('I', $currentPos))[1];
        if (($this->cacheSlotsMem - $pos) < $needSize) {
            if (($this->cacheSlotsMem - 4) >= $needSize) {
                $pos = 4;
            } else {
                return false;
            }
        }
        $end = $pos + $needSize;
        if ($cacheMem->write(pack('I', $end), 0) === null) {
            return false;
        }
        if ($cacheMem->write($value, $pos) === null) {
            return false;
        }
        $keyData = pack('I', strlen($key)) . str_pad($key, 48, $this->initChr, STR_PAD_RIGHT)
            . pack('I6', 0, $pos, $needSize, crc32($value), $realLength, $expire);
        if ($this->keyMem->write($keyData, $keyPos) === null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key) : bool
    {
        $k = $this->find($key);
        if ($k === null) {
            return false;
        }
        $keyPos = $k['keyPos'];
        if ($this->keyMem->write($this->keyInitial, $keyPos) !== null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $key
     * @param int $expire
     * @return bool
     */
    public function expire(string $key, int $expire) : bool
    {
        if ($expire > 0) {
            $expire += time();
        } else {
            $expire = 0;
        }
        $k = $this->find($key);
        if ($k === null) {
            return false;
        }
        $keyPos = $k['keyPos'];
        if ($this->keyMem->write(pack('I', $expire), $keyPos + 72) !== null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $key
     * @return array|null
     */
    public function key(string $key) : ?array
    {
        $k = $this->find($key);
        if ($k === null) {
            return null;
        }
        $data = $k['data'];
        $keyPos = $k['keyPos'];
        $v = unpack('I6', substr($data, 52));
        if ($v[6] > 0 && $v[6] < time()) {
            $this->keyMem->write($this->keyInitial, $keyPos);
            return null;
        }
        return [
            'key' => $key,
            'last_get_time' => $v[1],
            'cache_start' => $v[2],
            'cache_length' => $v[3],
            'value_length' => $v[5],
            'crc32' => $v[4],
            'expire' => $v[6]
        ];
    }

    /**
     * @return bool
     */
    public function clear() : bool
    {
        return $this->keyMem->write(str_repeat($this->initChr, $this->keySlotsMem), 0) === null ? false : true;
    }

    /**
     * @param string $queue
     * @return bool
     */
    private function queueLock(string $queue) : bool
    {
        if (isset($this->queueLock[$queue])) {
            $fp = $this->queueLock[$queue];
        } else {
            $fp = fopen($this->cacheDir . 'queue/' . $queue, 'r');
            if ($fp === false) {
                return false;
            }
            $this->queueLock[$queue] = $fp;
        }
        return flock($fp, LOCK_EX);
    }

    /**
     * @param string $queue
     * @return bool
     */
    private function queueUnlock(string $queue) : bool
    {
        if (!isset($this->queueLock[$queue])) {
            return false;
        }
        return flock($this->queueLock[$queue], LOCK_UN);
    }

    /**
     * @param string $queue
     * @return Storage\StorageInterface|null
     */
    private function getQueueMem(string $queue) : ?Storage\StorageInterface
    {
        if (isset($this->queueMem[$queue])) {
            return $this->queueMem[$queue];
        } else {
            $dir = $this->cacheDir . 'queue/';
            if (!is_dir($dir) && !mkdir($dir, 0744)) {
                $this->throw('mkdir failed');
                return null;
            }
            $queueMem = $this->getStorage();
            if ($queueMem->open($dir . $queue, $this->maxQueueMem) === false) {
                $this->throw('open queue memory failed');
                return null;
            }
            /**
             * 4bytes front + 4bytes rear + 4bytes queue size
             */
            $data = $queueMem->read(12, 0);
            if ($data === null) {
                return null;
            } else if ($data === str_repeat($this->initChr, 12)) {
                if ($queueMem->write(pack('I3', 12, 12, 0), 0) === null) {
                    return null;
                }
            }
            $this->queueMem[$queue] = $queueMem;
            return $queueMem;
        }
    }

    /**
     * @param string $queue
     * @param $data
     * @return bool|null
     */
    public function enQueue(string $queue, $data) : ?bool
    {
        $data = $this->pack($data);
        $queueMem = $this->getQueueMem($queue);
        if ($queueMem === null) {
            return false;
        }
        if (!$this->queueLock($queue)) {
            return false;
        }
        $r = $queueMem->read(12, 0);
        if ($r === null) {
            $this->queueUnlock($queue);
            return false;
        }
        $info = unpack('I3', $r);
        $front = $info[1];
        $rear = $info[2];
        $queueSize = $info[3];
        if ($front == $rear && $queueSize > 0) {
            $this->queueUnlock($queue); // full
            return null;
        }
        /**
         * 4bytes real length + value
         */
        $realLength = strlen($data);
        $needSize = $realLength + 4;
        if ($rear < $front) {
            if ($front - $rear < $needSize) {
                $this->queueUnlock($queue);
                return null;
            }
        } else if ($this->maxQueueMem - $rear < $needSize) {
            if ($this->maxQueueMem - $rear >= 4
                && $queueMem->write(str_repeat($this->initChr, 4), $rear) === null) {
                $this->queueUnlock($queue);
                return false;
            }
            $rear = 12;
            if ($front > $rear && ($front - $rear) < $needSize) {
                $this->queueUnlock($queue);
                return null;
            } else if ($front == $rear && ($queueSize > 0 || $this->maxQueueMem - $rear < $needSize)) {
                $this->queueUnlock($queue);
                return null;
            }
        }
        if ($queueMem->write(pack('I', $realLength) . $data, $rear) === null
            || $queueMem->write(pack('I2', $rear + $needSize, $queueSize + 1), 4) === null) {
            $this->queueUnlock($queue);
            return false;
        }
        $this->queueUnlock($queue);
        return true;
    }

    /**
     * @param string $queue
     * @return mixed
     */
    public function deQueue(string $queue)
    {
        $queueMem = $this->getQueueMem($queue);
        if ($queueMem === null) {
            return null;
        }
        if (!$this->queueLock($queue)) {
            return null;
        }
        $r = $queueMem->read(12, 0);
        if ($r === null) {
            $this->queueUnlock($queue);
            return null;
        }
        $info = unpack('I3', $r);
        $front = $info[1];
        $rear = $info[2];
        $queueSize = $info[3];
        if ($queueSize == 0) {
            $this->queueUnlock($queue);
            return null;
        }
        $k = $queueMem->read(4, $front);
        if ($k === null) {
            $this->queueUnlock($queue);
            return null;
        } else if ($k == str_repeat($this->initChr, 4)) {
            $front = 12;
            $k = $queueMem->read(4, $front);
            if ($k === null) {
                $this->queueUnlock($queue);
                return null;
            }
        }
        $len = (unpack('I', $k))[1];
        $data = $queueMem->read($len, $front + 4);
        if ($data === null) {
            $this->queueUnlock($queue);
            return null;
        }
        if ($front + $len + 8 > $this->maxQueueMem) {
            $front = 12;
        } else {
            $front = $front + $len + 4;
        }
        if ($queueMem->write(pack('I3', $front, $rear, $queueSize - 1), 0) === null) {
            $this->queueUnlock($queue);
            return null;
        }
        $this->queueUnlock($queue);
        return $this->unpack($data);
    }

    /**
     * @param string $queue
     * @return int|null
     */
    public function queueSize(string $queue) : ?int
    {
        $queueMem = $this->getQueueMem($queue);
        if ($queueMem === null) {
            return null;
        }
        $read = $queueMem->read(4, 8);
        if ($read === null) {
            return null;
        }
        $k = unpack('I', $read);
        return $k[1];
    }

    /**
     * @param string $queue
     * @return bool
     */
    public function clearQueue(string $queue) : bool
    {
        $queueMem = $this->getQueueMem($queue);
        if ($queueMem === null) {
            return false;
        }
        if (!$this->queueLock($queue)) {
            return false;
        }
        if ($queueMem->write(pack('I3', 12, 12, 0), 0) === null
            || $queueMem->write(str_repeat($this->initChr, $this->maxQueueMem - 12), 12) === null) {
            $this->queueUnlock($queue);
            return false;
        }
        $this->queueUnlock($queue);
        return true;
    }

    private function _close() : void
    {
        $this->keyMem->close();
        foreach ($this->cacheMem as $cacheMem) {
            $cacheMem->close();
        }
        foreach ($this->queueMem as $queueMem) {
            $queueMem->close();
        }
        foreach ($this->queueLock as $queueLock) {
            fclose($queueLock);
        }
    }

    private function _flush() : void
    {
        $this->keyMem->flush();
        foreach ($this->cacheMem as $cacheMem) {
            $cacheMem->flush();
        }
        foreach ($this->queueMem as $queueMem) {
            $queueMem->flush();
        }
    }

    private function _delete() : void
    {
        $this->keyMem->delete();
        foreach ($this->cacheMem as $cacheMem) {
            $cacheMem->delete();
        }
        foreach ($this->queueMem as $queueMem) {
            $queueMem->delete();
        }
        foreach ($this->queueLock as $queueLock) {
            fclose($queueLock);
        }
    }

    public static function close() : void
    {
        if (self::$instance !== null) {
            self::$instance->_close();
            self::$instance = null;
        }
    }

    public static function flush() : void
    {
        if (self::$instance !== null) {
            if (self::$instance->storage != 'MMap') {
                self::$instance->throw('only mmap has flush method');
                return;
            }
            self::$instance->_flush();
        }
    }

    public static function delete() : void
    {
        if (self::$instance !== null) {
            if (self::$instance->storage != 'ShMop') {
                self::$instance->throw('only shmop has delete method');
                return;
            }
            self::$instance->_delete();
            self::$instance = null;
        }
    }
}