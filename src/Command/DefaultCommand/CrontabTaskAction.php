<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Exception\InterruptedException;
use EasySwoole\Command\Input\Input;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\EasySwoole\Command\Selector\CrontabSelector;

abstract class CrontabTaskAction extends CrontabAction
{
    abstract protected function action(): string;
    abstract protected function bridgeAction(): string;
    protected function handle(Input $input): CommandResult
    {
        $result = new CommandResult();
        $taskName = $input->option('taskName');
        if ($taskName !== null) {
            $package = $this->callBridge($this->bridgeAction(), ['taskName' => $taskName]);
            $result->data = $package->getMsg();
            $result->message = $package->getMsg();
            if ($package->getStatus() !== StatusEnum::SUCCESS) {
                $result->exitCode = 1;
            }
            return $result;
        }
        if (!$this->isInteractive()) {
            $result->exitCode = 1;
            $result->message = 'interactive selection requires a terminal; pass --taskName';
            return $result;
        }
        $tasks = $this->fetchTasks($result);
        if ($tasks === null) {
            return $result;
        }
        if (!$tasks) {
            $result->data = [];
            $result->message = 'no crontab tasks available';
            return $result;
        }
        $choices = [];
        foreach ($tasks as $taskName => $task) {
            $task['taskName'] = (string) $taskName;
            $choices[] = $task;
        }
        $verb = $this->action() === 'runJobNow' ? 'run now' : $this->action();
        $selected = $this->selectTasks($choices, $verb);
        if (!$selected) {
            $result->data = [];
            $result->message = 'task selection cancelled';
            return $result;
        }
        $names = implode(', ', array_map(static fn(array $task) => CrontabSelector::label($task), $selected));
        $answer = $this->readInput(ucfirst($verb) . " tasks: {$names}? [y/N]: ");
        if (!in_array(strtolower(trim($answer ?? '')), ['y', 'yes'], true)) {
            $result->data = [];
            $result->message = 'task selection cancelled';
            return $result;
        }
        $current = $this->fetchTasks($result);
        if ($current === null) {
            return $result;
        }
        foreach ($selected as $task) {
            $fresh = $current[$task['taskName']] ?? null;
            if ($fresh === null || $fresh['taskRule'] !== $task['taskRule'] || $fresh['isStop'] !== $task['isStop']) {
                $result->exitCode = 1;
                $result->message = 'selected task has disappeared or changed; run the command again';
                return $result;
            }
        }
        $messages = [];
        $result->data = [];
        foreach ($selected as $task) {
            try {
                $package = $this->callBridge($this->bridgeAction(), ['taskName' => $task['taskName']]);
            } catch (\Throwable $error) {
                if ($error instanceof InterruptedException || !$result->data) {
                    throw $error;
                }
                // 批量操作可能已处理部分任务，保留此前的处理结果。
                $result->exitCode = 1;
                $messages[] = CrontabSelector::label($task) . ': ' . $error->getMessage();
                break;
            }
            $result->data[$task['taskName']] = ['status' => $package->getStatus()->name, 'message' => $package->getMsg()];
            $messages[] = CrontabSelector::label($task) . ': ' . $package->getMsg();
            if ($package->getStatus() !== StatusEnum::SUCCESS) {
                $result->exitCode = 1;
            }
        }
        $result->message = implode(PHP_EOL, $messages);
        return $result;
    }
}
