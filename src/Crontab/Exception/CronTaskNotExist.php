<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018-11-23
 * Time: 13:24
 */

namespace EasySwoole\EasySwoole\Crontab\Exception;

use Throwable;

/**
 * 任务不存在异常
 * Class CronTaskNotExist
 * @package EasySwoole\EasySwoole\Crontab
 */
class CronTaskNotExist extends \Exception
{
    protected $taskName;

    public function __construct(string $taskName = "", int $code = 0, Throwable $previous = null)
    {
        $this->taskName = $taskName;
        parent::__construct("the cron task {$taskName} does not exist", $code, $previous);
    }

    public function getTaskName()
    {
        return $this->taskName;
    }
}
