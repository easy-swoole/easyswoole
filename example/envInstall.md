# 环境配置 
## 基础环境  
 - Linux,FreeBSD 或 MacOS 操作系统
 - php >= 5.6
   + php-common
   + php-devel
 - gcc
 - autoconf
## swoole拓展
 - 拓展下载  
   到 https://github.com/swoole/swoole-src/releases 下载最新的版本的swoole拓展源码，并解压到指定文件夹。
 - 打开终端（命令行），并切换到swoole拓展主目录写，分别执行：
    + phpzie 
    + ./configure
    + make
    + make && install
 - 以上步骤执行成功后，找到php.ini所在位置，编辑并加入 extension=swoole.so
 - 执行 php -m ，若可以看见swoole字样，则说明拓展安装成功。