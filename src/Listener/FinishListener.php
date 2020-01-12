<?php
/**
 * Created by PhpStorm.
 * User: zhangrui
 * Date: 2020/1/12
 * Time: 7:32 PM
 */

namespace Swozr\Taskr\Server\Listener;


use Swozr\Taskr\Server\Contract\EventHandlerInterface;
use Swozr\Taskr\Server\Contract\EventInterface;
use Swozr\Taskr\Server\Swozr;

class FinishListener implements EventHandlerInterface
{
    public function handle(EventInterface $event)
    {
        $msg = Swozr::makeLogPrefix($event->getParams(), 'str');
        Swozr::server()->log($msg, $event->getParam('str'), $event->getName());
    }
}