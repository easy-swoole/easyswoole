# EasySwoole 3.8.2 命令行开发

command 3.x 需要 PHP 8.1+ 和 mbstring 扩展。

3.8.2 开始依赖 `easyswoole/command: ^3.0`，3.8.1 及 command 2.x 不变。保持已有命令路径、参数名称和 `--name=value`、`-d`、`-force` 等调用形式；不兼容旧的 PHP 命令接口。

## 分工

- command 组件：命令定义、参数类型转换与校验、帮助、注册、执行、普通输出、实时刷新、选择和确认。
- 主框架：Core 初始化、Bridge 客户端、server/process/task/crontab/install 命令及业务管理。
- 其他组件：实现 `CommandInterface` 或提供 `CommandProviderInterface`，由主框架显式注入。

内置命令按顶层 Command 通过 `DefaultCommandProvider` 延迟注册。一个命令声明多个 Action；复杂操作由独立处理器实现，并在同一个命令对象内按需创建、缓存复用。`CommandRunner` 使用 command 的执行中间件处理框架初始化和 `--watch`。帮助和参数错误不会创建命令实例或初始化 Core。

## 安装与验证

在主框架仓库根目录运行 `composer install`，Composer 会从 Packagist 安装 command。已有本地锁文件的开发者可运行 `composer update easyswoole/command`，将锁定版本更新为符合约束的正式版本。

```sh
php vendor/bin/phpunit
php bin/easyswoole.php server start --help
```

## 注入外部组件命令

在项目 `bootstrap.php` 中注册，早于 Core 初始化：

```php
use EasySwoole\EasySwoole\Command\CommandRunner;

CommandRunner::getInstance()->addProvider(new MyComponentCommandProvider());
// 或者直接注册一个带有构造函数依赖的命令对象：
CommandRunner::getInstance()->addCommand(new CacheCommand($cacheManager));
```

Provider 实现：

```php
use EasySwoole\Command\Contracts\CommandProviderInterface;
use EasySwoole\Command\CommandRegistry;

final class MyComponentCommandProvider implements CommandProviderInterface
{
    public function register(CommandRegistry $registry): void
    {
        $registry->register(
            CacheCommand::createDefinition(),
            fn () => new CacheCommand(/* 构造业务依赖 */),
        );
    }
}
```

延迟工厂中的依赖可以在执行时准备；注册和读取定义时不要创建连接或初始化应用。需要主框架运行环境的命令或操作在定义中声明 `metadata('runtime', true)`，并可声明 `Option::string('mode')`。普通独立命令无需该标记。

## 动态输出和选择

内置 `server status`、`process show`、`task show`、`crontab show` 支持 `--watch`（默认 2 秒）和 `--watch=0.5`。按 q/Esc/Ctrl+C 退出。刷新失败会结束视图并输出错误，终端状态在退出时恢复。

`process kill` 没有选择条件时使用多选并确认；带 `--pid`、`--group`、`--name` 时保持非交互调用。多个显式条件采用原有 OR 语义。`crontab stop/resume/runJobNow` 未指定 `--taskName` 时使用同一套通用选择器。确认后仍重新核对目标身份，目标变更则中止整次操作。

通用交互使用 `Console::select()`、`confirm()`、`live()`；业务标签和数据留在主框架。实时视图退出后才能进入选择或确认。原始键盘交互需要 POSIX 终端及 stty；不能使用原始模式的交互终端提供编号选择。非交互输入不会自动选择或自动确认，watch 要求支持动态绘制的终端。

## 新行为

- `--help` 和 `help <命令路径>` 正式支持。
- 参数值在执行前完成校验与类型转换，未知选项或多余参数返回输入错误。
- 布尔参数统一支持裸开关、`=true`、`=false`；值为 false 时不会开启对应能力。
- 返回值由 `CommandResult` 表达：退出码 0 成功、1 执行错误、2 参数错误。错误消息输出到 stderr。
- 重复命令注册直接报错，覆盖必须显式使用 `replace()`。

## Command 与 Action

CommandDefinition::create 只声明顶层名称，通过 action(ActionDefinition::create(...)) 添加操作。选项及位置参数定义在各个 Action 上；独立命令则直接在 CommandDefinition 上声明输入，两种模式不能混用。

execute(Input $input, Console $console) 通过 $input->action() 区分已经解析的操作，独立命令的操作为 null。ServerCommand、ProcessCommand、TaskCommand、CrontabCommand 分别缓存自己的操作处理器，InstallCommand 是独立命令。

不带命令时只列出顶层名称和描述；仅输入 server 等命令名时列出其操作及参数；server start --help 只显示 start 的帮助，help server start 也受支持。帮助与参数校验均不创建业务实例、不初始化 Core。未知操作不会回退执行默认逻辑。

中间件接收顶层 CommandDefinition，通过 actionDefinition($input->action()) 获取操作定义。主框架对 runtime 和 watch 优先读取操作元数据，再回退到命令元数据。

command 仓库的 examples/cache.php 展示了一个 CacheCommand 对象同时处理 clear 和 warmup 的用法。


## 参数规则与异常观察

Option 和 Argument 共用类型转换及校验规则。可选字段未传且没有默认值时返回 null，不调用值校验器；默认值及隐式值仍需校验。validate 返回新定义，多次调用按顺序追加，首条失败后停止。

```php
use EasySwoole\Command\Definition\{Argument, Option};
use EasySwoole\Command\Validation\Rules;

$name = Option::string('name', '目标名称', required: true);
$name = $name->validate(Rules::notBlank('目标名称不能为空'));

$count = Argument::integer('count', '处理数量', default: 100);
$count = $count->validate(Rules::range(1, 1000, '处理数量应在 1 到 1000 之间'));

$action->option($name);
$action->argument($count);
```

Rules::oneOf 可以限制允许的值，采用严格类型比较。详细帮助会显示参数类型、默认值和必填标记；ActionDefinition 及独立 CommandDefinition 可以通过 example 添加完整调用示例。

主框架继承了 onException，宿主可以显式接入自己的日志实例：

```php
use EasySwoole\Command\Event\ExceptionEvent;

$runner = CommandRunner::getInstance();
$runner->onException(static function (ExceptionEvent $event) use ($logger): void {
    $logger->error($event->error->getMessage(), [
        'exception' => $event->error,
        'command' => $event->command,
        'action' => $event->action,
    ]);
});
```

观察器可能在 Core 初始化之前执行，注入的日志实例应能独立使用。事件保留原始 Throwable 和异常链；解析尚未完成时命令上下文可能为空，输入绑定失败时 input 为 null。普通 Action 异常由 Application 统一报告。Bridge 返回的业务失败，以及带有部分执行结果的失败，直接由命令返回。主动返回失败结果不会触发异常事件，观察器自身失败不会覆盖原始错误。
