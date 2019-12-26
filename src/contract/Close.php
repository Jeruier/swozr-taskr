<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/26
 * Time: 17:09
 */

namespace Swozr\Taskr\Server\contract;


interface Close
{
    public function onClose(\swoole_server $server, int $fd, int $reactorId);
}