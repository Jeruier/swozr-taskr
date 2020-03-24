<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/25
 * Time: 10:53
 */

namespace SwozrTest\Taskr\Server\Listener;


use Swozr\Taskr\Server\Contract\EventHandlerInterface;
use Swozr\Taskr\Server\Contract\EventInterface;
use Swozr\Taskr\Server\Tools\OutputStyle\Output;

class TestHandleListener implements EventHandlerInterface
{
    public function handle(EventInterface $event)
    {
        (new Output())->info($event->getName() . "handle");
    }
}