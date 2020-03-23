<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/23
 * Time: 10:51
 */

namespace SwozrTest\Taskr\Server;


use Swozr\Taskr\Server\TaskrEngine;
use Swozr\Taskr\Server\Tools\OutputStyle\Output;

class Taskr
{
    const  ACTIONS = ['start', 'stop', 'status', 'reload', 'restart'];

    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function run($argv)
    {
        if (1 == count($argv)) {
            require self::tips();
        }
        $action = $argv[1];
        if (!in_array($action, self::ACTIONS)){
            return self::tips();
        }
        ( new TaskrEngine($this->config))->$action();
    }

    public static function tips()
    {
        Output::mList([
            'Usage:' => "php bin/taskr <info>COMMAND</info>",
            'Available Commands:' => self::ACTIONS,
        ], [
            'sepChar' => '   ',
        ]);
    }

}