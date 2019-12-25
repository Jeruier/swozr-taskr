<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/23
 * Time: 18:26
 */

namespace Swozr\Taskr\Server;

use Swoole\Server as SwooleServer;

class Server
{
    /**
     * Default host address
     *
     * @var string
     */
    protected $host = '0.0.0.0';

    /**
     * Default port
     *
     * @var int
     */
    protected $port = 80;

    /**
     * Default mode type
     *
     * @var int
     */
    protected $mode = SWOOLE_PROCESS;

    /**
     * Default socket type
     *
     * @var int
     */
    protected $type = SWOOLE_SOCK_TCP;

    /**
     * Server setting for swoole. (@see swooleServer->setting)
     *
     * @link https://wiki.swoole.com/wiki/page/274.html
     * @var array
     */
    protected $setting = [];

    /**
     * Swoole Server
     *
     */
    protected $swooleServer;

    /**
     * Server constructor
     */
    public function __construct()
    {
        $this->setting = $this->defaultSetting();
    }


    /**
     * 设置Server运行时的各项参数
     * @return array
     */
    protected function defaultSetting(): array
    {
        return [
            'daemonize' => 0,
            'worker_num' => swoole_cpu_num(),
            'task_worker_num' => 1
        ];
    }

    public function start()
    {
        $this->swooleServer = new SwooleServer($this->host, $this->port, $this->mode, $this->type);


    }
}