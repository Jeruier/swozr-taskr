<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/23
 * Time: 18:26
 */

namespace Swozr\Taskr\Server;

use Swoole\Process;
use Swoole\Server as SwooleServer;
use Swozr\Taskr\Server\Base\Event;
use Swozr\Taskr\Server\Base\BaseTask;
use Swozr\Taskr\Server\Base\EventManager;
use Swozr\Taskr\Server\Base\ExceptionManager;
use Swozr\Taskr\Server\Base\ListenerRegister;
use Swozr\Taskr\Server\Base\TaskBuilder;
use Swozr\Taskr\Server\Base\TaskDispatcher;
use Swozr\Taskr\Server\Event\ServerEvent;
use Swozr\Taskr\Server\Event\SwooleEvent;
use Swozr\Taskr\Server\Exception\ServerException;
use Swozr\Taskr\Server\Helper\Packet;
use Swozr\Taskr\Server\RabbmitMq\MqRegister;
use Swozr\Taskr\Server\Tools\OutputStyle\Console;
use Swozr\Taskr\Server\Tools\OutputStyle\Output;

class Server
{
    const ROLE_WORK_PROCESS_WORKER = 'worker';  //Worker进程

    const ROLE_WORK_PROCESS_TASK = 'task';  //Task进程

    // Swoole mode list
    const MODE_LIST = [
        SWOOLE_BASE    => 'Base',
        SWOOLE_PROCESS => 'Process',
    ];

    // Swoole socket type list
    const TYPE_LIST = [
        // SWOOLE_SOCK_TCP | SWOOLE_SSL = 513
        513                     => 'TCP & SSL',
        // SWOOLE_SOCK_TCP6 | SWOOLE_SSL = 515
        515                     => 'TCP6 & SSL',
        // Normal
        SWOOLE_SOCK_TCP         => 'TCP',
        SWOOLE_SOCK_TCP6        => 'TCP6',
        SWOOLE_SOCK_UDP         => 'UDP',
        SWOOLE_SOCK_UDP6        => 'UDP6',
        SWOOLE_SOCK_UNIX_DGRAM  => 'UNIX DGRAM',
        SWOOLE_SOCK_UNIX_STREAM => 'UNIX STREAM',
    ];

    /**
     * @var Server
     */
    private static $server;

    /**
     * Default host address
     *
     * @var string
     */
    protected $host = '0.0.0.0';

    /**
     * Default port
     *
     * @var int
     */
    protected $port = 9501;

    /**
     * Default mode type
     *
     * @var int
     */
    protected $mode = SWOOLE_PROCESS;

    /**
     * Default socket type
     *
     * @var int
     */
    protected $type = SWOOLE_SOCK_TCP;

    /**
     * Server setting for swoole. (@see swooleServer->setting)
     *
     * @link https://wiki.swoole.com/wiki/page/274.html
     * @var array
     */
    protected $setting = [];

    /**
     * Swoole Server
     * @var SwooleServer
     */
    protected $swooleServer;

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
    protected $on = [];

    /**
     * 用于进程名称
     * @var string
     */
    protected $pidName = 'swozr-taskr';

    /**
     * pid file 内容 matsterPid,managerPid
     * @var string
     */
    protected $pidFile = '/tmp/swozr.pid';

    /**
     * master process pid
     * @var null
     */
    protected $masterPid = null;

    /**
     * manager process pid
     * @var null
     */
    protected $managerPid = null;

    /**
     * log fole
     * @var string
     */
    protected $logFile = '/tmp/swoole.log';

    /**
     * @var TaskDispatcher
     */
    protected $taskDispatcher;

    /**
     * @var string
     */
    protected $sign;

    /**
     * 异常管理器
     * @var ExceptionManager
     */
    public $execptionManager;

    /**
     * 命令行样式化输出
     * @var Output
     */
    public $output;

    /**
     * 是否调试模式运行
     * @var bool
     */
    public $debug = true;

    /**
     * Server constructor
     */
    public function __construct()
    {
        //init
        $this->init();
    }

    private function init()
    {
        $this->setting = $this->defaultSetting();
        $this->sign = uniqid();
        $this->execptionManager = new ExceptionManager();  //异常处理管理
        $this->output = new Output(); //样式console输出
    }

