<?php
/**
 * Created by PhpStorm.
 * User: zhangrui
 * Date: 2020/1/21
 * Time: 1:27 PM
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Crontab\Crontab;
use Swozr\Taskr\Server\Crontab\CrontabRegister;
use Swozr\Taskr\Server\RabbmitMq\Mq;
use Swozr\Taskr\Server\RabbmitMq\MqRegister;
use Swozr\Taskr\Server\Server;
use Swozr\Taskr\Server\Swozr;

class TaskBuilder
{
    /**
     * 可构建的特殊任务
     */
    const SPECIAL_TASKS_DEPLOY = [
        BaseTask::TYPE_CRONTAB => [
            CrontabRegister::class,
            Crontab::class
        ], //定时任务
        BaseTask::TYPE_RABBMIT_MQ => [
            MqRegister::class,
            Mq::class
        ] //rabbmit任务
    ];

    /**
     * @var array
     */
    private static $builder = [];

    /**
     * 分配work进程做任务
     * @param int $workId
     * @return bool|int
     */
    public static function assign(int $workId)
    {
        //监听者注册
        foreach (self::SPECIAL_TASKS_DEPLOY as $taskType => $arr) {
            [$registerClassName, $runClassName] = $arr;

            /**@var \Swozr\Taskr\Server\Base\SpecialTaskRegister $registerClassName * */
            if ($registerClassName::getRegisters() && (!isset(self::$builder[$taskType]) || count(self::$builder[$taskType]) < $registerClassName::$processNum)) {
                if (Swozr::$server->getWorkProcessRole($workId) == Server::ROLE_WORK_PROCESS_TASK && BaseTask::TYPE_CRONTAB == $taskType) {
                    //task process
                    //定时任务只使用workr进程开启，这样可以投递异步任务到task 进程
                    return false;
                }

                //已配置该任务、根据$processNum启动该模式
                /**@var \Swozr\Taskr\Server\Contract\SpecialTask $runClassName * */
                $runClassName::run();

                return self::$builder[$taskType][] = $workId;
            }
        }

        return false;
    }
}