<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/24
 * Time: 10:30
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Contract\EventInterface;

class Event implements EventInterface
{
    /**
     * 事件里面的消息内容
     */
    const MESSAGE = 'msg';

    /**
     * 事件里面的data数据
     */
    const DATA = 'data';

    const LOG_PREFIX_FIELDS = ['taskId', 'workerId', 'fd', 'reactorId', 'srcWorkerId', 'signal', 'exitCode', 'processRole', 'workerPid'];
    /**
     * @var string 事件名称
     */
    private $name;

    /**
     * @var array 事件参数
     */
    private $params = [];

    /**
     * @var mixed
     */
    private $target;

    /**
     * BaseEvent constructor.
     * @param string $name
     * @param array $params
     */
    public function __construct(string $name = '', array $params = [])
    {
        if ($name) {
            $this->name = $name;
        }

        if ($params) {
            $this->params = $params;
        }
    }

    public function setName(string $name)
    {
        return $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setParams(array $params)
    {
        if (!$params) {
            return false;
        }
        return $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getParam($key, $dafault = null)
    {
        return $this->params[$key] ?? $dafault;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * 获取事件中的data
     * @return mixed
     */
    public function getData()
    {
        return $this->getParam(self::DATA);
    }

    /**
     * 获取事件中的消息
     * @return mixed
     */
    public function getMessage()
    {
        return $this->getParam(self::MESSAGE);
    }
}