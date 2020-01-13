<?php
/**
 * Created by PhpStorm.
 * User: zhangrui
 * Date: 2020/1/12
 * Time: 7:47 PM
 */

namespace Swozr\Taskr\Server\Listener;


use Swozr\Taskr\Server\Contract\EventHandlerInterface;
use Swozr\Taskr\Server\Contract\EventInterface;
use Swozr\Taskr\Server\Swozr;

class ReceiveListener implements EventHandlerInterface
{
    public function handle(EventInterface $event)
    {
        Swozr::makeEventLog($event);
    }
}