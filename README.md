# swozr-taskr
php任务服务组件

```text
   _____                             ______           __
  / ___/      ______  ____  _____   /_  __/___ ______/ /_______
  \__ \ | /| / / __ \/_  / / ___/    / / / __ `/ ___/ //_/ ___/
 ___/ / |/ |/ / /_/ / / /_/ /       / / / /_/ (__  ) ,< / /
/____/|__/|__/\____/ /___/_/       /_/  \__,_/____/_/|_/_/    
```
[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.4.1-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)


## 简介

在一些业务场景需要用户异步任务或秒级的定时耗时任务，在一些非swoole的php框架中未支持，在因需要共用一些业务框架逻辑或
不想再维护另一个单独部署的swoole框架的场景下，该任务服务组件可以解决这类问题。


## 功能特色

- 异步任务
- 延迟任务
- 秒级定时任务
- RabbmitMQ任务

## Install

- composer command

```bash
composer require jeruier/swozr-taskr
```

## Taskr Server Actions

- `status()` Taskr服务运行状态
- `reload()` 重新加载工作进程
- `restart(bool $onlyTaskWorker = false)` 重新启动Taskr服务 @param $onlyTaskWorker 是否只重启work进程
- `start()` 启动Taskr服务
- `stop()` 停止当前正在运行的Taskr服务


## 配置服务
> 配置参数皆为可选，设有默认值

在可以配置应用组件的框架下配置taskr组件，你可以看到taskr数组里面包含了taskr的基本信息

```php
    'task' => [
        'class' => \Swozr\Taskr\Server\TaskrEngine::class,
        'host' => '0.0.0.0',
        'port' => 9501,
        'MQProcessMum' => 3,
        'setting' => [
            'task_worker_num' => 12,
            ...
        ],
        'listener' => [
            ...
        ],
        'exceptionHandler' => [
            ...
        ],
        'crontabs' => [
            ...
        ],
        'rabbmitMqs' => [
            ...
        ],
    ],
```
> 例如Yii框架可以使用 `Yii:$app->task->start();` 来启动服务

类配置参数启动
```php
    $config = [
        'host' => '0.0.0.0',
        'port' => 9501,
        'MQProcessMum' => 3,
        'setting' => [
             'task_worker_num' => 12,
            ...
        ],
        'listener' => [
            ...
        ],
        'exceptionHandler' => [
            ...
        ],
        'crontabs' => [
            ...
        ],
        'rabbmitMqs' => [
            ...
        ],
   ];
```
>使用`(new \Swozr\Taskr\Server\TaskrEngine($config))->start()` 来启动服务

#### 可配置项：
>以下配置项皆为可选配置（非必须）
   * `host` 服务地址,默认值 `0.0.0.0`
   * `port` 端口,默认值 `9501`
   * `MQProcessMum` 配置rabbmitMq任务运行处理进程数，默认值`1`（在配置了<a href="#rabbmitMqs">`rabbmitMqs`</a>时生效,且设置值需要小于setting的task_worker_num，配置的这几个任务经常将全权处理rabbmitMq任务,配置的task_worker_num减MQProcessMum的多余任务才会执行其他类型的任务）
   * `type` 指定Socket的类型，支持TCP、UDP、TCP6、UDP6、UnixSocket Stream/Dgram 等 [ Swoole Server 构造函数 第四个参数](https://wiki.swoole.com/wiki/page/14.html)
   * `pidName` 启动后进程的名称,默认值`swozr-taskr`
   * `pidFile` pid存放路径,默认值`/tmp/swozr.pid`
   * `logFile` 指定swoole错误日志文件。在swoole运行期发生的异常信息会记录到这个文件中。默认会打印到屏幕,默认值`/tmp/swoole.log`
   * `debug` 是否开启debug,默认值`true`
   * `on` 配置监听的事件
   * `setting` 参考 [Swoole Server 配置选项](https://wiki.swoole.com/wiki/page/274.html)
   * <a href="#listener">`listener`</a> 注册事件、设置对应事件的处理监听，事件触发组件调用，在任务里面使用
   * <a href="#exceptionHandler">`exceptionHandler`</a> 自定义异常处理类
   * <a href="#crontabs">`crontabs`</a> 需要处理的定时任务集
   * <a href="#rabbmitMqs">`rabbmitMqs`</a> 配置rabbmitMq任务
   
   
### <a name="listener">listener配置</a>
 >listener为键为事件名称值为事件处理或事件处理数组
 ###### 事件名称
- Swoole事件：Swoole 文档中的每个事件，在 Swoft 里面均可监听，并且可以存在多个监听器。（完整事件列表请参阅 [SwooleEvent.php](src/Event/SwooleEvent.php) 文件）
- Swoft事件：基于 Swoole 的回调处理扩展了一些可用 Server 事件，提供更加精细的操作空间（完整事件列表请参阅 [ServerEvent.php](src/Event/ServerEvent.php) 文件）         
###### 事件处理定义
> [Event](src/Base/Event.php) $event 事件
- 闭包方式
 ```php
    'listener' => [
        ServerEvent::BEFORE_ADDED_EVENT => function(Event $event){
            //触发事件后处理
            $target = $event->getTarget(); //事件触发所传递
            $param = $event->getParam('key'); //获取指定参数
            $params = $event->getParams();  //获取所有参数
        },
        ...
    ],
