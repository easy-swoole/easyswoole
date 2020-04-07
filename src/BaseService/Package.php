<?php


namespace EasySwoole\EasySwoole\BaseService;

class Package
{
    protected $operation;
    protected $data;

    const OP_PROCESS_INFO = 101;
    const OP_SERVER_STATUS_INFO = 102;
    const OP_TASK_INFO = 103;
    const OP_CRON_INFO = 104;
    const OP_CRON_STOP = 201;
    const OP_CRON_RESUME = 202;

    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param mixed $operation
     */
    public function setOperation($operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}