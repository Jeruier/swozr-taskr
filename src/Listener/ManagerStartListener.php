<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/20
 * Time: 17:43
 */

namespace Swozr\Taskr\Server\Listener;


use Swoole\Process;
use Swozr\Taskr\Server\Contract\EventHandlerInterface;
use Swozr\Taskr\Server\Contract\EventInterface;
use Swozr\Taskr\Server\Swozr;
use Swozr\Taskr\Server\Tools\OutputStyle\Console;

class ManagerStartListener implements EventHandlerInterface
{
    public function handle(EventInterface $event)
    {
        $bgMsg = '';
        if (Swozr::server()->isDaemon()) {
            $bgMsg = '(Run in background)!';
        }
        Console::writef("<success>Taskr Server Start Success{$bgMsg}</success> (Master PID: <mga>%d</mga>, Manager PID: <mga>%d</mga>)",
            Swozr::swooleServer()->master_pid,
            Swozr::swooleServer()->manager_pid);


        // Dont handle on mac OS
        if (stripos(PHP_OS, 'Darwin') !== false){
            return;
        }

        // Listen signal: Ctrl+C (SIGINT = 2)
//        $server = Swozr::server();
//        Process::signal(2, function () use ($server) {
//            $server->output->success("Stop server by CTRL+C");
//            $server->getSwooleServer()->shutdown();
//        });
    }
}