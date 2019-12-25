<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/20
 * Time: 17:32
 */

namespace Swozr\Taskr\Server;


use Swozr\Taskr\Server\Base\EventManager;

class Swozr
{
    public static $app;

    public static function trigger(string $eventName, $target = null, ...$params)
    {
        return EventManager::getInstance()->trigger($eventName, $target, $params);
    }
}
