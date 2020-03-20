<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/18
 * Time: 16:14
 */

namespace Swozr\Taskr\Server\RabbmitMq;


use Swozr\Taskr\Server\Contract\Parse;

class MqExpression implements Parse
{
    /**
     * @param $configs
     * @return bool
     * @throws \Swozr\Taskr\Server\Exception\RabbmitMqException
     */
    public static function parse($configs): bool
    {
        foreach ($configs as $config){
            RabbmitMq::checkConfig($config);
        }

        return true;
    }
}