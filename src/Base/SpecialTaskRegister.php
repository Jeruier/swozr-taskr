<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/19
 * Time: 14:39
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Contract\TaskConsume;
use Swozr\Taskr\Server\Exception\RegisterException;

abstract class SpecialTaskRegister
{
    public static $processNum = 1; //执行该任务开启使用的进程数

    abstract public static function register($data);

    abstract public static function getRegisters(): array;

    /**
     * 校验执行消费类的合法性
     * @param $className
     * @throws RegisterException
     * @throws \ReflectionException
     */
    public static function checkClass($className){
        $reflect = new \ReflectionClass($className);
        if (!$reflect->isSubclassOf(BaseTask::class)) {
            throw new RegisterException(sprintf("% (class = %s) must extends class %s", get_called_class(), $className, BaseTask::class));
        }
        if (!$reflect->implementsInterface(TaskConsume::class)) {
            throw new RabbmitMqRegisterException(sprintf("% (class = %s) must implement interface %s", get_called_class(), $className, TaskConsume::class));
        }
    }
}