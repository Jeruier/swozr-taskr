<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/24
 * Time: 17:55
 */

namespace SwozrTest\Taskr\Server\ExceptionHandler;


use Swozr\Taskr\Server\Contract\ExceptionHandlerInterface;
use Swozr\Taskr\Server\Tools\OutputStyle\Output;

class TaskExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(\Exception $e)
    {
        (new Output())->danger("file={$e->getFile()} line={$e->getLine()} message={$e->getMessage()}");
    }
}