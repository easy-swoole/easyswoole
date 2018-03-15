<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/5
 * Time: 上午11:51
 */

namespace EasySwoole\Core\Http\Message;


class UploadFile
{
    private $tempName;
    private $stream;
    private $size;
    private $error;
    private $clientFileName;
    private $clientMediaType;
    function __construct( $tempName,$size, $errorStatus, $clientFilename = null, $clientMediaType = null)
    {
        $this->tempName = $tempName;
        $this->stream = new Stream(fopen($tempName,"r+"));
        $this->error = $errorStatus;
        $this->size = $size;
        $this->clientFileName = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }
    
    public function getTempName() {
		// TODO: Implement getTempName() method.
		return $this->tempName;
	}

    public function getStream()
    {
        // TODO: Implement getStream() method.
        return $this->stream;
    }

    public function moveTo($targetPath)
    {
        // TODO: Implement moveTo() method.
        return file_put_contents($targetPath,$this->stream) ? true :false;
    }

    public function getSize()
    {
        // TODO: Implement getSize() method.
        return $this->size;
    }

    public function getError()
    {
        // TODO: Implement getError() method.
        return $this->error;
    }

    public function getClientFilename()
    {
        // TODO: Implement getClientFilename() method.
        return $this->clientFileName;
    }

    public function getClientMediaType()
    {
        // TODO: Implement getClientMediaType() method.
        return $this->clientMediaType;
    }
}
