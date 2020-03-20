<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/18
 * Time: 16:24
 */

namespace Swozr\Taskr\Server\Contract;


use Swozr\Taskr\Server\Base\BaseTask;

interface SpecialTask
{
    /**
     * 启动该服务
     * @return mixed
     */
    public static function run();

    /**
     * 服务任务handle
     * @return mixed
     */
    public static function handle(BaseTask $taskObj);
}