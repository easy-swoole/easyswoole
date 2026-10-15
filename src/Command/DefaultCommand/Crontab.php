<?php

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\Selector\CrontabSelector;
use EasySwoole\EasySwoole\Core;
use Swoole\Coroutine\Scheduler;

class Crontab extends AbstractCommand
{
    function name(): string
    {
        return 'crontab';
    }

    function description(): string
    {
        return 'EasySwoole crontab manager';
    }

    public function beforeExecute(Caller $caller, Result $result): bool
    {
        $mode = $caller->commandLine->getOption('mode');
        Core::getInstance()->initialize($mode);
        return true;
    }

    protected function init():void
    {
        $action = new Action('show','show crontab task info');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller, Result $result): void {
            try {
                $tasks = $this->fetchTasks($result);
                if ($tasks !== null) {
                    $result->result = $tasks;
                    $result->msg = $this->formatTaskList($tasks);
                }
            } catch (\Throwable $throwable) {
                $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                $result->msg = $throwable->getMessage();
            }
        });
        $this->registerAction($action);


        $this->registerTaskAction('stop', 'stopCrontabRule', 'stop crontab tasks');

        $action = new Action('stopAll','stop all crontab task');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller, Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result,$caller){
                $package = Bridge::bridgeCall( 'stopAllCrontabRule');
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getMsg();
                    $result->msg = $package->getMsg();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
        });
        $this->registerAction($action);


        $this->registerTaskAction('resume', 'resumeCrontabRule', 'resume crontab tasks');

        $action = new Action('resumeAll','resume all crontab task');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller, Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result,$caller){
                $package = Bridge::bridgeCall( 'resumeAllCrontabRule');
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getMsg();
                    $result->msg = $package->getMsg();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
        });
        $this->registerAction($action);


        $this->registerTaskAction('runJobNow', 'runCrontabJobNow', 'run crontab tasks right now');

        $action = new Action('setCrontabRule','update an crontab task time rule');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->addOption(new class('taskName','crontab task name,such as --taskName=checkAlive') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                if(empty($value)){
                    return 'taskName must be set';
                }
                return true;
            }
        });
        $action->addOption(new class('taskRule','crontab task name,such as --taskName=checkAlive') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                if(empty($value)){
                    return 'taskRule must be set';
                }
                return true;
            }
        });
        $action->setCallback(function (Caller $caller, Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result,$caller){
                $package = Bridge::bridgeCall( 'setCrontabRule',[
                    'taskName'=>$caller->commandLine->getOption('taskName'),
                    'taskRule'=>$caller->commandLine->getOption('taskRule'),
                ]);
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getMsg();
                    $result->msg = $package->getMsg();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
        });
        $this->registerAction($action);
    }

    protected function formatTaskList(array $tasks): string
    {
        if (!$tasks) {
            return 'No crontab tasks registered.';
        }
        $stopped = count(array_filter($tasks, static fn (array $task) => !empty($task['isStop'])));
        $running = count($tasks) - $stopped;
        $lines = ['Crontab tasks (total: ' . count($tasks) . ", running: {$running}, stopped: {$stopped})"];
        $index = 0;
        foreach ($tasks as $name => $task) {
            $task['taskName'] = (string)$name;
            $lines[] = '';
            $lines[] = '[' . (++$index) . '] ' . CrontabSelector::label($task);
            $lines[] = '    ' . CrontabSelector::details($task);
            $runs = $task['taskRunTimes'] ?? 0;
            $lastRun = CrontabSelector::formatTime($task['taskCurrentRunTime'] ?? null);
            $lines[] = "    Runs {$runs}   Last run {$lastRun}";
        }
        return implode(PHP_EOL, $lines);
    }

    protected function registerTaskAction(string $name, string $bridgeAction, string $description): void
    {
        $action = new Action($name, $description);
        $action->addOption(new Option('mode', 'run mode, such as --mode=dev'));
        $action->addOption(new class('taskName', 'task name; omit to select interactively') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return $value === null || (is_string($value) && trim($value) !== '')
                    ? true : 'taskName must be a non-empty string';
            }
        });
        $action->setCallback(function (Caller $caller, Result $result) use ($name, $bridgeAction): void {
            try {
                $taskName = $caller->commandLine->getOption('taskName');
                if ($taskName !== null) {
                    $package = $this->callBridge($bridgeAction, ['taskName' => $taskName]);
                    $result->result = $package->getMsg();
                    $result->msg = $package->getMsg();
                    if ($package->getStatus() !== StatusEnum::SUCCESS) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    }
                    return;
                }
                if (!$this->isInteractive()) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = 'interactive selection requires a terminal; pass --taskName';
                    return;
                }
                $tasks = $this->fetchTasks($result);
                if ($tasks === null) {
                    return;
                }
                if (!$tasks) {
                    $result->result = [];
                    $result->msg = 'no crontab tasks available';
                    return;
                }
                $choices = [];
                foreach ($tasks as $taskName => $task) {
                    $task['taskName'] = (string)$taskName;
                    $choices[] = $task;
                }
                $verb = $name === 'runJobNow' ? 'run now' : $name;
                $selected = $this->selectTasks($choices, $verb);
                if (!$selected) {
                    $result->result = [];
                    $result->msg = 'task selection cancelled';
                    return;
                }
                $names = implode(', ', array_map(static fn (array $task) => CrontabSelector::label($task), $selected));
                $answer = $this->readInput(ucfirst($verb) . " tasks: {$names}? [y/N]: ");
                if (!in_array(strtolower(trim($answer ?? '')), ['y', 'yes'], true)) {
                    $result->result = [];
                    $result->msg = 'task selection cancelled';
                    return;
                }
                $current = $this->fetchTasks($result);
                if ($current === null) {
                    return;
                }
                foreach ($selected as $task) {
                    $fresh = $current[$task['taskName']] ?? null;
                    if ($fresh === null || $fresh['taskRule'] !== $task['taskRule'] || $fresh['isStop'] !== $task['isStop']) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                        $result->msg = 'selected task has disappeared or changed; run the command again';
                        return;
                    }
                }
                $messages = [];
                $result->result = [];
                foreach ($selected as $task) {
                    $package = $this->callBridge($bridgeAction, ['taskName' => $task['taskName']]);
                    $result->result[$task['taskName']] = ['status' => $package->getStatus()->name, 'message' => $package->getMsg()];
                    $messages[] = CrontabSelector::label($task) . ': ' . $package->getMsg();
                    if ($package->getStatus() !== StatusEnum::SUCCESS) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    }
                }
                $result->msg = implode(PHP_EOL, $messages);
            } catch (\Throwable $throwable) {
                $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                $result->msg = $throwable->getMessage();
            }
        });
        $this->registerAction($action);
    }

    protected function fetchTasks(Result $result): ?array
    {
        $package = $this->callBridge('crontabInfo');
        if ($package->getStatus() !== StatusEnum::SUCCESS) {
            $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
            $result->msg = $package->getMsg();
            return null;
        }
        if (!is_array($package->getArgs())) {
            throw new \UnexpectedValueException('invalid crontab task list returned by Bridge');
        }
        return $package->getArgs();
    }

    protected function callBridge(string $action, array $args = []): Package
    {
        $package = null;
        $error = null;
        $scheduler = new Scheduler();
        $scheduler->add(static function () use ($action, $args, &$package, &$error): void {
            try {
                $package = Bridge::bridgeCall($action, $args);
            } catch (\Throwable $throwable) {
                $error = $throwable;
            }
        });
        $scheduler->start();
        if ($error !== null) {
            throw $error;
        }
        return $package;
    }

    protected function selectTasks(array $choices, string $verb): array
    {
        $selected = (new CrontabSelector($verb))->select($choices);
        if ($selected !== null) {
            return $selected;
        }
        echo "Select crontab tasks to {$verb}:\n";
        foreach ($choices as $index => $task) {
            echo '[' . ($index + 1) . '] ' . CrontabSelector::label($task) . "\n";
            echo '    ' . CrontabSelector::details($task) . "\n";
        }
        echo "[0] Cancel\n";
        while (true) {
            $input = trim($this->readInput('Enter indexes (comma-separated) [0]: ') ?? '');
            if ($input === '' || $input === '0' || strtolower($input) === 'q') {
                return [];
            }
            $selected = [];
            foreach (preg_split('/[\s,，]+/u', $input) as $index) {
                if (!ctype_digit($index) || (int)$index < 1 || (int)$index > count($choices)) {
                    $selected = [];
                    break;
                }
                $selected[(int)$index - 1] = $choices[(int)$index - 1];
            }
            if ($selected) {
                return $selected;
            }
            echo "Invalid selection; enter listed indexes or 0 to cancel.\n";
        }
    }

    protected function isInteractive(): bool
    {
        return stream_isatty(STDIN);
    }

    protected function readInput(string $prompt): ?string
    {
        echo $prompt;
        $line = fgets(STDIN);
        return $line === false ? null : $line;
    }

}
