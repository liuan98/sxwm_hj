<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/3
 * Time: 13:36
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\plugins\dianqilai\forms\common;


use app\forms\common\template\tplmsg\Tplmsg;

class CommonTemplate extends Tplmsg
{
    /**
     * @param $serviceName
     * @param $timestamp
     * @param $content
     * @return array
     * @throws \Exception
     */
    public function contactTpl($serviceName, $timestamp, $content)
    {
        try {
            $data = [
                'keyword1' => [
                    'value' => $serviceName,
                    'color' => '#333333'
                ],
                'keyword2' => [
                    'value' => date('Y-m-d H:i:s', $timestamp),
                    'color' => '#333333'
                ],
                'keyword3' => [
                    'value' => "【消息内容】" . urldecode($content),
                    'color' => '#333333'
                ],
                'keyword4' => [
                    'value' => '您好，有商家客服联系您了，点击进入会话',
                    'color' => '#333333'
                ],
            ];
            return $this->send($data, 'contact_tpl');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
