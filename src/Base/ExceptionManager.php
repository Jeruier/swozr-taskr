<?php
/**
 * 异常处理管理
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/10
 * Time: 14:44
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Contract\ExceptionHandlerInterface;
use Swozr\Taskr\Server\Exception\Handler\DefaultExceptionHandler;

class ExceptionManager
{
    /**
     * 此包自定义的异常处理都会优先触发
     * @var array
     * [
     *  exception class => handler class,
     *  ... ...
     * ]
     */
    private $handlers = [];

    /**
     * 添加异常自定义处理
     * @param string $exceptionClass
     * @param string $handlerClass
     * @throws \ReflectionException
     */
    public function addHandler(string $exceptionClass, string $handlerClass)
    {
        $reflection = new \ReflectionClass($handlerClass);
        if (!$reflection->implementsInterface(ExceptionHandlerInterface::class)) {
            throw new \Exception(sprintf("(class = %s) must implement interface %s", $handlerClass, ExceptionHandlerInterface::class));
        }
        $this->handlers[$exceptionClass] = $handlerClass;
    }

    /**
     * 异常自定义处理
     * @param \Exception $e
     * @return bool
     */
    public function handler(\Exception $e)
    {
        $errClass = get_class($e);

        //执行包内异常处理
        $errHandlerClass = "{$errClass}Handler";
        $errHandlerClass = class_exists($errHandlerClass) ? $errHandlerClass : DefaultExceptionHandler::class;
        /** @var ExceptionHandlerInterface $errHandlerObj **/
        $errHandlerObj = new $errHandlerClass();
        $errHandlerObj->handle($e);

        //执行自定义的异常处理
        if (isset($this->handlers[$errClass])){
            //设置自定义处理异常
            $errHandlerObj = new $this->handlers[$errClass]();
            $errHandlerObj->handle($e);
        }
        return true;
    }
}