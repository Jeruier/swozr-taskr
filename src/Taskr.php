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

class Taskr
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
    public $pidName = 'swozr';

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
        if ($config) {
            foreach ($config as $key => $val) {
                if (!property_exists($this, $key)) continue;
                $this->$key = $val;
            }
        }

        $this->server = new Server();
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

        //添加监听者
        foreach ($this->listener as $eventName => $definition) {
            ListenerRegister::addListener($eventName, $definition);
        }

        //添加异常处理者
        if ($this->exceptionHandler) {
            $execptionManager = new ExceptionManager();
            foreach ($this->exceptionHandler as $exceptionClass => $handlerClass) {
                $execptionManager->addHandler($exceptionClass, $handlerClass);
            }
            $this->server->setExecptionManager($execptionManager);
        }
    }

    /**
     * 开启taskr服务
     * @throws Exception\ServerException
     * @throws \ReflectionException
     */
    public function run()
    {
        $this->beforeRun();
        $this->server->start();
    }
}