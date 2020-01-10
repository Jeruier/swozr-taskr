<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/24
 * Time: 10:20
 */

namespace Swozr\Taskr\Server\Contract;


interface EventHandlerInterface
{
    public function handle(EventInterface $event);
}