```
- 类public方法或类静态方法方式
> 事件处理为无需参数可实例化可以调用的方法或可以调用的静态方法，注入的参数为$event
```php
       'listener' => [
           ServerEvent::BEFORE_ADDED_EVENT => [
                'EventHandlerController@action',
                'EventHandlerController@staticAction',
                ...
           ],
           ...
       ], 
```
- 可使用函数方法调用的类
> 该类需定义魔术方法 __invoke()里处理事件逻辑
```php
       'listener' => [
           ServerEvent::BEFORE_ADDED_EVENT => 'EventHandlerController',
           ...
       ], 
```
- 事件处理处理类
> 该类需要继承 [EventHandlerInterface](src/Contract/EventHandlerInterface.php)
```php
       'listener' => [
           ServerEvent::BEFORE_ADDED_EVENT => 'EventHandlerController',
           ...
       ], 
```

### <a name="exceptionHandler">exceptionHandler配置</a>
 >exceptionHandler配置为键为指定异常值为异常处理类的数组
 ####指定异常
 >[可定义的异常](https://github.com/Jeruier/swozr-taskr/tree/master/src/Exception)
 ####异常处理类
 > 需要实现[ExceptionHandlerInterface](src/Contract/ExceptionHandlerInterface.php)接口
####配置自定义异常处理类
 ```php
    'exceptionHandler' => [
        'SwozrException' => 'SwozrExceptionHandler',
        'ServerException' => 'ServerExceptionHandler',
        ...
    ]
```

### <a name="crontabs">crontabs配置</a>
>项目有定时业务需求的时候定义crontabs数组，crontabs数组为键为任务格式值为继承[BaseTask](src/Base/BaseTask.php)的类,必须定义静态变量$cron( Crontab 表达式，支持到秒)
Cron格式说明
```php
*    *    *    *    *    *
-    -    -    -    -    -
|    |    |    |    |    |
|    |    |    |    |    +----- day of week (0 - 6) (Sunday=0)
|    |    |    |    +----- month (1 - 12)
|    |    |    +------- day of month (1 - 31)
|    |    +--------- hour (0 - 23)
|    +----------- min (0 - 59)
+------------- sec (0-59)
```
示例：
- `* * * * * *` 表示每秒执行一次。
- `0 * * * * *` 表示每分钟的第0秒执行一次，即每分钟执行一次。
- `0 0 * * * *` 表示每小时的0分0秒执行一次，即每小时执行一次。
- `0/10 * * * * *` 表示每分钟的第0秒开始每10秒执行一次。
- `10-20 * * * * *` 表示每分钟的第10-20秒执行一次。
- `10,20,30 * * * * *` 表示每分钟的第10,20,30秒各执行一次。

配置定时任务
```php
    'crontabs' => [
        CrontabTaskHandle::class,
        ...
    ]
