<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/10
 * Time: 下午3:50
 */

namespace Core\Component\IO;


class Stream
{
    private $stream;
    private $seekable;
    private $readable;
    private $writable;
    private $readList = array(
        'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
        'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
        'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a+' => true
    );
    private $writeList = array(
        'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
        'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
        'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
    );
    function __construct($resource = '',$mode = 'r+')
    {
        switch (gettype($resource)) {
            case 'resource': {
                $this->stream = $resource;
                break;
            }
            case 'object':{
                if(method_exists($resource, '__toString')) {
                    $resource = $resource->__toString();
                    $this->stream = fopen('php://memory', $mode);
                    if ($resource !== '') {
                        fwrite($this->stream, $resource);
                    }
                    break;
                }else{
                    throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
                }
            }
            default:{
                $this->stream  = fopen('php://memory', $mode);
                try{
                    $resource = (string)$resource;
                    if ($resource !== '') {
                        fwrite($this->stream, $resource);
                    }
                }catch (\Exception $exception){
                    throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
                }
            }
        }
        $info = stream_get_meta_data($this->stream);
        $this->seekable = $info['seekable'];
        $this->readable = isset($this->readList[$info['mode']]);
        $this->writable =  isset($this->writeList[$info['mode']]);
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        try {
            $this->seek(0);
            return (string) stream_get_contents($this->stream);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function close()
    {
        // TODO: Implement close() method.
        $res = $this->detach();
        if(is_resource($res)){
            fclose($res);
        }
    }

    public function detach()
    {
        // TODO: Implement detach() method.
        if (!isset($this->stream)) {
            return null;
        }
        $this->readable = $this->writable = $this->seekable = false;
        $result = $this->stream;
        unset($this->stream);
        return $result;
    }

    public function getSize()
    {
        // TODO: Implement getSize() method.
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            return $stats['size'];
        }else{
            return null;
        }
    }

    public function tell()
    {
        // TODO: Implement tell() method.
        $result = ftell($this->stream);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    public function eof()
    {
        // TODO: Implement eof() method.
        return !$this->stream || feof($this->stream);
    }

    public function isSeekable()
    {
        // TODO: Implement isSeekable() method.
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        // TODO: Implement seek() method.
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        } elseif (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position '
                . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    public function rewind()
    {
        // TODO: Implement rewind() method.
        $this->seek(0);
    }

    public function isWritable()
    {
        // TODO: Implement isWritable() method.
        return $this->writable;
    }

    public function write($string)
    {
        // TODO: Implement write() method.
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    public function isReadable()
    {
        // TODO: Implement isReadable() method.
        return $this->readable;
    }

    public function read($length)
    {
        // TODO: Implement read() method.
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \RuntimeException('Length parameter cannot be negative');
        }
        if (0 === $length) {
            return '';
        }
        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new \RuntimeException('Unable to read from stream');
        }
        return $string;
    }

    public function getContents()
    {
        // TODO: Implement getContents() method.
        //注意与__toString的区别
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    public function getMetadata($key = null)
    {
        // TODO: Implement getMetadata() method.
        if (!isset($this->stream)) {
            return $key ? null : [];
        } elseif (!$key) {
            return stream_get_meta_data($this->stream);
        } else {
            $meta = stream_get_meta_data($this->stream);
            return isset($meta[$key]) ? $meta[$key] : null;
        }
    }
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }
    function getStreamResource(){
        return $this->stream;
    }
    function truncate($size = 0){
        return ftruncate($this->stream,$size);
    }
}