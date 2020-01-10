<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/25
 * Time: 14:09
 */

namespace SwozrTest\Taskr\Server;


use Swozr\Taskr\Server\Base\BaseTask;
use Swozr\Taskr\Server\Event\SwooleEvent;
use Swozr\Taskr\Server\Server;
use Swozr\Taskr\Server\Swozr;

require __DIR__ . '/../vendor/autoload.php';
class ServerModuleTest
{

}
$server = new Server();
$server->start();
