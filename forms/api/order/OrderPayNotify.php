<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/16 11:21
 */


namespace app\forms\api\order;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\events\OrderEvent;
use app\models\Order;

class OrderPayNotify extends PaymentNotify
{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder)
    {
        $order = Order::findOne([
            'order_no' => $paymentOrder->orderNo,
        ]);
        if (!$order) {
            return false;
        }
        $order->is_pay = 1;
        switch ($paymentOrder->payType) {
            case PaymentOrder::PAY_TYPE_HUODAO:
                $order->is_pay = 0;
                $order->pay_type = 2;
                break;
            case PaymentOrder::PAY_TYPE_BALANCE:
                $order->pay_type = 3;
                break;
            case PaymentOrder::PAY_TYPE_WECHAT:
                $order->pay_type = 1;
                break;
            case PaymentOrder::PAY_TYPE_ALIPAY:
                $order->pay_type = 4;
                break;
            case PaymentOrder::PAY_TYPE_BAIDU:
                $order->pay_type = 5;
                break;
            case PaymentOrder::PAY_TYPE_TOUTIAO:
                $order->pay_type = 6;
                break;
            default:
                break;
        }
        $order->pay_time = date('Y-m-d H:i:s');
        $order->save();

        $event = new OrderEvent();
        $event->order = $order;
        $event->sender = $this;
        \Yii::$app->trigger(Order::EVENT_PAYED, $event);
        return true;
    }
}
