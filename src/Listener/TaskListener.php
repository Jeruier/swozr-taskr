<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/10
 * Time: 17:46
 */

namespace Swozr\Taskr\Server\Listener;


use Swozr\Taskr\Server\Contract\EventHandlerInterface;
use Swozr\Taskr\Server\Contract\EventInterface;
use Swozr\Taskr\Server\Swozr;

class TaskListener implements EventHandlerInterface
{
    public function handle(EventInterface $event)
    {
        Swozr::makeEventLog($event);
    }
}