#PSR-7 Http Message
easyswoole完全兼容PSR7 Http Message接口规范。
## 接口规范示例
[http://www.php-fig.org/psr/psr-7/](http://www.php-fig.org/psr/psr-7/)

## 部分对象详解
### Stream 对象
easySwoole中利用php://memory实现Stream对象，(新手可以把Stream理解为一个字符串对象)，所有的操作均为二进制安全，且完全是内存IO，因此效率极高，不会由于磁盘IO问题影响执行速度。
   - __toString
   返回Stream对象中完整的流数据。
   - close
   关闭当前流对象，流对象中的数据也随之清空。
   - detach
   将流对象里面的资源（文件流句柄）从Stream对象中抽离。
        > 注意：抽离后，该Stream对象将不再不可用。
   - getSize
   获取当前Stream对象中数据的大小（长度）。
   - tell
   获取当前数据流指针所处位置。
   - eof
   判断当数据流指针是否处于资源结束位置。
   - isSeekable
   - seek
   移动数据流指针到指定位置。
   - rewind
   将数据流指针移动至开始位置。
   - isWritable
   - write
   向当前数据流写入数据。
        > 注意：写入时应该注意数据流指针所处位置。
   - isReadable
   - read
   - getContents
   - getMetadata
### UploadFile 对象
easySwoole中，所有的文件均自动转化为UploadFile对象。
   - getStream
   返回上传文件的数据流。
   - moveTo
   将上传文件存为实体文件。
        >注意：moveTo以file_put_contents实现，因此请确保保存文件时，文件存储路劲已经存在且有写入权限。 
   - getSize
   获取文件大小。
   - getError
   获取文件上传时的错误信息。
   - getClientFilename
   获取文件的客户端文件名。
   - getClientMediaType