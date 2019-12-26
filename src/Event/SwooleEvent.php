<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/23
 * Time: 18:05
 */

namespace Swozr\Taskr\Server\Event;


use Swozr\Taskr\Server\contract\Close;
use Swozr\Taskr\Server\contract\Connect;
use Swozr\Taskr\Server\contract\PipeMessage;
use Swozr\Taskr\Server\contract\RequestInterface;

class SwooleEvent
{
    /**
     * Start
     */
    const START = 'start';

    /**
     * Shutdown
     */
    const SHUTDOWN = 'shutdown';

    /**
     * WorkerStart
     */
    const WORKER_START = 'workerStart';

    /**
     * WorkerStop
     */
    const WORKER_STOP = 'workerStop';

    /**
     * WorkerError
     */
    const WORKER_ERROR = 'workerError';

    /**
     * ManagerStart
     */
    const MANAGER_START = 'managerStart';

    /**
     * ManagerStop
     */
    const MANAGER_STOP = 'managerStop';

    /**
     * Task
     */
    const TASK = 'task';

    /**
     * Finish
     */
    const FINISH = 'finish';

    /**
     * PipeMessage
     */
    const PIPE_MESSAGE = 'pipeMessage';

    /**
     * Handshake
     */
    const HANDSHAKE = 'handshake';

    /**
     * Message
     */
    const MESSAGE = 'message';

    /**
     * Open
     */
    const OPEN = 'open';

    /**
     * Request
     */
    const REQUEST = 'request';

    /**
     * Packet
     */
    const PACKET = 'packet';

    /**
     * Receive
     */
    const RECEIVE = 'receive';

    /**
     * Connect
     */
    const CONNECT = 'connect';

    /**
     * Close
     */
    const CLOSE = 'close';

    /**
     * 可以自定义的事件
     */
    const CUSTOM_EVENTS_MAPPING = [
        self::REQUEST => RequestInterface::class,
        self::CONNECT => Connect::class,
        self::CLOSE => Close::class,
        self::PIPE_MESSAGE => PipeMessage::class
    ];
}