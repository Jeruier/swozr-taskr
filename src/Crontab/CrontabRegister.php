<?php
/**
 * Created by PhpStorm.
 * User: zhangrui
 * Date: 2020/1/21
 * Time: 10:58 AM
 */

namespace Swozr\Taskr\Server\Crontab;


use Swozr\Taskr\Server\Base\SpecialTaskRegister;
use Swozr\Taskr\Server\Exception\CrontabException;

class CrontabRegister extends SpecialTaskRegister
{
    /**
     * [
     *    'cron' => class::name, ...
     * ]
     * @var array
     */
    private static $crontabs = [];

    /**
     * register cron
     * @param string $cron
     * @param $className
     * @throws CrontabException
     */
    public static function registerCron(string $cron, $className)
    {
        if (!CrontabExpression::parse($cron)) {
            throw new CrontabException(
                sprintf('`%s::$cron`  expression format is error', $className)
            );
        }

        self::checkClass($className);

        self::$crontabs[$cron] = $className;
    }

    /**
     * use classname register cron
     * @param $className
     * @throws CrontabException
     */
    public static function register($className)
    {
        /** @var \Swozr\Taskr\Server\Base\BaseTask $className * */
        self::registerCron($className::$cron, $className);
    }

    /**
     * $crontabs
     * @return array
     */
    public static function getRegisters(): array
    {
        return self::$crontabs;
    }

    /**
     * 获取当前可执行任务类名
     * @return array
     */
    public static function getCronTasks()
    {
        $tasks = [];
        $time = time();
        foreach (self::$crontabs as $cron => $className) {
            if (!CrontabExpression::parseObj($cron, $time)) {
                continue;
            }

            $tasks[] = $className;
        }

        return $tasks;
    }
}