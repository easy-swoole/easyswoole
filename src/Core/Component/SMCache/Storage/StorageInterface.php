<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-13
 * Time: 上午12:31
 */

namespace EasySwoole\Core\Component\SMCache\Storage;

interface StorageInterface
{
    public function open(string $file, int $size) : bool;
    public function read(int $size, int $offset) : ?string;
    public function write(string $data, int $offset) : ?int;
    public function close() : bool;
}