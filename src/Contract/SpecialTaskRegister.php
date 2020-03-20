<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/19
 * Time: 13:46
 */

namespace Swozr\Taskr\Server\Contract;


interface SpecialTaskRegister
{
    public static function register($config);

    public static function getRegisters(): array;
}