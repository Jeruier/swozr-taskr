<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/6
 * Time: 15:44
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Exception\TaskException;
use Swozr\Taskr\Server\Helper\JsonHelper;
use Swozr\Taskr\Server\Swozr;
use Swozr\Taskr\Server\Tools\TaskrClient;

abstract class BaseTask
{
    const TYPE_ASYNC = 'async'; //正常异步任务
    const TYPE_DELAY = 'delay'; //延时任务
    const TYPE_CRONTAB = 'timed'; //定时任务

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
     * @var static
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
    protected $data;

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
     * @param string $msg
     * @throws TaskException
     */
    public static function Error(string $msg)
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
    public static function publish(array $data, ...$varParams)
    {
        if (count($varParams) > 2) {
            self::Error('Up to three parameters passed in');
        }

        $delay = 0;
        $taskrClientObj = null;
        foreach ($varParams as $param) {
            is_integer($param) && $delay = $param;
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
        $str = self::packClinet($class, $data, $taskType, $delay);
        $taskrClientObj->send($str);

        return true;
    }

    /**
     * @param string $class
     * @param array $data
     * @param string $taskType
     * @param int $delay
     * @param int $dstWorkerId
     * @param callable|null $fallback
     * @return mixed
     * @throws TaskException
     */
    public static function push(
        string $class,
        array $data = [],
        string $taskType = self::TYPE_ASYNC,
        int $delay = 0,
        int $dstWorkerId = -1,
        callable $fallback = null
    )
    {
        try {
            if (!in_array($taskType, [self::TYPE_ASYNC, self::TYPE_DELAY, self::TYPE_CRONTAB])) {
                //只能发布异步或延迟任务
                self::Error(sprintf("Task error : tasktype is %s or %s or %s (tasktype = %s)", self::TYPE_ASYNC, self::TYPE_DELAY, self::TYPE_CRONTAB, $taskType));
            }

            $attributes = [
                'taskType' => $taskType,
                'delay' => $delay
            ];
            $str = self::pack($class, $data, $attributes);

            if (self::TYPE_DELAY == $taskType && !DelayExpression::parse($delay)) {
                //延迟任务延迟发布
                self::Error(sprintf("Task error : Must be a number from 1 to 86400000 in milliseconds (delay = %s)", $delay));
            }

            $taskId = Swozr::swooleServer()->task($str, $dstWorkerId, $fallback);
            if ($taskId === false) {
                self::Error(sprintf("Task error class=%d", $class));
            }
        } catch (\Exception $e) {
            /* @var BaseTask $class */
            $class::pushFailure($data, $delay, $e->getMessage()); //任务投递失败
            self::Error($e->getMessage());
        }

        return $taskId;
    }

    /**
     * 任务投递失败
     * @param array $data 投递的数据
     * @param int $delay 延迟执行毫秒数
     * @param string $failMsg 任务发布失败原因
     * @return mixed
     */
    abstract public static function pushFailure(array $data, int $delay, string $failMsg);

    /**
     *任务已投递
     * @return bool
     */
    abstract public function pushed();

    /**
     * 消费任务
     * @param $data
     * @return bool
     */
    abstract public function consume($data): string;

    /**
     * 标记任务完成
     * @return bool
     */
    abstract public function finished();

    /**
     * server层打包数据
     * @param string $class
     * @param array $data
     * @param array $attributes
     * @return string
     */
    public static function pack(string $class, array $data = [], array $attributes = [])
    {
        $data = [
            'class' => $class,
            'data' => $data,
            'attributes' => $attributes,
        ];

        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 打包client发送给server的数据
     * @param string $class
     * @param array $data
     * @param string $taskType
     * @param int $delay
     * @return string
     */
    private static function packClinet(string $class, array $data = [], string $taskType = BaseTask::TYPE_ASYNC, int $delay = 0)
    {
        $data = [
            'class' => $class,
            'data' => $data,
            'taskType' => $taskType,
            'delay' => $delay,
        ];

        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 解server层打包的数据
     * @param string $str
     * @return array
     * @throws TaskException
     */
    public static function unpack(string $str)
    {
        $strdata = JsonHelper::decode($str, true);

        $class = $strdata['class'] ?? '';
        $data = $strdata['data'] ?? [];
        $attributes = $strdata['attributes'] ?? [];

        return [$class, $data, $attributes];
    }

    /**
     * server端解client端发送过来的数据
     * @param string $str
     * @return array
     */
    public static function unpackClient(string $str)
    {
        $strdata = JsonHelper::decode($str, true);

        $class = $strdata['class'] ?? '';
        $data = $strdata['data'] ?? [];
        $taskType = $strdata['taskType'] ?? BaseTask::TYPE_ASYNC;
        $delay = $strdata['delay'] ?? 0;

        return [$class, $data, $taskType, $delay];
    }

    /**
     * 打包task return 内容
     * @param $result
     * @param int|null $errorCode
     * @param string $errorMessage
     * @return string
     */
    public static function packResponse($result, int $errorCode = null, string $errorMessage = '')
    {
        if ($errorCode !== null) {
            $data = [
                'code' => $errorCode,
                'message' => $errorMessage
            ];
        } else {
            $data['result'] = $result;
        }

        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 解包task return 内容
     * @param string $str
     * @return array
     */
    public static function unpackResponse(string $str)
    {
        $data = JsonHelper::decode($str, true);

        if (array_key_exists('result', $data)) {
            return [$data['result'], null, ''];
        }

        $errorCode = $data['code'] ?? 0;
        $errorMessage = $data['message'] ?? '';

        return [null, $errorCode, $errorMessage];
    }
}