```


### <a name="rabbmitMqs">配置rabbmitMqs任务</a>
>需要装php扩展:`amqp`

配置项

>带`*` 为必须配置项,可以配置多项（以多维数组的形式）

- `calss` 执行rabbmitMq任务的类，必须继承
[BaseTask](src/Base/BaseTask.php)
且实现[TaskConsume](https://github.com/Jeruier/swozr-taskr/tree/master/src/Contract/TaskConsume.php)接口
- `*host` rabbmitMq地址
- `port` rabbmitMq端口，默认值`5672`
- `username` rabbmitMq用户名，默认值`guest`
- `password` rabbmitMq密码，默认值`guest`
- `*exchange_name` rabbmitMq交换机名称
- `*queue_name` rabbmitMq队列名称
- `routing_key` rabbmitMq路由名称

配置一项rabbmit任务
```php
    'rabbmitMqs' => [
        'class' => MqTaskHandleOne::class,
        'host' => '192.168.99.100',
        'port' => 5672
        'username' => 'guest',
        'password' => 'guest',
        'exchange_name' => 'exchange1',
        'queue_name' => 'queue1',
        'routing_key' => 'routing1',
    ],
```
配置多项rabbmit任务
```php
    'rabbmitMqs' => [
        [        
            'class' => MqTaskHandleOne::class,
             'host' => '192.168.99.100',
             'port' => 5672
             'username' => 'guest',
             'password' => 'guest',
             'exchange_name' => 'exchange1',
             'queue_name' => 'queue1',
             'routing_key' => 'routing1',
         ],
         [        
             'class' => MqTaskHandleTwo::class,
              'host' => '192.168.99.100',
              'port' => 5672
              'username' => 'guest',
              'password' => 'guest',
              'exchange_name' => 'exchange1',
              'queue_name' => 'queue2',
              'routing_key' => 'routing2',
          ],
          ...
    ],
```

## 声明任务
>定义一个任务类（必须继承[BaseTask](src/Base/BaseTask.php)
且实现[TaskConsume](https://github.com/Jeruier/swozr-taskr/tree/master/src/Contract/TaskConsume.php)接口,除定时任务和rabbmitMq任务的其他任务还要实现
[TaskNotice](https://github.com/Jeruier/swozr-taskr/tree/master/src/Contract/TaskNotice.php)接口）

```php
use Swozr\Taskr\Server\Base\BaseTask;
use Swozr\Taskr\Server\Contract\TaskConsume;
use Swozr\Taskr\Server\Contract\TaskNotice;

class TaskHandleTest extends BaseTask implements TaskNotice,TaskConsume
{
    /**
     * 任务投递失败
     * @param array $data 投递的数据
     * @param int $delay 延迟执行毫秒数
     * @param string $failMsg 任务发布失败原因
     * @return mixed
     */
    public static function pushFailure(array $data, int $delay, string $failMsg)
    {
        // TODO: Implement pushFailure() method.
    }

    /**
     *任务已投递
     * @return mixed
     */
    public function pushed()
    {
        // TODO: Implement pushed() method.
    }

    /**
     * 消费任务
     * @return mixed
     */
    public function consume(): string
    {
        // TODO: Implement consume() method.
    }

    /**
     * 任务完成
     * @return mixed
     */
    public function finished()
    {
        // TODO: Implement finished() method.
    }
}
```
>当配置为定时任务时需要继承静态属性进行配置<a href="#crontabs">$cron</a>

```php
    public static $cron = '0/3 * * * * *';
