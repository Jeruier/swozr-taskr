<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 14:40
 */

namespace Swozr\Taskr\Server\Processor;


use Swozr\Taskr\Server\Crontab\CrontabRegister;

class CrontabsProcessor extends Processor
{
    public function handle()
    {
        /**
         * 载入定时任务
         */
        foreach ($this->taskrEngine->crontabs as $cron => $className) {
            is_int($cron) ? CrontabRegister::register($className) : CrontabRegister::registerCron($cron, $className);
        }
    }
}