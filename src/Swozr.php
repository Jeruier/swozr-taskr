<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2019/12/20
 * Time: 17:32
 */

namespace Swozr\Taskr\Server;


use Swozr\Taskr\Server\Base\Event;
use Swozr\Taskr\Server\Base\EventManager;
use Swozr\Taskr\Server\Contract\EventInterface;
use Swozr\Taskr\Server\Exception\RuntimeException;
use Swozr\Taskr\Server\Helper\SystemHelper;
use Swozr\Taskr\Server\Tools\OutputStyle\Console;
use Swozr\Taskr\Server\Tools\OutputStyle\Output;

class Swozr
{
    /**
     * @var Server
     */
    public static $server;

    /**
     * taskr server start banner logo
     */
    const BANNER_LOGO_SMALL = "
 ______           __
/_  __/___ ______/ /_______
  / / / __ `/ ___/ //_/ ___/
 / / / /_/ (__  ) ,< / /
/_/  \__,_/____/_/|_/_/  
";

    /**
     * taskr server start banner logo
     */
    const BANNER_LOGO_FULL = "
   _____                             ______           __
  / ___/      ______  ____  _____   /_  __/___ ______/ /_______
  \__ \ | /| / / __ \/_  / / ___/    / / / __ `/ ___/ //_/ ___/
 ___/ / |/ |/ / /_/ / / /_/ /       / / / /_/ (__  ) ,< / /
/____/|__/|__/\____/ /___/_/       /_/  \__,_/____/_/|_/_/                     
";

    /**
     * waring级别日志
     */
    const LOG_LEVEL_WARNING = 'warning';

    /**
     * info级别日志
     */
    const LOG_LEVEL_INFO = 'info';

    /**
     * error级别日志
     */
    const LOG_LEVEL_ERROR = 'error';

    /**
     * error级别日志
     */
    const LOG_LEVEL_DEBUG = 'debug';

    public static function init()
    {
        define('TASKR_ROOT_DIR', __DIR__ . '/'); //taskr 根目錄

        define('TASKR_EXCEPTION_DIR', TASKR_ROOT_DIR . 'Exception/'); //异常文件目录

        define('TASKR_EXCEPTION_HANDLER_DIR', TASKR_EXCEPTION_DIR . 'Handler/'); //异常处理文件目录
    }

    /**
     * 触发事件
     * @param string|\Swozr\Taskr\Server\Contract\EventInterface $event
     * @param null $target
     * @param mixed $params
     * @return bool
     */
    public static function trigger($event, $target = null, $params = [])
    {
        return EventManager::getInstance()->trigger($event, $target, $params);
    }

    /**
     * 设置进程名称
     * @param string $name
     * @return bool
     */
    public static function setProcessName(string $name)
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($name);
        } elseif (function_exists('swoole_set_process_name')) {
            @swoole_set_process_name($name);
        }
        return true;
    }

    /**
     *  Create directory
     * @param string $dir
     * @param int $mode
     */
    public static function makeDir(string $dir, int $mode = 0755)
    {
        if (!file_exists($dir) && !mkdir($dir, $mode, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    /**
     * @return Server
     */
    public static function server()
    {
        return Server::getServer();
    }

    /**
     * @return \Swoole\Server
     */
    public static function swooleServer()
    {
        return self::server()->getSwooleServer();
    }

    /**
     * 制作日志前缀，主要记录工作进程id等 [workId: , ...]
     * @param array $arr
     * @param array|string $fields 指定字段
     * @return string
     */
    public static function makeLogPrefix(array $arr, $fields = Event::LOG_PREFIX_FIELDS)
    {
        if (empty($arr)) return '';
        $str = "";
        foreach ($arr as $key => $val) {
            if ((is_array($fields) && in_array($key, $fields)) || (is_string($fields) && $key == $fields)) {
                $str .= "{$key}:{$val},";
            }
        }
        $str = $str ? '[' . rtrim($str, ',') . ']' : '';

        return $str;
    }

    /**
     * 制作事件日志
     * @param Event $event
     */
    public static function makeEventLog(EventInterface $event)
    {
        $msg = self::makeLogPrefix($event->getParams());
        $msg .= $event->getMessage();
        Swozr::server()->log(ucfirst($event->getName()) . ':' . $msg, $event->getData());
    }

    /**
     * 校验运行环境
     * @param string $minPhp
     * @param string $minSwoole
     * @throws RuntimeException
     */
    public static function checkRuntime(string $minPhp = '7.1', string $minSwoole = '4.4.1')
    {
        try {
            if (version_compare(PHP_VERSION, $minPhp, '<')) {
                throw new RuntimeException('Run the server requires PHP version > ' . $minPhp . '! current is ' . PHP_VERSION);
            }

            if (!extension_loaded('swoole')) {
                throw new RuntimeException("Run the server, extension 'swoole' is required!");
            }

            if (version_compare(SWOOLE_VERSION, $minSwoole, '<')) {
                throw new RuntimeException('Run the server requires swoole version > ' . $minSwoole . '! current is ' . SWOOLE_VERSION);
            }

            foreach ([
                         'blackfire',
                         'xdebug',
                         'uopz',
                         'xhprof',
                         'zend',
                         'trace',
                     ] as $ext) {
                if (extension_loaded($ext)) {
                    throw new RuntimeException("The extension of '{$ext}' must be closed, otherwise swoole will be affected!");
                }
            }
        } catch (\Exception $e) {
            Console::writeln("<danger>Taskr Server start fail! " . PHP_EOL . "{$e->getMessage()}</danger>" . PHP_EOL);
            exit(0);
        }
    }

    /**
     * Show sowzr taskr logo banner
     */
    public static function showBanner()
    {
        [$width,] = SystemHelper::getScreenSize();
        $logoText = $width > 90 ? self::BANNER_LOGO_FULL : self::BANNER_LOGO_SMALL;
        $logoText = ltrim($logoText, "\n");

        Console::colored(Output::applyIndent($logoText), 'cyan');
    }
}
