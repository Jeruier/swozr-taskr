<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/23
 * Time: 18:15
 */

namespace Swozr\Taskr\Server\Event;


class ServerEvent
{
    /**
     * Before set swoole settings
     */
    const BEFORE_SETTING = 'server.setting.before';

    /**
     * Before add swoole events
     */
    const BEFORE_ADDED_EVENT = 'server.added.event.before';

    /**
     * After add swoole events
     */
    const AFTER_ADDED_EVENT = 'server.added.event.after';

    /**
     * Before add process(es)
     */
    const BEFORE_ADDED_PROCESS = 'server.added.process.before';

    /**
     * Add process(es)
     */
    const ADDED_PROCESS = 'server.added.process';

    /**
     * After each process is successfully added
     */
    const AFTER_ADDED_PROCESS = 'server.added.process.after';

    /**
     * before start server event
     */
    const BEFORE_START = 'server.start.before';

    /**
     * On task process start event
     */
    const TASK_PROCESS_START = 'server.process.task.start';

    /**
     * On task process start event
     */
    const TASK_PROCESS_STOP = 'server.process.task.stop';

    /**
     * On work process start event
     */
    const WORK_PROCESS_START = 'server.process.work.start';

    /**
     * On work process start event
     */
    const WORK_PROCESS_STOP = 'server.process.work.stop';

    /**
     * After after event
     */
    const AFTER_EVENT = 'server.event.after';

    /**
     * Before shutdown event
     */
    const BEFORE_SHUTDOWN_EVENT = 'server.event.shutdown.before';

    /**
     * Before start event
     */
    const BEFORE_START_EVENT = 'server.event.start.before';

    /**
     * Before worker error event
     */
    const BEFORE_WORKER_ERROR_EVENT = 'server.event.worker.error.before';

    /**
     * Before worker start event
     */
    const BEFORE_WORKER_START_EVENT = 'server.event.worker.start.before';

    /**
     * Before worker stop event
     */
    const BEFORE_WORKER_STOP_EVENT = 'server.event.worker.stop.before';
}