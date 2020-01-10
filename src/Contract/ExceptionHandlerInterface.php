<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/10
 * Time: 15:00
 */

namespace Swozr\Taskr\Server\Contract;


interface ExceptionHandlerInterface
{
    public function handle(\Exception $e);
}