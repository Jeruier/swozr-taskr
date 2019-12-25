<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/25
 * Time: 10:53
 */

namespace SwozrTest\Taskr\Server\Listener;


use Swozr\Taskr\Server\contract\EventHandlerInterface;
use Swozr\Taskr\Server\contract\EventInterface;

class TestHandleListener implements EventHandlerInterface
{
    public function handle(EventInterface $event)
    {
        echo __METHOD__ . "\n";
    }
}