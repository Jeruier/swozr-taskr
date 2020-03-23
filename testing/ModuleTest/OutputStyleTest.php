<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/20
 * Time: 16:38
 */

namespace SwozrTest\Taskr\Server\ModuleTest;

use Swozr\Taskr\Server\Tools\OutputStyle\Output;

require __DIR__ . '/../../vendor/autoload.php';

class OutputStyleTest
{

}
$output = new Output();
$output->info("asdsad");
$output->error("asdsad");
$output->success("asdsad");
$output->danger("asdsad");
$output->primary("asdsad");
$output->warning("asdsad");
$output::Panel([
'taskr' => [
    'listen' => '0.0.0.0:9501',
    'type'   => 'type',
    'mode'   => 'mode',
    'worker' => 1,
    'task_worker_num' => 2
]
],'Server Information', [
    'titleStyle' => 'cyan',
]);
