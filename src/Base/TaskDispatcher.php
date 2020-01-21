<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/6
 * Time: 18:01
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Event\ServerEvent;
use Swozr\Taskr\Server\Exception\TaskException;
use Swozr\Taskr\Server\Swozr;

class TaskDispatcher
{
    /**
     * @param string $class
     * @param array $data
     * @param array $attributes
     * @return string
     */
    public function dispatch(string $class, array $data = [], array $attributes = [])
    {
        try {
            $result = $this->handle($class, $data, $attributes);
            $response = BaseTask::packResponse($result);
        } catch (\Exception $e) {
            $response = BaseTask::packResponse(null, $e->getCode(), $e->getMessage());
        }

        return $response;
    }

    /**
     * 执行消费
     * @param string $class
     * @param array $data
     * @param array $attributes
     * @return bool|string
     * @throws TaskException
     */
    private function handle(string $class, array $data, array $attributes)
    {
        /**@var BaseTask $taskObject **/
        $taskObject = new $class();
        if (!($taskObject instanceof BaseTask)) {
            throw new TaskException(sprintf("class %s must be instanceof %s", $class, BaseTask::class));
        }

        //初始化属性值 $taskType、$taskId、$srcWorkerId
        foreach ($attributes as $attribute => $val) {
            $taskObject->$attribute = $val;
        }

        //task pushed event
        Swozr::trigger(new Event(ServerEvent::TASK_PUSHED));
        $taskObject->pushed();   //触发任务已投递

        //task consume event
        Swozr::trigger(new Event(ServerEvent::TASK_CONSUME), [
            Event::DATA => $data
        ]);
        $result = $taskObject->consume();

        $taskObject->finished();  //任务消费完成

        return $result;
    }
}