    /**
     * 配置异常处理管理器
     * @param ExceptionManager $em
     */
    public function setExecptionManager(ExceptionManager $em)
    {
        $this->execptionManager = $em;
    }

    /**
     * 设置运行时的各项参数
     * @param $setting
     */
    public function setSetting(array $setting)
    {
        $this->setting = array_merge($this->setting, $setting);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * set host
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * swt port
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getModeName(): string
    {
        return self::MODE_LIST[$this->mode] ?? 'Unknown';
    }

    /**
     * set mode
     * @param int $mode
     */
    public function setMode(int $mode)
    {
        if (!in_array($mode, [SWOOLE_PROCESS, SWOOLE_BASE])) {
            throw new \InvalidArgumentException('invalid server mode value: ' . $mode);
        }
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * set type
     * @param int $type
     */
    public function setType(int $type)
    {
        if (!isset(self::TYPE_LIST[$type])) {
            throw new \InvalidArgumentException('invalid server type value: ' . $type);
        }
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return self::TYPE_LIST[$this->type] ?? 'Unknown';
    }

    /**
     * @return string
     */
    public function getPidName()
    {
        return $this->pidName;
    }

    /**
     * set pid name
     * @param $pidName
     */
    public function setPidName(string $pidName)
    {
        $pidName && $this->pidName = $pidName;
    }

    /**
     * @return string
     */
    public function getPidFile()
    {
        return $this->pidFile;
    }

    /**
     * set pid file
     * @param string $pidFile
     */
    public function setPidFile(string $pidFile)
    {
        $pidFile && $this->pidFile = $pidFile;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * set log file
     * @param string $logFile
     */
    public function setLogfile(string $logFile)
    {
        $logFile && $this->logFile = $logFile;
    }

    /**
     * master pid
     * @return null
     */
    public function getMasterPid()
    {
        return $this->masterPid;
    }

    /**
     * manager pid
     * @return null
     */
    public function getManagerPid()
    {
        return $this->managerPid;
    }

    /**
     * sign
     * @return string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @param bool $debug
     * @return $this
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * 是否守护进程运行
     * @return bool
     */
    public function isDaemon(): bool
    {
        return !$this->setting['daemonize'] ? false : true;
    }

    /**
     * 是否调运行
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * 是否协程任务
     * @return bool
     */
    public function isCoroutineTask(): bool
    {
        return $this->setting['task_enable_coroutine'] ?? false;
    }

    /**
     * 运行时返回Pid
     * check if the server is running
     * @return bool|int
     */
    public function isRunning(): bool
    {
        if (!file_exists($this->pidFile)) {
            return false;
        }

        $pids = file_get_contents($this->pidFile);
        [$masterPid, $managerPid] = explode(',', $pids);
        $exists = $masterPid > 1 && Process::kill($masterPid, 0);//$signo=0，可以检测进程是否存在，不会发送信号
        !$exists && @unlink($this->pidFile);

        return $exists;
    }

    /**
     * 根据pid文件获取pid信息
     *
     * @return array
     */
    public function getPidsFormFile():array
    {
        if (!file_exists($this->pidFile)) {
            return [null, null];
        }

        $pids = file_get_contents($this->pidFile);
        return explode(',', $pids);
    }

    /**
     * 获取工作进程角色
     * @param int $workerId
     * @return string
     */
    public function getWorkProcessRole(int $workerId): string
    {
        return $workerId >= $this->setting['worker_num'] ? self::ROLE_WORK_PROCESS_TASK : self::ROLE_WORK_PROCESS_WORKER;
    }

    /**
     * @param Server $server
     */
    public static function setServer(Server $server)
    {
        self::$server = $server;
    }

    /**
     * @return Server
     */
    public static function getServer()
    {
        return self::$server;
    }

    /**
     * @return Server
     */
    public function getSwooleServer()
    {
        return $this->swooleServer;
    }

    /**
     * 添加任务事件
     */
    protected function addTaskEvent()
    {
        $listenerMethod = $this->isCoroutineTask() ? 'onSyncTask' : 'onTask';
        $this->swooleServer->on(SwooleEvent::TASK, [$this, $listenerMethod]);
        $this->swooleServer->on(SwooleEvent::FINISH, [$this, 'onFinish']);
    }

    /**
     * before start
     * @throws Exception\RegisterEventException
     * @throws \ReflectionException
     */
    private function beforeStart()
    {
        //监听者注册
        ListenerRegister::register(EventManager::getInstance());

        //show logo banner
        Swozr::showBanner();

        //服务信息面板
        Output::Panel($this->buildMainServerInfo(), 'Tasker Server Information', [
            'titleStyle' => 'cyan',
        ]);
    }

    /**
     * 开启服务
     * @throws ServerException
     */
    public function start()
    {
        $this->beforeStart();

        $this->swooleServer = new SwooleServer($this->host, $this->port, $this->mode, $this->type);

        //Before setiing
        Swozr::trigger(ServerEvent::BEFORE_SETTING, $this);

        //Set setting
        $this->swooleServer->set($this->setting);

        //Before add event
        Swozr::trigger(ServerEvent::BEFORE_ADDED_EVENT, $this);
        //注册Server的事件回调函数
        $defaultEvents = $this->defaultEvents();

        //添加默认事件
        foreach ($defaultEvents as $name => $listener) {
            $this->swooleServer->on($name, $listener);
        }

        //添加自定义事件
        foreach ($this->on as $name => $listener) {
            if (array_key_exists($name, $defaultEvents) || !isset(SwooleEvent::CUSTOM_EVENTS_MAPPING[$name])) {
                //默认事件或不正规时间不能自定义
                throw new ServerException(sprintf("Cannot customize event %s", $name));
            }

            $listenerInterface = SwooleEvent::CUSTOM_EVENTS_MAPPING[$name];
            if (!($listener instanceof $listenerInterface)) {
                throw new ServerException(sprintf("Swoole %s event listener is not %s", $name, $listenerInterface));
            }
            $this->swooleServer->on($name, [$listener, sprintf("on%s", ucfirst($name))]);
        }

        $this->addTaskEvent(); //add task event

        //After add event
        Swozr::trigger(ServerEvent::AFTER_ADDED_EVENT, $this);

        // Trigger event
        Swozr::trigger(ServerEvent::BEFORE_START, $this);

        self::$server = $this;

        $this->taskDispatcher = new TaskDispatcher(); //任务调度器

        $this->swooleServer->start();
    }

    /**
     * reload
     * @param bool $onlyTaskWorker
     * @return bool
     */
    public function reload(bool $onlyTaskWorker = false): bool
    {
        if (!$this->isRunning()) {
            //未运行
            return false;
        }
        $signal = $onlyTaskWorker ? 12 : 10; //用户信号

        return Process::kill($this->masterPid, $signal);
    }

    /**
     * stop server
     * @return bool
     */
    public function stop(): bool
    {
        if (!$this->isRunning()) {
            return false;
        }

        if (!Process::kill($this->masterPid, 15)) {
            return Console::writef("<success>Send stop signal to the %s(PID:%s) failed!</success>", $this->pidName, $this->masterPid);

        }

        file_exists($this->pidFile) && @unlink($this->pidFile);

        return Console::writef("<success>The %d process stopped!</success>", $this->pidName);
    }

    /**
     * onStart
     * @param SwooleServer $serv
     */
    public function onStart(SwooleServer $serv)
    {
        [$this->masterPid, $this->managerPid] = [$serv->master_pid, $serv->manager_pid];

        //create dir and save pid to file
        Swozr::makeDir(dirname($this->pidFile));
        file_put_contents($this->pidFile, sprintf("%s,%s", $this->masterPid, $this->managerPid));

        //set process title
        Swozr::setProcessName(sprintf("%s master process", $this->pidName));

        //start event
        Swozr::trigger(SwooleEvent::START, $this);

    }

    /**
     * 强制kill进程不会回调onShutdown，如kill -9
     * 需要使用kill -15来发送SIGTREM信号到主进程才能按照正常的流程终止
     * 在命令行中使用Ctrl+C中断程序会立即停止，底层不会回调onShutdown
     * @param SwooleServer $serv
     */
    public function onShutdown(SwooleServer $serv)
    {
        $this->log("Shutdown: pidFile={$this->pidFile}");

        //delete pid file
        file_exists($this->pidFile) && @unlink($this->pidFile);

        //shutdown event
        Swozr::trigger(SwooleEvent::SHUTDOWN, $this);
    }

    /**
     * onManagerStart
     * @param SwooleServer $serv
     */
    public function onManagerStart(SwooleServer $serv)
    {
        // Set process title
        Swozr::setProcessName(sprintf("%s manager process", $this->pidName));

        //manager start event
        Swozr::trigger(SwooleEvent::MANAGER_START, $this);
    }

    /**
     * onManagerStop
     * @param SwooleServer $serv
     */
    public function onManagerStop(SwooleServer $serv)
    {
        //manager stop event
        Swozr::trigger(SwooleEvent::MANAGER_STOP, $this);
    }

    /**
     * onWorkerStart
     * @param SwooleServer $serv
     * @param int $workerId
     */
    public function onWorkerStart(SwooleServer $serv, int $workerId)
    {
        try {
            $processRole = $this->getWorkProcessRole($workerId);
            $this->log("WorkerStart: $processRole process start workerId=$workerId");

            //worker start event
            Swozr::trigger(SwooleEvent::WORKER_START, $serv, compact('workerId'));

            Swozr::setProcessName(sprintf("%s %s process", $this->pidName, $processRole));

            //worker|task start event
            $eventName = $processRole == self::ROLE_WORK_PROCESS_WORKER ? ServerEvent::WORK_PROCESS_START : ServerEvent::TASK_PROCESS_START;
            Swozr::trigger($eventName, $this, compact('workerId'));

            //分配work进程做任务
            TaskBuilder::assign($workerId);
        } catch (\Exception $e) {
            $this->execptionManager->handler($e);
        }
    }

    /**
     * onWorkerStop
     * @param SwooleServer $serv
     * @param int $workerId
     */
    public function onWorkerStop(SwooleServer $serv, int $workerId)
    {
        $this->log("WorkerStop: workerId=$workerId");

        //worker end event
        Swozr::trigger(SwooleEvent::WORKER_STOP, $serv, compact('workerId'));

        //worker|task stop event
        $eventName = $this->getWorkProcessRole($workerId) == self::ROLE_WORK_PROCESS_WORKER ? ServerEvent::WORK_PROCESS_STOP : ServerEvent::TASK_PROCESS_STOP;
        Swozr::trigger($eventName, $this, compact('workerId'));
    }

    /**
     * onWorkerError
     * @param SwooleServer $serv
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function onWorkerError(SwooleServer $serv, int $workerId, int $workerPid, int $exitCode, int $signal)
    {
        $this->log("WorkerError: exitCode=$exitCode, Error worker: workerId=$workerId workerPid=$workerPid signal=$signal");

        //work error event
        $event = new Event(SwooleEvent::WORKER_ERROR, [
            'signal' => $signal,
            'exitCode' => $exitCode,
            'workerPid' => $workerPid,
            'processRole' => $this->getWorkProcessRole($workerId)
        ]);
        Swozr::trigger($event, $this);
    }

    /**
     * onReceive
     * @param SwooleServer $serv
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive(SwooleServer $serv, int $fd, int $reactorId, string $data)
    {
        try {
            $this->log("Receive: conn#{$fd} received client request, begin init context", $data, Swozr::LOG_LEVEL_DEBUG);

            Swozr::trigger(SwooleEvent::RECEIVE, $serv, compact('fd', 'reactorId', 'data'));

            [$class, $data, $taskType, $delay] = Packet::unpackClient($data);

            //投递
            if (BaseTask::TYPE_DELAY == $taskType) {
                //延迟任务
                \Swoole\Timer::after($delay, function () use ($class, $data, $taskType, $delay) {
                    $taskId = $this->puskTask($class, $data, $taskType, $delay);
                });
            } else {
                $taskId = $this->puskTask($class, $data, $taskType, $delay);
            }
        } catch (\Exception $e) {
            $this->execptionManager->handler($e); //execption handler
        }
    }

    /**
     * 推送任务
     * @param $class
     * @param $data
     * @param $taskType
     * @param $delay
     * @return bool|mixed
     */
    private function puskTask($class, $data, $taskType, $delay){
        try{
            return BaseTask::push($class, $data, $taskType, $delay);
        }catch (\Exception $e){
            $this->execptionManager->handler($e); //execption handler
            return false;
        }
    }

    /**
     * 协程任务onSyncTask
     * @param SwooleServer $serv
     * @param SwooleServer\Task $task
     */
    public function onSyncTask(SwooleServer $serv, \Swoole\Server\Task $task)
    {

    }

    /**
     * onTask
     * @param SwooleServer $serv
     * @param int $taskId
     * @param int $srcWorkerId
     * @param string $data
     */
    public function onTask(SwooleServer $serv, int $taskId, int $srcWorkerId, $data)
    {
        try {
            Swozr::trigger(SwooleEvent::TASK, $serv, compact('taskId', 'srcWorkerId', 'data'));

            [$class, $data, $attributes] = Packet::unpack($data);
            $attributes['taskId'] = $taskId;
            $attributes['srcWorkerId'] = $srcWorkerId;

            $result = $this->taskDispatcher->dispatch($class, $data, $attributes);
            return $result; //return数据触发onFinish事件
        } catch (\Exception $e) {
            $this->execptionManager->handler($e); // //execption handler
        }
    }

    /**
     * onFinish
     * @param SwooleServer $serv
     * @param int $taskId
     * @param string $str
     */
    public function onFinish(SwooleServer $serv, int $taskId, string $data)
    {
        Swozr::trigger(SwooleEvent::FINISH, $serv, compact('taskId', 'data'));
    }

    /**
     * Worker进程或者Task进程发送消息触发 $serv->sendMessage()
     * onPipeMessage
     * @param SwooleServer $serv
     * @param int $srcWorkerId
     * @param $message
     */
    public function onPipeMessage(SwooleServer $serv, int $srcWorkerId, $message)
    {
        //@todo 暂无需要
    }

    /**
     * @param string $msg
     * @param $data
     * @param string $type
     */
    public function log(string $msg, $data = '', string $type = Swozr::LOG_LEVEL_INFO)
    {
        if (!$this->debug) {
            return;
        }

        //作色
        if (isset(Console::LOG_LEVEL2TAG[$type])) {
            $type = Output::wrap(strtoupper($type), Console::LOG_LEVEL2TAG[$type]);
        }

        $dataString = is_array($data) ? PHP_EOL . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $data;
        Console::writef('%s [%s] %s %s', date('Y-m-d H:i:s'), $type, trim($msg), $dataString);
    }

    /**
     * 设置Server运行时的各项参数
     * @return array
     */
    protected function defaultSetting(): array
    {
        return [
            'daemonize' => 0,
            'worker_num' => swoole_cpu_num(),
            'task_worker_num' => swoole_cpu_num(),
            'log_file' => $this->logFile
        ];
    }

    /**
     * 默认时间
     * @return array
     */
    public function defaultEvents(): array
    {
        return [
            SwooleEvent::START => [$this, 'onStart'],
            SwooleEvent::SHUTDOWN => [$this, 'onShutdown'],
            SwooleEvent::MANAGER_START => [$this, 'onManagerStart'],
            SwooleEvent::MANAGER_STOP => [$this, 'onManagerStop'],
            SwooleEvent::WORKER_START => [$this, 'onWorkerStart'],
            SwooleEvent::WORKER_STOP => [$this, 'onWorkerStop'],
            SwooleEvent::WORKER_ERROR => [$this, 'onWorkerError'],
            SwooleEvent::RECEIVE => [$this, 'onReceive'],
        ];
    }

    /**
     *
     * @return array
     */
    protected function buildMainServerInfo(): array
    {
        return [
            $this->pidName => [
                'listen' => $this->host . ':' . $this->port,
                'type' => $this->getTypeName(),
                'mode' => $this->getModeName(),
                'worker_num' => $this->setting['worker_num'],
                'task_worker_num' => $this->setting['task_worker_num'],
            ]
        ];
    }
}