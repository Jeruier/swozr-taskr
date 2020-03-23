<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/20
 * Time: 17:24
 */

namespace Swozr\Taskr\Server;


use Swozr\Taskr\Server\Base\ExceptionManager;
use Swozr\Taskr\Server\Base\ListenerRegister;
use Swozr\Taskr\Server\Crontab\CrontabRegister;
use Swozr\Taskr\Server\Exception\RuntimeException;
use Swozr\Taskr\Server\Processor\CrontabsProcessor;
use Swozr\Taskr\Server\Processor\ExecptionManagerProcessor;
use Swozr\Taskr\Server\Processor\ListenerProcessor;
use Swozr\Taskr\Server\Processor\Processor;
use Swozr\Taskr\Server\Processor\RabbmitMqsProcessor;
use Swozr\Taskr\Server\RabbmitMq\MqRegister;
use Swozr\Taskr\Server\Tools\OutputStyle\Console;

class TaskrEngine
{
    /**
     * Default host address
     *
     * @var string
     */
    public $host = '0.0.0.0';

    /**
     * Default port
     *
     * @var int
     */
    public $port = 9501;

    /**
     * 事件监听者
     * [
     *      eventNmae => [
     *           [EventInterface|[className, staticMethod]|[Object, method]],
     *          ...
     *      ],
     *      ...
     * ]
     * @var array
     */
    public $listener = [];

    /**
     * 自定义异常处理类
     * @var array
     * [
     *  exception class => handler class,
     *  ... ...
     * ]
     */
    public $exceptionHandler = [];

    /**
     * Server event for swoole event
     *
     * @var array
     *
     * @example
     * [
     *     'serverName' => new SwooleEventListener(),
     *     'serverName' => new SwooleEventListener(),
     *     'serverName' => new SwooleEventListener(),
     * ]
     */
    public $on = [];

    /**
     * Server setting for swoole. (@see swooleServer->setting)
     *
     * @link https://wiki.swoole.com/wiki/page/274.html
     * @var array
     */
    public $setting = [];

    /**
     * Default socket type
     *
     * @var int
     */
    public $type = SWOOLE_SOCK_TCP;

    /**
     * 用于进程名称
     * @var string
     */
    public $pidName = 'swozr-taskr';

    /**
     * pid file 内容 matsterPid,managerPid
     * @var string
     */
    public $pidFile = '/tmp/swozr.pid';

    /**
     * log file
     * @var string
     */
    public $logFile = '/tmp/swoole.log';

    /**
     * 是否开启模式运行
     * @var bool
     */
    public $debug = false;

    /**
     * rabbmit需要开启的进程数
     * @var int
     */
    public $MQProcessMum = 1;

    /**
     * 注册定时任务
     * @var array
     */
    public $crontabs = [];

    /**
     * 注册rabbmitMq 任务
     * @var array
     */
    public $rabbmitMqs = [];

    /**
     * swoole server
     * @var Server
     */
    private $server;

    /**
     * 载入配置属性赋值
     * Taskr constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // Check runtime env
        Swozr::checkRuntime();

        // Init Taskr
        $this->init($config);
    }

    /**
     * @param array $config
     */
    private function init(array $config = [])
    {
        if ($config) {
            foreach ($config as $key => $val) {
                if (!property_exists($this, $key)) continue;
                $this->$key = $val;
            }
        }

        $this->server = new Server();
        Swozr::$server = $this->server;
    }

    /**
     * @return Processor[]
     */
    protected function processors(): array
    {
        return [
            new ListenerProcessor($this),
            new ExecptionManagerProcessor($this),
            new CrontabsProcessor($this),
            new RabbmitMqsProcessor($this),
        ];
    }

    /**
     * 配置、注册事件监听者、注册异常处理
     * @throws \ReflectionException
     */
    private function beforeRun()
    {
        //配置server属性
        $reflection = new \ReflectionClass(self::class);
        foreach ($reflection->getProperties() as $reflProperty) {
            if (!$reflProperty->isPublic()) continue;

            $propertyName = $reflProperty->getName();
            $methodName = 'set' . ucfirst($propertyName);
            if (method_exists($this->server, $methodName)) {
                $this->server->$methodName($this->$propertyName);
            }
        }

        //载入
        foreach ($this->processors() as $processor){
            $processor->handle();
        }
    }

    /**
     * 开启taskr服务
     * @throws Exception\ServerException
     * @throws \ReflectionException
     */
    public function start()
    {
        $this->beforeRun();
        $this->server->start();
    }

    /**
     * stop server
     */
    public function stop()
    {
        $this->server->stop();
    }

    /**
     * reload server 热重启
     * @param bool $onlyTaskWorker 是否只重启work进程
     */
    public function reload(bool $onlyTaskWorker = false)
    {
        $this->server->reload($onlyTaskWorker);
    }

    /**
     * 重启服务
     * @throws Exception\ServerException
     * @throws \ReflectionException
     */
    public function restart()
    {
        $this->stop();
        sleep(1);
        $this->start();
    }

    /**
     * server status
     */
    public function status()
    {
        if ($pid = $this->server->isRunning()) {
            //running
            [$masterPid, $managerPid] = $this->server->getPidsFormFile();
            return Console::writef("<success>Taskr Server is Running...</success> (Master PID: <mga>%d</mga>, Manager PID: <mga>%d</mga>)",
                $masterPid,
                $managerPid);
        }

        Console::writeln("<danger>Taskr Server is not Running</danger>" . PHP_EOL);
    }
}