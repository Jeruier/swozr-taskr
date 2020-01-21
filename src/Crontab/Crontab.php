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

class Crontab
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
                BaseTask::push($className, [], BaseTask::TYPE_CRONTAB);
            }
        });
    }
}