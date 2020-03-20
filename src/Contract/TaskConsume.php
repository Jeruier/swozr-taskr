<?php
/**
 *任务消费接口
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/19
 * Time: 11:32
 */

namespace Swozr\Taskr\Server\Contract;


interface TaskConsume
{
    /**
     * 任务消费
     * @return string
     */
    public function consume(): string;
}