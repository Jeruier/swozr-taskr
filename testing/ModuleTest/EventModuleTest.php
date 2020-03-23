<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/25
 * Time: 10:04
 */

namespace SwozrTest\Taskr\Server\ModuleTest;


use Swozr\Taskr\Server\Base\EventManager;
use Swozr\Taskr\Server\Contract\EventHandlerInterface;
use Swozr\Taskr\Server\Event\ServerEvent;
use Swozr\Taskr\Server\Swozr;

require __DIR__ . '/../../vendor/autoload.php';

class EventModuleTest
{
    public $eventManage;

    public function __construct()
    {
        $this->eventManage = EventManager::getInstance();
    }

    /**
     * 注册事件监听者
     * @param $eventName
     * @param $router
     * @throws \ReflectionException
     * @throws \Swozr\Taskr\Server\Exception\SwozrException
     */
    public function registerEvent($eventName, $router)
    {
        return $this->eventManage->addListener($eventName, $router);
    }

    /**
     * 获取监听者
     * @return array
     */
    public function getListeners()
    {
        return $this->eventManage->getListeners();
    }

    /**
     * @param $event EventHandlerInterface
     * 处理事件
     */
    public function handleEvent($event)
    {
        echo __METHOD__ . "\n";
    }

    /**
     * 处理事件(静态方法)
     * @param $event EventHandlerInterface
     */
    public static function staticHandleEvent($event)
    {
        echo __METHOD__ . "\n";
    }

    /**
     * 处理事件函数执行的时候
     */
    public function __invoke($event)
    {
        echo __METHOD__ . "\n";
    }
}

//注册事件
$eventModuleTest = new EventModuleTest();
/**
 * 几种添加事件调度的路由方法
 */
//类方法
$eventModuleTest->registerEvent(ServerEvent::AFTER_ADDED_EVENT, '\SwozrTest\Taskr\Server\EventModuleTest@handleEvent');
//静态方法
$eventModuleTest->registerEvent(ServerEvent::AFTER_ADDED_EVENT, '\SwozrTest\Taskr\Server\EventModuleTest@staticHandleEvent');
//实现事件处理接口的类
$eventModuleTest->registerEvent(ServerEvent::BEFORE_ADDED_EVENT, '\SwozrTest\Taskr\Server\Listener\TestHandleListener');
//可做函数方法调用的类
$eventModuleTest->registerEvent(ServerEvent::BEFORE_ADDED_EVENT, '\SwozrTest\Taskr\Server\EventModuleTest');
//匿名函数
$eventModuleTest->registerEvent(ServerEvent::BEFORE_ADDED_EVENT, function ($event) {
    echo "closures\n";
});
//一次性注册
$eventModuleTest->registerEvent(ServerEvent::WORK_PROCESS_START, [
    '\SwozrTest\Taskr\Server\EventModuleTest@handleEvent',
    '\SwozrTest\Taskr\Server\EventModuleTest@staticHandleEvent',
    '\SwozrTest\Taskr\Server\Listener\TestHandleListener',
    '\SwozrTest\Taskr\Server\EventModuleTest'
]);


//触发事件
$reflection = new \ReflectionClass(ServerEvent::class);
$eventNames = $reflection->getConstants();
foreach ($eventNames as $eventName){
    echo "####################$eventName####################\n";
    Swozr::trigger($eventName, new EventModuleTest());
}
