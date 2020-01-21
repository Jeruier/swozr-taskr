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
use Swozr\Taskr\Server\Server;
use Swozr\Taskr\Server\Swozr;

class TaskBuilder
{
    const TYPE_CRONTAB = 'corntab';  //定时任务

    const TYPE_RABBIT_MQ = 'rabbit_mq'; //rabbitMq任务

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
        if (Swozr::$server->getWorkProcessRole($workId) == Server::ROLE_WORK_PROCESS_TASK) {
            //task work process
            return false;
        }

        if (CrontabRegister::getCrontabs() && !isset(self::$builder[self::TYPE_CRONTAB])) {
            //已注册定时任务
            //启动改模式
            Crontab::run();

            return self::$builder[self::TYPE_CRONTAB] = $workId;
        }

        return false;
    }
}