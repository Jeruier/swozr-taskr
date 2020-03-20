<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/18
 * Time: 16:26
 */

namespace Swozr\Taskr\Server\RabbmitMq;


use Swozr\Taskr\Server\Base\SpecialTaskRegister;
use Swozr\Taskr\Server\Exception\RegisterException;
use Swozr\Taskr\Server\Helper\RabbmitMq;

class MqRegister extends SpecialTaskRegister
{
    /**
     * @var array
     */
    private static $rabbmitMqs = [];

    const CLASS_NAME_FIELD = 'class'; //类名字段

    public static $processNum = 1; //执行该任务开启使用的进程数

    /**
     * @param $config
     * @throws RegisterException
     * @throws \ReflectionException
     * @throws \Swozr\Taskr\Server\Exception\RabbmitMqException
     */
    public static function register($config)
    {
        $className = $config['class'] ?? '';
        if ('' == $className) {
            throw new RegisterException("set config class is require");
        }
        RabbmitMq::checkConfig($config);
        self::checkClass($className);

        self::$rabbmitMqs[] = $config;
    }

    /**
     * @return array
     */
    public static function getRegisters():array
    {
        return self::$rabbmitMqs;
    }
}