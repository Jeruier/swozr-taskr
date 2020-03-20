<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/6
 * Time: 18:01
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Contract\TaskNotice;
use Swozr\Taskr\Server\Event\ServerEvent;
use Swozr\Taskr\Server\Exception\TaskException;
use Swozr\Taskr\Server\Helper\Packet;
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
            $result = $this->handle($class, $data, $attributes);
            return Packet::packResponse($result);
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
        /**@var BaseTask|\Swozr\Taskr\Server\Contract\TaskConsume|TaskNotice $taskObj * */
        $taskObj = new $class();

        //初始化属性值 $taskType、$taskId、$srcWorkerId
        foreach ($attributes as $attribute => $val) {
            $taskObj->$attribute = $val;
        }
        $taskObj->setData($data);

        /**@var \Swozr\Taskr\Server\Contract\SpecialTask $runClassName**/
        [, $runClass] = TaskBuilder::SPECIAL_TASKS_DEPLOY[$taskObj->getTaskType()] ?? [null, null];
        if ($runClass){
            //定制任务handle
            return $runClass::handle($taskObj);
        }

        if ($taskObj instanceof TaskNotice){
            //task pushed event
            Swozr::trigger(new Event(ServerEvent::TASK_PUSHED));
            $taskObj->pushed();   //触发任务已投递（通知）
        }

        //trigger consume event
        self::triggerConsumeEvent($taskObj);

        $result = $taskObj->consume();

        if ($taskObj instanceof TaskNotice){
            $taskObj->finished();  //通知任务消费完成
        }

        return $result;
    }

    /**
     * 触发任务消费事件
     * @param BaseTask $taskObj
     */
    public static function triggerConsumeEvent(BaseTask $taskObj){
        //task consume event
        Swozr::trigger(new Event(ServerEvent::TASK_CONSUME), null,[
            Event::MESSAGE => "begin consume {$taskObj->getTaskType()} task...",
            Event::DATA => $taskObj->getData()
        ]);
    }
}