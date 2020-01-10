<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/9
 * Time: 15:44
 */

namespace SwozrTest\Taskr\Server;


use Swozr\Taskr\Server\Tools\TaskrClient;
use SwozrTest\Taskr\Server\Tasks\TaskHandleTest;

require __DIR__ . '/../vendor/autoload.php';

class PublishTaskTest
{
    /**
     * 默认配置的客户端发布
     * @throws \Swozr\Taskr\Server\Exception\TaskException
     */
    public function defaultPush(){
        TaskHandleTest::publish(['a' => 1, 'b' => 2, 'ext' => __METHOD__]);  //异步任务
        TaskHandleTest::publish(['a' => 1, 'b' => 2, 'ext' => __METHOD__], 5000);  //延迟任务
    }

    /**
     * 自定义配置客户端发布
     */
    public function customPush(){
        $taskrClientObj = TaskrClient::getInstance();
        $taskrClientObj->setHost('0.0.0.0');
        $taskrClientObj->setPort(9501);
        $taskrClientObj->setTimeout(1);

        TaskHandleTest::publish(['a' => 1, 'b' => 2, 'ext' => __METHOD__], $taskrClientObj); //异步任务
        TaskHandleTest::publish(['a' => 1, 'b' => 2, 'ext' => __METHOD__], $taskrClientObj, 5000); //延迟任务
    }
}

$taskr = new PublishTaskTest();
echo '默认配置发布异步任务和延迟任务'.PHP_EOL;
$taskr->defaultPush(); //默认客户端发布

echo '自定义配置发布异步任务和延迟任务'.PHP_EOL;
$taskr->customPush(); //默认客户端发布


