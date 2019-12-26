<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/23
 * Time: 10:49
 */

namespace Swozr\Taskr\Server\Base;


use function PHPSTORM_META\type;
use Swozr\Taskr\Server\contract\EventHandlerInterface;
use Swozr\Taskr\Server\contract\EventInterface;
use Swozr\Taskr\Server\Exception\RegisterEventException;

class EventManager
{

    const LISTENER_METHOD_DELIMITER = '@';  //监听触发方法分隔符

    /**
     * 单例
     * @var null
     */
    private static $instance = null;

    /**
     * @var EventInterface
     */
    protected $event;

    /**
     * 监听者
     * [
     *      eventNmae => [
     *           [EventInterface|[className, staticMethod]|[Object, method]],
     *          ...
     *      ],
     *      ...
     * ]
     * @var array
     */
    protected $listeners = [];

    private function __construct()
    {
        $this->event = new Event();
    }

    private function __clone()
    {
    }

    /**
     * 单例模式
     * @return null|EventManager
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $eventName 事件名称
     * @param $definition array|string|callable
     * @return bool
     * @throws RegisterEventException
     * @throws \ReflectionException
     */
    public function addListener(string $eventName, $definition)
    {
        if (is_array($definition)) {
            foreach ($definition as $listener) {
                $this->addListener($eventName, $listener);
            }
            return true;
        }

        if (is_callable($definition)) {
            //匿名函数
            $this->listeners[$eventName][] = $definition;
            return true;
        }

        if (!is_string($definition)) {
            throw new \InvalidArgumentException('Invalid definition params');
        }

        [$class, $method] = strrpos($definition, self::LISTENER_METHOD_DELIMITER) ? explode(self::LISTENER_METHOD_DELIMITER, $definition) : [$definition, ''];
        if (!class_exists($class)) {
            throw new RegisterEventException(sprintf("Class %s does not exist", $class));
        }

        //class@method|class
        $refletion = new \ReflectionClass($class);
        if ($refletion->implementsInterface(EventHandlerInterface::class) || (!$method && $refletion->hasMethod('__invoke'))) {
            //实现触发事件处理接口的类或可当函数执行的类
            $this->listeners[$eventName][] = new $class;
            return true;
        }

        if (!$method) {
            //没有定义应该调用哪个方法来处理
            throw new RegisterEventException(sprintf("No method defined for event call in class %s", $class));
        }

        if (!$refletion->hasMethod($method)) {
            //不存在该类
            throw new RegisterEventException(sprintf("Method %s does not exist in class %s", $method, $class));
        }

        $refletionMethod = $refletion->getMethod($method);

        if (!$refletionMethod->getParameters()) {
            //方法至少定义一个参数
            throw new RegisterEventException(sprintf("Method %s defines at least one parameter in class %s, first parameter type is %s", $method, $class, EventHandlerInterface::class));
        }

        //判断是否为类中静态方法
        if (!$refletionMethod->isStatic()) {
            //判断是否被实例化
            if (!$refletion->isInstantiable()) {
                throw new RegisterEventException(sprintf("Class %s can`t instantiable", $class));
            }

            //构造方法不需要参数或参数有默认值
            $constructor = $refletion->getConstructor();
            if ($constructor) {
                //定义了构造方法
                foreach ($constructor->getParameters() as $parameter) {
                    if (!$parameter->isDefaultValueAvailable()) {
                        //该类存在参数未设置默认值不能实例化
                        throw new RegisterEventException(sprintf("Parameter $%s not set default value cannot be instantiated in class %s", $parameter->getName(), $class));
                    }
                }
            }

            $this->listeners[$eventName][] = [new $class, $method]; //使用指定函数方法[Object 实例化类, calledMethodName 被调用方法]
        } else {
            $this->listeners[$eventName][] = [$class, $method];//使用指定函数静态方法[className 类名, calledMethodName 被调用方法]
        }
        return true;
    }

    /**
     * 获取监听者
     * @return array
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * 触发
     * @param string|EventInterface $event
     * @param null $target
     * @param array $args
     * @return bool
     */
    public function trigger($event, $target = null, array $args = [])
    {
        if (is_string($event)) {
            $eventName = $event;
            $event = $this->event;
            $event->setName($eventName);
        } elseif (!($event instanceof EventInterface)) {
            throw new \InvalidArgumentException('Invalid event params for trigger event handler');
        }
        $event->setTarget($target);
        $event->setParams($args);

        foreach ([$event->getName(), '*'] as $name) {
            if (isset($this->listeners[$name])) {
                $this->triggerListeners($name, $event);
            }
        }
        return true;
    }

    /**
     * 触发监听者
     * @param string $eventName
     * @param EventInterface $event
     */
    public function triggerListeners(string $eventName, EventInterface $event)
    {
        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            if (is_object($listener)) {
                if ($listener instanceof EventHandlerInterface) {
                    $listener->handle($event);
                } elseif (method_exists($listener, '__invoke')) {
                    $listener($event);
                }
            } elseif (is_callable($listener)) {
                $listener($event);
            } elseif (is_array($listener)) {
                [$class, $method] = $listener;
                if (is_object($class)) {
                    $class->$method($event);
                } elseif (is_string($class)) {
                    $class::$method($event);
                }
            }
        }
    }

}