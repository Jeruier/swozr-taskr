<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/10
 * Time: 15:10
 */

namespace Swozr\Taskr\Server\Exception\Handler;


use Swozr\Taskr\Server\Contract\ExceptionHandlerInterface;
use Swozr\Taskr\Server\Swozr;

class DefaultExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(\Exception $e)
    {
        $msg = "code: {$e->getCode()}, file: {$e->getFile()}, line: {$e->getLine()}, msg: {$e->getMessage()} ";
        $name = get_class($e);
        $loglevel = $name == \Exception::class ? Swozr::LOG_LEVEL_ERROR : Swozr::LOG_LEVEL_WARNING;
        Swozr::$server->log($name . ':' . $msg, '', $loglevel);
    }
}