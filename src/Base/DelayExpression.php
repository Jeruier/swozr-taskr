<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/8
 * Time: 11:47
 */

namespace Swozr\Taskr\Server\Base;


use Swozr\Taskr\Server\Contract\Parse;

class DelayExpression implements Parse
{
    public static function parse($rule): bool
    {
        return ctype_digit($rule) && $rule > 0 && $rule < 86400000 ? true : false;
    }
}