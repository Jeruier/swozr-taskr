<?php
/**
 * Created by PhpStorm.
 * User: zhangrui
 * Date: 2020/1/21
 * Time: 10:56 AM
 */

namespace Swozr\Taskr\Server\Crontab;


use Swoole\Timer;
use Swozr\Taskr\Server\Base\BaseTask;
use Swozr\Taskr\Server\Base\TaskDispatcher;
use Swozr\Taskr\Server\Contract\SpecialTask;
use Swozr\Taskr\Server\Swozr;

class Crontab implements SpecialTask
{
    /**
     * run Crontab
     */
    public static function run()
    {
        Timer::tick(1000, function (){
            // All task
            $tasks = CrontabRegister::getCronTasks();

            // Push task
            foreach ($tasks as $className) {
                try{
                    BaseTask::push($className, [], BaseTask::TYPE_CRONTAB);
                }catch (\Exception $e){
                    Swozr::server()->execptionManager->handler($e);
                }
            }
        });
    }

    /**
     * @param BaseTask|\Swozr\Taskr\Server\Contract\TaskConsume $taskObj
     * @return mixed
     */
    public static function handle(BaseTask $taskObj)
    {
        //trigger consume event
        TaskDispatcher::triggerConsumeEvent($taskObj);

        return $taskObj->consume();
    }
}