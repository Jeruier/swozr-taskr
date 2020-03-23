<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 14:40
 */

namespace Swozr\Taskr\Server\Processor;


use Swozr\Taskr\Server\Base\ExceptionManager;
use Swozr\Taskr\Server\Swozr;

class ExecptionManagerProcessor extends Processor
{
    public function handle()
    {
        //添加异常处理者
        if ($this->taskrEngine->exceptionHandler) {
            $execptionManager = new ExceptionManager();
            foreach ($this->taskrEngine->exceptionHandler as $exceptionClass => $handlerClass) {
                $execptionManager->addHandler($exceptionClass, $handlerClass);
            }
            Swozr::server()->setExecptionManager($execptionManager);
        }
    }
}