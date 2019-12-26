<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/25
 * Time: 15:07
 */

namespace Swozr\Taskr\Server\contract;


interface RequestInterface
{
    public function onRequest(\swoole_http_server $request, \swoole_http_response $response);
}