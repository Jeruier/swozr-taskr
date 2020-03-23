<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 14:10
 */

namespace Swozr\Taskr\Server\Processor;


use Swozr\Taskr\Server\Base\ListenerRegister;

class ListenerProcessor extends Processor
{
    public function handle()
    {
        //添加监听者
        foreach ($this->taskrEngine->listener as $eventName => $definition) {
            ListenerRegister::addListener($eventName, $definition);
        }
    }
}