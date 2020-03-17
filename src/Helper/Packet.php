<?php
/**
 * 数据打包解包
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/17
 * Time: 14:02
 */

namespace Swozr\Taskr\Server\Helper;


use Swozr\Taskr\Server\Base\BaseTask;

class Packet
{
    /**
     * server层打包数据
     * @param string $class
     * @param array $data
     * @param array $attributes
     * @return string
     */
    public static function pack(string $class, array $data = [], array $attributes = [])
    {
        $data = [
            'class' => $class,
            'data' => $data,
            'attributes' => $attributes,
        ];

        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 打包client发送给server的数据
     * @param string $class
     * @param array $data
     * @param string $taskType
     * @param int $delay
     * @return string
     */
    public static function packClinet(string $class, array $data = [], string $taskType = BaseTask::TYPE_ASYNC, int $delay = 0)
    {
        $data = [
            'class' => $class,
            'data' => $data,
            'taskType' => $taskType,
            'delay' => $delay,
        ];

        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 解server层打包的数据
     * @param string $str
     * @return array
     */
    public static function unpack(string $str)
    {
        $strdata = JsonHelper::decode($str, true);

        $class = $strdata['class'] ?? '';
        $data = $strdata['data'] ?? [];
        $attributes = $strdata['attributes'] ?? [];

        return [$class, $data, $attributes];
    }

    /**
     * server端解client端发送过来的数据
     * @param string $str
     * @return array
     */
    public static function unpackClient(string $str)
    {
        $strdata = JsonHelper::decode($str, true);

        $class = $strdata['class'] ?? '';
        $data = $strdata['data'] ?? [];
        $taskType = $strdata['taskType'] ?? BaseTask::TYPE_ASYNC;
        $delay = $strdata['delay'] ?? 0;

        return [$class, $data, $taskType, $delay];
    }

    /**
     * 打包task return 内容
     * @param $result
     * @param int|null $errorCode
     * @param string $errorMessage
     * @return string
     */
    public static function packResponse($result, int $errorCode = null, string $errorMessage = '')
    {
        if ($errorCode !== null) {
            $data = [
                'code' => $errorCode,
                'message' => $errorMessage
            ];
        } else {
            $data['result'] = $result;
        }

        return JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 解包task return 内容
     * @param string $str
     * @return array
     */
    public static function unpackResponse(string $str)
    {
        $data = JsonHelper::decode($str, true);

        if (array_key_exists('result', $data)) {
            return [$data['result'], null, ''];
        }

        $errorCode = $data['code'] ?? 0;
        $errorMessage = $data['message'] ?? '';

        return [null, $errorCode, $errorMessage];
    }
}