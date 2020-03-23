<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 14:40
 */

namespace Swozr\Taskr\Server\Processor;



use Swozr\Taskr\Server\Exception\RuntimeException;
use Swozr\Taskr\Server\RabbmitMq\MqRegister;

class RabbmitMqsProcessor extends Processor
{
    public function handle()
    {
        //载入rabbmitMq任务
        if ($this->taskrEngine->rabbmitMqs) {
            if (!extension_loaded('amqp')) {
                throw new RuntimeException("loading rabbmitMq task, extension 'amqp' is required!");
            }
            MqRegister::$processNum = $this->taskrEngine->MQProcessMum; //设置Mq执行进程数

            if (count($this->taskrEngine->rabbmitMqs) == count($this->taskrEngine->rabbmitMqs, 1)) {
                //以一维数组的形式配置了一项，
                MqRegister::register($this->taskrEngine->rabbmitMqs);
            } else {
                //已多维数组的形式配置
                foreach ($this->taskrEngine->rabbmitMqs as $config) {
                    MqRegister::register($config);
                }
            }
        }
    }
}