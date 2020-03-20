<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/8
 * Time: 11:42
 */

namespace Swozr\Taskr\Server\Contract;


interface Parse
{
    /**
     * @param $rule
     * @return bool
     */
    public static function parse($rule):bool;
}