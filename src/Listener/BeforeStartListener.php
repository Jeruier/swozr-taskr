<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 10:25
 */

namespace Swozr\Taskr\Server\Listener;


use Swozr\Taskr\Server\Contract\EventHandlerInterface;
use Swozr\Taskr\Server\Contract\EventInterface;
use Swozr\Taskr\Server\Server;
use Swozr\Taskr\Server\Swozr;
use Swozr\Taskr\Server\Tools\OutputStyle\Console;

class BeforeStartListener implements EventHandlerInterface
{
    public function handle(EventInterface $event)
    {
        /**@var Server $server**/
        $server = $event->getTarget();
        Console::writef('Server extra info: pidFile <info>%s</info>', $server->getPidFile());
    }

}