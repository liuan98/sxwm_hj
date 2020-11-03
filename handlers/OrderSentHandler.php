<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/14
 * Time: 15:49
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\handlers;


use app\events\OrderEvent;
use app\forms\common\template\tplmsg\Tplmsg;
use app\jobs\OrderConfirmJob;
use app\models\Order;

class OrderSentHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_SENT, function ($event) {
            /** @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $orderAutoConfirmTime = \Yii::$app->mall->getMallSettingOne('delivery_time');

            // 发送模板消息
            $tplMsg = new Tplmsg();
            $tplMsg->orderSendMsg($event->order);

            if (is_numeric($orderAutoConfirmTime) && $orderAutoConfirmTime >= 0) {
                // 订单自动收货任务
                \Yii::$app->queue->delay($orderAutoConfirmTime * 86400)->push(new OrderConfirmJob([
                    'orderId' => $event->order->id
                ]));
                $autoConfirmTime = strtotime($event->order->send_time) + $orderAutoConfirmTime * 86400;
                $event->order->auto_confirm_time = mysql_timestamp($autoConfirmTime);
                $event->order->save();
            }
        });
    }
}
