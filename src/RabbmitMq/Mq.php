<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/18
 * Time: 16:06
 */

namespace Swozr\Taskr\Server\RabbmitMq;


use Swozr\Taskr\Server\Base\BaseTask;
use Swozr\Taskr\Server\Base\TaskDispatcher;
use Swozr\Taskr\Server\Contract\SpecialTask;
use Swozr\Taskr\Server\Exception\MqRejectMsgException;
use Swozr\Taskr\Server\Exception\TaskException;
use Swozr\Taskr\Server\Helper\RabbmitMq;
use Swozr\Taskr\Server\Swozr;

class Mq implements SpecialTask
{
    /**
     * 开启rabbmitMq任务处理
     * @return mixed|void
     */
    public static function run()
    {
        $taskDispatcher = new TaskDispatcher();

        while (true) {
            foreach (MqRegister::getRegisters() as $config) {
                try {
                    $taskDispatcher->dispatch($config[MqRegister::CLASS_NAME_FIELD], $config, [
                        'taskType' => BaseTask::TYPE_RABBMIT_MQ,
                        'srcWorkerId' => Swozr::swooleServer()->worker_id,
                    ]);
                } catch (\Exception $e) {
                    Swozr::server()->execptionManager->handler($e);
                }
            }
        }
    }

    /**
     * @param BaseTask|\Swozr\Taskr\Server\Contract\TaskConsume $taskObj
     * @return bool|mixed
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     * @throws \Swozr\Taskr\Server\Exception\RabbmitMqException
     */
    public static function handle(BaseTask $taskObj)
    {
        $rabbitMqObj = RabbmitMq::getInstance($taskObj->getData());
        $envelope = $rabbitMqObj->getEnvelope();
        if (! $envelope instanceof \AMQPEnvelope){
            //没有消息需要处理
            return false;
        }
        $taskObj->setData($envelope->getBody()); //rabbmit的数据

        try{
            //trigger consume event
            TaskDispatcher::triggerConsumeEvent($taskObj);

            $result = $taskObj->consume();
            //rabbmitMq任务消息反馈
            $rabbitMqObj->getAMQPQueue()->nack($envelope->getDeliveryTag());
        }catch (MqRejectMsgException $e){
                //rabbmitMq任务消息反馈
                $rabbitMqObj->getAMQPQueue()->nack($envelope->getDeliveryTag());
               return false;
        }catch (\Exception $e){
                //rabbmitMq任务消息反馈
                $rabbitMqObj->getAMQPQueue()->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
                throw new TaskException($e);
        }

        return $result;
    }
}