<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/10
 * Time: 15:16
 */

namespace SwozrTest\Taskr\Server;

use Swozr\Taskr\Server\Base\ExceptionManager;
use Swozr\Taskr\Server\Exception\TaskException;

require __DIR__ . '/../vendor/autoload.php';

$manager = new ExceptionManager();
$manager->handler(new TaskException());
