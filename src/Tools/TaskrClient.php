<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/1/9
 * Time: 13:40
 */

namespace Swozr\Taskr\Server\Tools;


use Swoole\Coroutine\Client as CoClient;
use Swoole\Client;
use Swozr\Taskr\Server\Exception\ClientException;

class TaskrClient
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
    protected $port = 9501;

    /**
     * Deafault timeout
     * @var float
     */
    protected $timeout = 0.5;


    /**
     * Deafault setting
     * @var array
     */
    protected $setting = [];


    /**
     * 是否协程客户端
     * @var bool
     */
    private $isCoroutineMode = false;

    /**
     * 单例
     * @var null
     */
    private static $instance = null;

    /**
     * @var Client
     */
    private $client;

    private function __construct()
    {
        $this->client = $this->isCoroutineMode ? new CoClient() : new Client(SWOOLE_SOCK_TCP);
    }

    /**
     * @return null|TaskrClient
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * set host
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * swt port
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * set timeout
     * @param float $timeout
     */
    public function setTimeout(float $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return array
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * set setting
     * @param array $setting
     */
    public function setSetting(array $setting)
    {
        $this->setting = $setting;
    }

    /**
     * 设置协程模式
     */
    public function setCoroutineMode(){
        $this->isCoroutineMode = true;
    }

    /**
     * @param string $msg
     * @throws ClientException
     */
    public static function Error(string $msg)
    {
        throw new ClientException($msg);
    }

    /**
     * 发送数据
     * @param string $str
     * @return bool
     * @throws ClientException
     */
    public function send(string $str)
    {
        try {
            if (!$this->client->connect($this->host, $this->port, $this->timeout)) {
                self::Error(sprintf("connect failed. Error: %s", $this->client->errCode));
            }

            $this->setting && $this->client->set($this->setting);
            $this->client->send($str);
            $this->client->close();
        } catch (\Exception $e) {
            self::Error($e->getMessage());
        }

        return true;
    }
}