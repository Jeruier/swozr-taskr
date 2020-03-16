# swozr-taskr
php任务服务组件

## 简介

某些场景对主流程没有依赖，可以直接使用任务来实现类似这些功能。框架为开发者提供了 协程 和 异步 两种任务。
- 异步任务可分为即时投递任务和延迟任务

## 功能特色

- 异步任务
- 延迟任务
- 秒级定时任务

## Install

- composer command

```bash
composer require jeruier/swozr-taskr
```

## Taskr Server Actions

- `reload()` 重新加载工作进程
- `restart(bool $onlyTaskWorker = false)` 重新启动Taskr服务器 @param $onlyTaskWorker 是否只重启work进程
- `start()` 启动Taskr服务器
- `stop()` 停止当前正在运行的Taskr服务器


## 配置服务
> 配置参数皆为可选，设有默认值

在可以配置应用组件的框架下配置taskr组件，你可以看到taskr数组里面包含了taskr的基本信息

```php
    'task' => [
        'class' => \Swozr\Taskr\Server\TaskrEngine::class,
        'host' => '0.0.0.0',
        'port' => 9501,
        'setting' => [
        
        ],
        'on' => [
       
        ],
        'listener' => [
        
        ],
        'exceptionHandler' => [
        
        ],
        'crontabs' => [
        
        ],
    ],
```
> 例如Yii框架可以使用 `Yii:$app->task->start();` 来启动服务

类配置参数启动
```php
    $config = [
        'host' => '0.0.0.0',
        'port' => 9501,
        'setting' => [
        
        ],
        'on' => [
       
        ],
        'listener' => [
        
        ],
        'exceptionHandler' => [
        
        ],
        'crontabs' => [
        
        ],
   ];
```
>使用`(new \Swozr\Taskr\Server\TaskrEngine($config))->start()` 来启动服务

#### 可配置项：
   * `host` 服务地址,默认值 `0.0.0.0`
   * `port` 端口,默认值 `9501`
   * `type` 指定Socket的类型，支持TCP、UDP、TCP6、UDP6、UnixSocket Stream/Dgram 等 [ Swoole Server 构造函数 第四个参数](https://wiki.swoole.com/wiki/page/14.html)
   * `pidName` 启动后进程的名称,默认值`swozr-taskr`
   * `pidFile` pid存放路径,默认值`/tmp/swozr.pid`
   * `logFile` 指定swoole错误日志文件。在swoole运行期发生的异常信息会记录到这个文件中。默认会打印到屏幕,默认值`/tmp/swoole.log`
   * `debug` 是否开启debug,默认值`false`
   * `on` 配置监听的事件
   * `setting` 参考 [Swoole Server 配置选项](https://wiki.swoole.com/wiki/page/274.html)
   * <a href="#listener">`listener`</a> 注册事件、设置对应事件的处理监听，事件触发组件调用，在任务里面使用
   * <a href="#exceptionHandler">`exceptionHandler`</a> 自定义异常处理类
   * <a href="#crontabs">`crontabs`</a> 需要处理的定时任务集