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

## 配置服务

在可以配置应用组件的框架下配置taskr组件，你可以看到taskr数组里面包含了taskr的基本信息

```php
    'task' => [
        'class' => '\Swozr\Taskr\Server\Taskr',
        'host' => '0.0.0.0',
        'port' => 9501,
        'listener' => [
        ],
        'on' => [
       
        ],
        'setting' => [
        ],
        'exceptionHandler' => [
        ],
        'type' => SWOOLE_SOCK_TCP
    ],
```
> 例如Yii框架可以使用 `Yii:$app->task->run();` 来启动服务

类配置参数启动
```php
    $config = [
          'host' => '0.0.0.0',
          'port' => 9501,
          'listener' => [
          ],
          'on' => [
         
          ],
          'setting' => [
          ],
          'exceptionHandler' => [
          ],
          'type' => SWOOLE_SOCK_TCP
   ];
```
>使用`(new \Swozr\Taskr\Server\Taskr($config))->run()` 来启动服务

#### 可配置项：
   * `host` 服务地址,默认值 `0.0.0.0`
   * `port` 端口,默认值 `9501`
   * `listener` 指定其他一同启动的服务，添加端口服务监听，可以多个
        *    时间监听者
   * `on` 配置监听的事件
        * 注册事件、设置对应事件的处理监听，事件触发组件调用，在任务里面使用
   * `setting` 参考 [Swoole Server 配置选项](https://wiki.swoole.com/wiki/page/274.html)
   * `mode` 运行的模式，参考[Swoole Server 构造函数 第三个参数](https://wiki.swoole.com/wiki/page/14.html)
   * `type` 指定Socket的类型，支持TCP、UDP、TCP6、UDP6、UnixSocket Stream/Dgram 等 [ Swoole Server 构造函数 第四个参数](https://wiki.swoole.com/wiki/page/14.html)
   * `exceptionHandler` 自定义异常处理类
   * `pidName` 启动后进程的名称,默认值`swozr-taskr`
   * `pidFile` pid存放路径,默认值`/tmp/swozr.pid`
   * `logFile` 指定swoole错误日志文件。在swoole运行期发生的异常信息会记录到这个文件中。默认会打印到屏幕,默认值`/tmp/swoole.log`
   * `debug` 是否开启debug,默认值`false`