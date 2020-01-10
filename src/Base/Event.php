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
     * @var string 事件名称
     */
    private $name;

    /**
     * @var array 事件参数
     */
    private $params;

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
        if (!$params){
            return false;
        }
        return $this->params = $params;
    }

    /**
     * @return array
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
}