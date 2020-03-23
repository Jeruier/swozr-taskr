<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 14:07
 */

namespace Swozr\Taskr\Server\Processor;


use Swozr\Taskr\Server\Contract\ProcessorInterface;

abstract class Processor implements ProcessorInterface
{
    protected $taskrEngine;

    public function __construct($taskrEngine)
    {
        $this->taskrEngine = $taskrEngine;
    }

}