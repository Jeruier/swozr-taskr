<?php
/**
 * 任务通知类接口
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/19
 * Time: 14:18
 */

namespace Swozr\Taskr\Server\Contract;


interface TaskNotice
{
    /**
     * 任务投递失败
     * @param array $data 投递的数据
     * @param int $delay 延迟执行毫秒数
     * @param string $failMsg 任务发布失败原因
     * @return mixed
     */
    public static function pushFailure(array $data, int $delay, string $failMsg);

    /**
     *任务已投递
     * @return mixed
     */
    public function pushed();


    /**
     * 标记任务完成
     * @return mixed
     */
    public function finished();
}