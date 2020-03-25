<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/6
 * Time: 15:44
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Contract\TaskConsume;
use Swozr\Taskr\Server\Contract\TaskNotice;
use Swozr\Taskr\Server\Event\ServerEvent;
use Swozr\Taskr\Server\Exception\TaskException;
use Swozr\Taskr\Server\Helper\Packet;
use Swozr\Taskr\Server\Swozr;
use Swozr\Taskr\Server\Tools\TaskrClient;

class BaseTask
{
    const TYPE_ASYNC = 'async'; //正常异步任务

    const TYPE_DELAY = 'delay'; //延时任务

    const TYPE_CRONTAB = 'corntab'; //定时任务

    const TYPE_RABBMIT_MQ = 'rabbmit_mq'; //定时任务

    /**
     * @var int
     */
    public $taskId;

    /**
     * 来自于哪个worker进程
     * @var int
     */
    public $srcWorkerId;


    /**
     * 任务名称
     * @var string
     */
    protected $taskName;

    /**
     * 任务服务的标识，和taskid合成唯一任务
     * @var string
     */
    protected $taskSign;

    /**
     * 任务类型
     * @var string
     */
    public $taskType;

    /**
     * 延迟投递时间
     * @var int
     */
    public $delay;

    /**
     * task data
     * @var mixed
     */
    private $data;

    /**
     * 定时任务规则
     * @var string
     */
    public static $cron;

    public function __construct()
    {
        $this->taskName = lcfirst((new \ReflectionClass($this))->getShortName());
        $this->taskSign = Swozr::server()->getSign();
    }

    /**
     * 设置自定义任务名称
     * @param string $name
     * @return bool
     */
    public function setTaskName(string $name): bool
    {
        if (!$name) return false;

        return $this->taskName = $name;
    }

    /**
     * get task name
     * @return string
     */
    public function getTaskName(): string
    {
        return $this->taskName;
    }

    /**
     *  get task sign
     * @return string
     */
    public function getTaskSign(): string
    {
        return $this->taskSign;
    }

    /**
     * get task id
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * get task type
     * @return string
     */
    public function getTaskType(): string
    {
        return $this->taskType;
    }

    /**
     * get delay
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * set data
     * @param $data
     */
    public function setData($data){
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    final public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $msg
     * @throws TaskException
     */
    final public static function Error(string $msg)
    {
        throw new TaskException($msg);
    }

    /**
     * @param array $data
     * @param mixed ...$varParams 可传入两个参数，参数类型为int的为$delay，参数类型为object的为$taskrClientObj的值
     * @return bool
     * @throws TaskException
     * @throws \Swozr\Taskr\Server\Exception\ClientException
     */
    final public static function publish(array $data, ...$varParams)
    {
        if (count($varParams) > 2) {
            self::Error('Up to three parameters passed in');
        }

        $delay = 0;
        $taskrClientObj = null;
        foreach ($varParams as $param) {
            is_numeric($param) && $delay = $param;
            is_object($param) && $taskrClientObj = $param;
        }
        $taskType = $delay > 0 ? self::TYPE_DELAY : self::TYPE_ASYNC;
        if (self::TYPE_DELAY == $taskType && !DelayExpression::parse($delay)) {
            //延迟任务延迟发布
            self::Error(sprintf("Task error : Must be a number from 1 to 86400000 in milliseconds (delay = %s)", $delay));
        }

        $taskrClientObj = $taskrClientObj ? $taskrClientObj : TaskrClient::getInstance();  //未设置使用默认配置
        if (!is_object($taskrClientObj) || !($taskrClientObj instanceof TaskrClient)) {
            self::Error(sprintf("taskrClientObj must be instanceof %s", TaskrClient::class));
        }

        $class = get_called_class();

        //校验子类是实现指定接口
        $subReflection = new \ReflectionClass($class);
        if (!$subReflection->implementsInterface(TaskNotice::class) || !$subReflection->implementsInterface(TaskConsume::class)) {
            self::Error(sprintf("(class = %s) must implement interface %s,%s", $class, TaskNotice::class, TaskConsume::class));
        }

        $str = Packet::packClinet($class, $data, $taskType, $delay);
        $taskrClientObj->send($str);

        return true;
    }

    /**
     * @param string $class
     * @param array $data
     * @param string $taskType
     * @param int $delay
     * @param int $dstWorkerId
     * @return mixed
     * @throws TaskException
     */
    final public static function push(
        string $class,
        array $data = [],
        string $taskType = self::TYPE_ASYNC,
        int $delay = 0,
        int $dstWorkerId = -1,
        callable $fallback = null
    )
    {
        try {
            if (!in_array($taskType,
                [
                    self::TYPE_ASYNC,
                    self::TYPE_DELAY,
                    self::TYPE_CRONTAB,
                    self::TYPE_RABBMIT_MQ
                ])) {
                //只能发布异步或延迟任务
                self::Error(sprintf("Task error : tasktype is %s or %s or %s or %s (tasktype = %s)", self::TYPE_ASYNC, self::TYPE_DELAY, self::TYPE_CRONTAB, self::TYPE_RABBMIT_MQ, $taskType));
            }

            $attributes = [
                'taskType' => $taskType,
                'delay' => $delay,
                'data' => $data
            ];
            $str = Packet::pack($class, $data, $attributes);

            if (self::TYPE_DELAY == $taskType && !DelayExpression::parse($delay)) {
                //延迟任务延迟发布
                self::Error(sprintf("Task error : Must be a number from 1 to 86400000 in milliseconds (delay = %s)", $delay));
            }

            $taskId = Swozr::swooleServer()->task($str, $dstWorkerId, $fallback);
            if ($taskId === false) {
                self::Error(sprintf("Task error class=%d", $class));
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            //task push fail event
            Swozr::trigger(new Event(ServerEvent::TASK_PUSH_FAIL, [
                Event::MESSAGE => $msg,
                Event::DATA => [
                    'data' => $data,
                    'delay' => $delay
                ],
            ]));
            /* @var TaskNotice $class */
            $class::pushFailure($data, $delay, $msg); //任务投递失败
            self::Error($msg);
        }

        return $taskId;
    }
}