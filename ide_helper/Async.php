<?php


namespace Swoole;

/**
 * 异步文件系统 IO
 * https://wiki.swoole.com/wiki/page/183.html
 */
class Async
{
    /**
     * 异步读取文件内容，函数原型
     * 文件不存在会返回false
     * 成功打开文件立即返回true
     * 数据读取完毕后会回调指定的callback函数。
     *
     * swoole_async_readfile会将文件内容全部复制到内存，所以不能用于大文件的读取
     * 如果要读取超大文件，请使用swoole_async_read函数
    swoole_async_readfile最大可读取4M的文件，受限于SW_AIO_MAX_FILESIZE宏
     *
     * swoole_async_readfile(__DIR__."/server.php", function($filename, $content) {
     * echo "$filename: $content";
     * });
     *
     * @param string $filename
     * @param mixed $callback
     * @return bool
     */
    public static function readfile(string $filename, mixed $callback)
    {
    }

    /**
     * 异步写文件，调用此函数后会立即返回。当写入完成时会自动回调指定的callback函数。
     *
     * 参数1为文件的名称，必须有可写权限，文件不存在会自动创建。打开文件失败会立即返回false
     * 参数2为要写入到文件的内容，最大可写入4M
     * 参数3为写入成功后的回调函数，可选
     * 参数4为写入的选项，可以使用FILE_APPEND表示追加到文件末尾
     *
     * FILE_APPEND在1.9.1或更高版本可用
     * Linux原生异步IO不支持FILE_APPEND，并且写入的内容长度必须为4096的整数倍，否则底层会自动在末尾填充0
     *
     * @param string $filename
     * @param string $fileContent
     * @param callable|null $callback
     * @param int $flags
     * @return bool
     */
    public static function writeFile(string $filename, string $fileContent, callable $callback = null, int $flags = 0)
    {
    }

    /**
     * 异步读文件，使用此函数读取文件是非阻塞的，当读操作完成时会自动回调指定的函数。
     *
     * 此函数与swoole_async_readfile不同，它是分段读取，可以用于读取超大文件。每次只读$size个字节，不会占用太多内存。
     * 在读完后会自动回调$callback函数，回调函数接受2个参数：
     * bool callback(string $filename, string $content);
     * $filename，文件名称
     * $content，读取到的分段内容，如果内容为空，表明文件已读完
     * $offset参数在1.7.13以上版本可用
     * $callback函数，可以通过return true/false，来控制是否继续读下一段内容。
     * return true，继续读取
     * return false，停止读取并关闭文件
     *
     * @param string $filename
     * @param mixed $callback
     * @param int $size
     * @param int $offset
     * @return bool
     */
    public static function read(string $filename, mixed $callback, int $size = 8192, int $offset = 0)
    {
    }

    /**
     * 异步写文件，与swoole_async_writefile不同，swoole_async_write是分段写的。不需要一次性将要写的内容放到内存里，所以只占用少量内存。swoole_async_write通过传入的offset参数来确定写入的位置。
     *
     * 当offset为-1时表示追加写入到文件的末尾
     * Linux原生异步IO不支持追加模式，并且$content的长度和$offset必须为512的整数倍。如果传入错误的字符串长度或者$offset写入会失败，并且错误码为EINVAL
     *
     * @param string $filename
     * @param string $content
     * @param int $offset
     * @param mixed|NULL $callback
     * @return bool
     */
    public static function write(string $filename, string $content, int $offset = -1, mixed $callback = NULL)
    {
    }

    /**
     * 将域名解析为IP地址。调用此函数是非阻塞的，调用会立即返回。将向下执行后面的代码。
     *
     * 当DNS查询完成时，自动回调指定的callback函数。
     * 当DNS查询失败时，比如域名不存在，回调函数传入的$ip为空
     * swoole_async_dns_lookup("www.baidu.com", function($host, $ip){
     * echo "{$host} : {$ip}\n";
     * });
     *
     * 关闭DNS缓存
     * swoole_async_set(array(
     * 'disable_dns_cache' => true,
     * ));
     *
     * DNS随机
     * swoole_async_set(array(
     * 'dns_lookup_random' => true,
     * ));
     *
     * 指定DNS服务器
     * swoole_async_set(array(
     * 'dns_server' => '114.114.114.114',
     * ));
     *
     * @param string $domain
     * @param mixed $callback
     */
    public static function dnsLookup(string $domain, mixed $callback)
    {
    }

    /**
     * 异步执行Shell命令。相当于shell_exec函数，执行后底层会fork一个子进程，并执行对应的command命令。
     *
     * $command为执行的终端指令，如ls
     * 执行成功后返回子进程的PID
     * 命令执行完毕子进程退出后会回调指定的$callback函数，回调函数接收2个参数，第一个参数为命令执行后的屏幕输出内容$result，第二个参数为进程退出的状态信息$status
     *
     * 注意事项
     * fork创建子进程的操作代价是非常昂贵的，系统无法支撑过大的并发量
     * 使用exec时，请勿使用pcntl_signal或swoole_process::signal注册SIGCHLD函数，执行wait操作，否则在命令回调函数中，状态信息$status将为false
     * 此函数在1.9.22或更高版本可用
     *
     * 使用实例
     * $pid = Swoole\Async::exec("ps aux", function ($result, $status) {
     * var_dump(strlen($result), $status);
     * });
     * var_dump($pid);
     *
     * @param string $command
     * @param callable $callback
     */
    public static function exec(string $command, callable $callback)
    {
    }
}