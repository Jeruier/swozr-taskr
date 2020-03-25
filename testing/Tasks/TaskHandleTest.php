<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/9
 * Time: 15:42
 */

namespace SwozrTest\Taskr\Server\Tasks;


use Swozr\Taskr\Server\Base\BaseTask;
use Swozr\Taskr\Server\Contract\TaskConsume;
use Swozr\Taskr\Server\Contract\TaskNotice;

class TaskHandleTest extends BaseTask implements TaskNotice,TaskConsume
{
    public static $cron = '0 0/5 * * * *';

    public static function pushFailure(array $data, int $delay, string $failMsg)
    {
        // TODO: Implement pushFailure() method.
        echo __METHOD__ . PHP_EOL;
    }

    public function pushed()
    {
        // TODO: Implement pushed() method.
        echo __METHOD__ . PHP_EOL;
    }

    public function consume(): string
    {
        var_dump($this->getData());
        // TODO: Implement consume() method.
        return __METHOD__ . PHP_EOL;
    }

    public function finished()
    {
        // TODO: Implement finished() method.
        echo __METHOD__ . PHP_EOL;
    }
}