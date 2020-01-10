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
        // TODO: Implement handle() method.
        echo __METHOD__ . PHP_EOL;
    }
}