<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/10
 * Time: 17:51
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Event\ServerEvent;
use Swozr\Taskr\Server\Event\SwooleEvent;

final class ListenerRegister
{
    /**
     * @var array
     */
    private static $listeners = [];

    /**
     * 添加事件监听者
     * @param string $className
     * @param $definition
     */
    public static function addListener(string $eventName, $definition)
    {
        // Collect listeners
        self::$listeners[$eventName][] = $definition;
    }

    /**
     * 注册监听者
     * @param EventManager $eventManager
     * @return bool
     * @throws \ReflectionException
     * @throws \Swozr\Taskr\Server\Exception\RegisterEventException
     */
    public static function register(EventManager $eventManager)
    {
        //载入包内的已定义事件监听类的监听者
        $events = [SwooleEvent::class, ServerEvent::class]; //swoole事件，定义的流程性事件
        $swooleEventNames = [];
        foreach ($events as $className){
            $reflection = new \ReflectionClass($className);
            $swooleEventNames = array_merge($swooleEventNames, $reflection->getConstants());
        }
        foreach ($swooleEventNames as $eventName) {
            $listenerClass = '\Swozr\Taskr\Server\Listener\\' . ucfirst($eventName) . 'Listener';
            if (class_exists($listenerClass)) {
                $eventManager->addListener($eventName, $listenerClass);
            }
        }

        //添加事件监听（用户自定义）
        foreach (self::$listeners as $eventName => $eventInfo) {
            $eventManager->addListener($eventName, $eventInfo);
        }

        return true;
    }
}