<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 14:07
 */

namespace Swozr\Taskr\Server\Contract;


interface ProcessorInterface
{
    public function handle();
}