```

可调用的类方法
- `getData()` 获取投递的数据
- `getTaskType()` 获取投递任务类型 
    - `BaseTask::TYPE_ASYNC` 异步任务（默认投递类型）
    - `BaseTask::TYPE_DELAY` 延迟任务
    - `BaseTask::TYPE_CRONTAB` 定时任务
- `getDelay()` 获取延迟时间（当投递类型为延迟任务时）
- `getTaskId()` 获取投递任务成功swoole返回的task_id
- `getTaskSign()` 任务服务的标识，和taskid合成唯一任务
- `getTaskName()` 获取任务名称


可实现的方法
- `setTaskName()` 可设置自定义任务名称，（非必须）默认值`类ShortName`

需要实现的[TaskNotice](https://github.com/Jeruier/swozr-taskr/tree/master/src/Contract/TaskNotice.php)接口
- `pushFailure(array $data, int $delay, string $failMsg)`  服务投递过程投递失败时触发
    - `$data` 投递的数据
    - `$delay` 延迟时间
    - `$failMsg` 投递失败原因
- `pushed()` 任务投递成功时触发
- `finished()` 任务消费完成触发

需要实现的实现[TaskConsume](https://github.com/Jeruier/swozr-taskr/tree/master/src/Contract/TaskConsume.php)接口
- `consume()` 任务消费逻辑处理


## 任务投递
>`TaskTest`为声明的任务类，默认投递地址0.0.0.0，端口9501

TaskTest::publish(array $data, ...$varParams)
```php
    $data = [
        'param1' => 'val1',
        'param2' => 'val2',
    ];
    //使用默认地址0.0.0.0端口为9501
    TaskTest::publish($data);  //异步任务
    TaskTest::publish($data, 5000);  //延迟任务
    
    //自定义Taskr client投递任务（自定义地址、端口）
    $taskrClientObj = TaskrClient::getInstance([
        'host' => '0.0.0.0',
        'port' => 9501,
        'timeout' => 1
     ]);
     TaskTest::publish($data, $taskrClientObj);  //异步任务
     TaskTest::publish($data, $taskrClientObj, 5000);  //延迟任务
```

- `$data` 需要投递的数据
- `$varParams` 为可选参数
    - `int $delay` 如需投递延迟任务时传入(单位：ms)
    - [TaskrClient](src/Tools/TaskrClient.php) `$taskrClientObj` 
    如需自定义投递地址及端口需定义发送客户端（地址端口与Taskr配置的服务地址端口需相同）

Taskr Client 发布任务的客户端
>配置客户端

- 方式一
```php
    $taskrClientObj = TaskrClient::getInstance([
        'host' => '0.0.0.0',
        'port' => 9501,
        'timeout' => 1
     ]);
```
- 方式二

```php
    $taskrClientObj = TaskrClient::getInstance();
    $taskrClientObj->setHost('0.0.0.0');
    $taskrClientObj->setPort(9501);
    $taskrClientObj->setTimeout(1);
```



## testing

* [testing](https://github.com/Jeruier/swozr-taskr/tree/master/testing)

## Yii2框架使用示例
>其他php框架使用方法类似

### 使用Taskr服务

应用组件的方式配置
```php
return [
    // ...
    'components' => [
        // ...
        'taskr' => [
            [
                'class' => \Swozr\Taskr\Server\TaskrEngine::class,
                'host' => '0.0.0.0',
                'port' => '9501',
                'pidName' => 'swozr-taskr',
                'MQProcessMum' => 1,
                'debug' => true,
                'setting' => [
                    'worker_num' => 1,
                    'task_worker_num' => 2,
                    'daemonize' => false
                ],
                'exceptionHandler' => [
                    \Swozr\Taskr\Server\Exception\TaskException::class => \SwozrTest\Taskr\Server\ExceptionHandler\TaskExceptionHandler::class
                ],
                'crontabs' => [
                    \SwozrTest\Taskr\Server\Tasks\TaskHandleTest::class
                ],
                'listener' => [
                    \Swozr\Taskr\Server\Event\ServerEvent::TASK_PUSHED => \SwozrTest\Taskr\Server\Listener\TestHandleListener::class,
                    \Swozr\Taskr\Server\Event\ServerEvent::TASK_CONSUME => \SwozrTest\Taskr\Server\Listener\TestHandleListener::class
                ],
                'rabbmitMqs' => [
                    'class' => \SwozrTest\Taskr\Server\Tasks\TaskHandleTest::class,
                    'host' => '192.168.99.100',
                    'exchange_name' => 'a',
                    'queue_name' => 'a',
                    'routing_key' => 'c',
                ],
            ]
        ],
    ],
    // ...
];
```
之后你就可以通过语句 Yii::$app->taskr 来使用taskr服务了。

创建的控制台命令
* 配置应用组件的方式
```php
namespace app\commands;

