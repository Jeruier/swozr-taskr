<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/26
 * Time: 17:07
 */

namespace Swozr\Taskr\Server\contract;


interface Connect
{
    public function onConnect(\swoole_server $server, int $fd, int $reactorId);
}