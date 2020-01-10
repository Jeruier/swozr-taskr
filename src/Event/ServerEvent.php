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
    const BEFORE_SETTING = 'beforeSetting';

    /**
     * Before add swoole events
     */
    const BEFORE_ADDED_EVENT = 'beforeAddedEvent';

    /**
     * After add swoole events
     */
    const AFTER_ADDED_EVENT = 'afterAddedEvent';

    /**
     * Before add process(es)
     */
    const BEFORE_ADDED_PROCESS = 'addedProcessBefore';

    /**
     * Add process(es)
     */
    const ADDED_PROCESS = 'addedProcess';

    /**
     * After each process is successfully added
     */
    const AFTER_ADDED_PROCESS = 'addedProcessAfter';

    /**
     * before start server event
     */
    const BEFORE_START = 'beforeStart';

    /**
     * On task process start event
     */
    const TASK_PROCESS_START = 'taskProcessStart';

    /**
     * On task process start event
     */
    const TASK_PROCESS_STOP = 'taskProcessStop';

    /**
     * On work process start event
     */
    const WORK_PROCESS_START = 'workProcessStart';

    /**
     * On work process start event
     */
    const WORK_PROCESS_STOP = 'workProcessStop';

    /**
     * After after event
     */
    const AFTER_EVENT = 'eventAfter';

    /**
     * Before shutdown event
     */
    const BEFORE_SHUTDOWN_EVENT = 'shutdownBefore';

    /**
     * Before start event
     */
    const BEFORE_START_EVENT = 'eventStartBefore';

    /**
     * Before worker error event
     */
    const BEFORE_WORKER_ERROR_EVENT = 'workerErrorBefore';
}