use yii\console\Controller;

class TaskrController extends Controller
{
    private $taskr;
    
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->taskr = Yii::$app->taskr;
    }
    
    /**
     * start taskr server
     */
    public function actionStart()
    {
        $this->taskr->start();
    }
    
    /**
     * stop taskr server
     */
    public function actionStop()
    {
        $this->taskr->stop();
    }
    
    /**
     * taskr server status
     */
    public function actionStatus()
    {
        $this->taskr->status();
    }
  
    /**
     * @param $type 当为-o则只reload task worker进程，默认重启所有
     * reload taskr server
     */    
    public function actionReload($type = '')
    {
        $onlyTaskWorker = '-o' == $type ? true : false;
        $this->taskr->reload();
    }  
    
    /**
     * restart taskr server
     */
    public function actionRestart()
    {
        $this->taskr->restart();
    }  
}
```
* 直接类调用的方式
```php
namespace app\commands;

use yii\console\Controller;
use Swozr\Taskr\Server\TaskrEngine;

class TaskrController extends Controller
{
    private $taskr;
    
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->taskr = new TaskrEngine([
            'host' => '0.0.0.0',
            'port' => '9501',
            'pidName' => 'swozr-taskr',
            'MQProcessMum' => 1,
            'debug' => true,
            'setting' => [
                'worker_num' => 1,
                'task_worker_num' => 2,
                'daemonize' => false
            ],
            'exceptionHandler' => [
                \Swozr\Taskr\Server\Exception\TaskException::class => \SwozrTest\Taskr\Server\ExceptionHandler\TaskExceptionHandler::class
            ],
            'crontabs' => [
                \SwozrTest\Taskr\Server\Tasks\TaskHandleTest::class
            ],
            'listener' => [
                \Swozr\Taskr\Server\Event\ServerEvent::TASK_PUSHED => \SwozrTest\Taskr\Server\Listener\TestHandleListener::class,
                \Swozr\Taskr\Server\Event\ServerEvent::TASK_CONSUME => \SwozrTest\Taskr\Server\Listener\TestHandleListener::class
            ],
            'rabbmitMqs' => [
                'class' => \SwozrTest\Taskr\Server\Tasks\TaskHandleTest::class,
                'host' => '192.168.99.100',
                'exchange_name' => 'a',
                'queue_name' => 'a',
                'routing_key' => 'c',
            ],
        ]);
    }
    
    /**
     * start taskr server
     */
    public function actionStart()
    {
        $this->taskr->start();
    }
    
    /**
     * stop taskr server
     */
    public function actionStop()
    {
        $this->taskr->stop();
    }
    
    /**
     * taskr server status
     */
    public function actionStatus()
    {
        $this->taskr->status();
    }
  
    /**
     * @param $type 当为-o则只reload task worker进程，默认重启所有
     * reload taskr server
     */    
    public function actionReload($type = '')
    {
        $onlyTaskWorker = '-o' == $type ? true : false;
        $this->taskr->reload();
    }  
    
    /**
     * restart taskr server
     */
    public function actionRestart()
    {
        $this->taskr->restart();
    }  
}
```

运行该命令

* 开启taskr服务
```bash
    php yii taskr/start
```
* 停止taskr服务
```bash
    php yii taskr/stop
```
* 查看taskr服务状态
```bash
    php yii taskr/status
```

* reload taskr服务
```bash
    php yii taskr/reload
    
    //只reload task worker进程
    php yii taskr/reload -o
```

* 重启taskr服务
```bash
    php yii taskr/restart
```