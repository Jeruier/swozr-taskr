<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/24
 * Time: 10:22
 */

namespace Swozr\Taskr\Server\contract;


interface EventInterface
{
    public function setName(string $name);

    public function getName(): string;

    public function setParams(array $params);

    public function getParam($key, $dafault = null);

    public function getParams(): array;

    public function setTarget($target);

    public function getTarget();
}