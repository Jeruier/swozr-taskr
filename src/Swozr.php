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

    /**
     * 触发事件
     * @param string|\Swozr\Taskr\Server\contract\EventInterface $event
     * @param null $target
     * @param mixed ...$params
     * @return bool
     */
    public static function trigger($event, $target = null, ...$params)
    {
        return EventManager::getInstance()->trigger($event, $target, $params);
    }

    /**
     * 设置进程名称
     * @param string $name
     * @return bool
     */
    public static function setProcessName(string $name)
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($name);
        } elseif (function_exists('swoole_set_process_name')) {
            @swoole_set_process_name($name);
        }
        return true;
    }

    /**
     *  Create directory
     * @param string $dir
     * @param int $mode
     */
    public static function makeDir(string $dir, int $mode = 0755)
    {
        if (!file_exists($dir) && !mkdir($dir, $mode, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }
}
