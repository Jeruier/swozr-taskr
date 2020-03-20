<?php
/**
 * RabbmitMq类
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/18
 * Time: 10:46
 */

namespace Swozr\Taskr\Server\Helper;

use Swozr\Taskr\Server\Exception\RabbmitMqException;
use Swozr\Taskr\Server\Exception\RegisterException;

class RabbmitMq
{
    /**
     * 单例
     * @var null
     */
    private static $instance = [];

    /**
     * 必须配置的字段
     */
    const CONFIG_REQUIRE_FIELDS = [
        'host',
        'exchange_name',
        'queue_name',
    ];

    public $host = "127.0.0.1";

    public $username = "guest";

    public $password = "guest";

    public $port = "5672";

    public $queue_name = null;

    public $exchange_name = null;

    public $routing_key = null;

    /**
     * @var \AMQPExchange
     */
    private $exchange;

    /**
     * @var \AMQPChannel
     */
    private $channel;

    /**
     * @var \AMQPConnection
     */
    private $connect;

    /**
     * @var \AMQPQueue
     */
    private $queue;

    public $exchange_type = AMQP_EX_TYPE_DIRECT;

    public $exchange_flags = AMQP_DURABLE;

    public $queue_flags = AMQP_DURABLE;

    public $consume_flags = AMQP_NOPARAM;

    /**
     * RabbmitMq constructor.
     * @param array $config
     */
    private function __construct($config = [])
    {
        foreach ($config as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }

        $this->getAMQPExchange();
    }

    /**
     * @param array $config
     * @return null|RabbmitMq
     * @throws RabbmitMqException
     */
    public static function getInstance($config = [])
    {
        $changeName = $config['exchange_name'] ?? '';
        $queueName = $config['queue_name'] ?? '';
        $routingKey = $config['routing_key'] ?? '';
        $sign = md5($changeName . $queueName . $routingKey);

        if (empty(self::$instance[$sign])) {
            self::checkConfig($config);

            self::$instance[$sign] = new self($config);
        }

        return self::$instance[$sign];
    }

    /**
     * check config
     * @param $config
     * @throws RabbmitMqException
     */
    public static function checkConfig($config)
    {
        foreach (self::CONFIG_REQUIRE_FIELDS as $field) {
            if (!array_key_exists($field, $config)) {
                throw new RegisterException("set config $field is require");
            }
        }
    }

    /**
     * @return \AMQPConnection
     * @throws \AMQPChannelException
     */
    private function getConnect()
    {
        try {
            if ($this->connect && $this->connect->isConnected()) {
                return $this->connect;
            }

            $connect = new \AMQPConnection([
                'host' => $this->host,
                'port' => $this->port,
                'login' => $this->username,
                'password' => $this->password
            ]);
            $connect->connect();

            return $this->connect = $connect;
        } catch (\AMQPConnectionException $e) {
            throw new \AMQPChannelException($e->getMessage() . "{$this->host}:{$this->port}");
        }
    }

    /**
     * @return \AMQPExchange
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function getAMQPExchange()
    {
        if ($this->exchange && $this->exchange->getChannel()->isConnected()) {
            return $this->exchange;
        }

        $exchange = new \AMQPExchange($this->getChannel());
        $exchange->setType($this->exchange_type);
        $exchange->setName($this->exchange_name);
        $exchange->setFlags($this->exchange_flags);
        $exchange->declareExchange();

        return $this->exchange = $exchange;

    }

    /**
     * @return \AMQPChannel
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     */
    public function getChannel()
    {
        if ($this->channel && $this->channel->isConnected()) {
            return $this->channel;
        }

        return $this->channel = new \AMQPChannel($this->getConnect());
    }

    /**
     * @return \AMQPQueue
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
    public function getAMQPQueue()
    {
        if ($this->queue && $this->queue->getChannel()->isConnected()) {
            return $this->queue;
        }

        $queue = new \AMQPQueue($this->getChannel());
        $queue->setFlags($this->queue_flags);
        $this->queue_name && $queue->setName($this->queue_name);
        $queue->declareQueue();
        $queue->bind($this->exchange_name, $this->routing_key);
        return $this->queue = $queue;
    }

    /**
     * @return \AMQPEnvelope|bool
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
    public function getEnvelope()
    {
        try {
            return $this->getAMQPQueue()->get($this->consume_flags);
        } catch (\AMQPChannelException $AMQPChannelException) {
            $this->disconnect();
            throw new RabbmitMqException("AMQPChannelException: {$AMQPChannelException->getMessage()}");
        } catch (\AMQPConnectionException $AMQPConnectionException) {
            $this->disconnect();
            throw new RabbmitMqException("AMQPChannelException: {$AMQPConnectionException->getMessage()}");
        }
    }

    public function publish(string $str, $routingKey = '')
    {
        return $this->getAMQPExchange()->publish($str, $routingKey);
    }

    public function disconnect()
    {
        $this->queue = null;
        $this->exchange = null;
        $this->channel = null;
        $this->connect = null;
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}

