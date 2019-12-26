<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/26
 * Time: 17:10
 */

namespace Swozr\Taskr\Server\contract;


interface PipeMessage
{
    public function onPipeMessage(\swoole_server $server, int $srcWorkerId, mixed $message);
}