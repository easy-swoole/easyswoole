<?php
/**
 * Created by PhpStorm.
 * User: evalor
 * Date: 2018-11-23
 * Time: 13:31
 */

namespace EasySwoole\EasySwoole\Crontab\Exception;

use Throwable;

class CronTaskRuleInvalid extends CrontabException
{
    protected $taskName;
    protected $taskRule;

    public function __construct(string $taskName = "", $taskRule = "", Throwable $previous = null)
    {
        $this->taskName = $taskName;
        $this->taskRule = $taskRule;
        parent::__construct("the cron task {$taskName} rule {$taskRule} is invalid", 0, $previous);
    }

    public function getTaskName()
    {
        return $this->taskName;
    }

    public function getTaskRule()
    {
        return $this->taskRule;
    }
}
