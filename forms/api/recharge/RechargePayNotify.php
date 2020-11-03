<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

namespace app\forms\api\recharge;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\models\BalanceLog;
use app\models\RechargeOrders;
use app\models\User;
use app\models\UserInfo;

class RechargePayNotify extends PaymentNotify
{
    public function notify($paymentOrder)
    {
        try {
            /* @var RechargeOrders $order */
            $order = RechargeOrders::find()->where(['order_no' => $paymentOrder->orderNo])->one();

            if (!$order) {
                throw new \Exception('订单不存在:' . $paymentOrder->orderNo);
            }

            if ($order->pay_type != 1) {
                throw new \Exception('必须使用微信支付');
            }

            $order->is_pay = 1;
            $order->pay_time = date('Y-m-d H:i:s', time());
            $res = $order->save();

            if (!$res) {
                throw new \Exception('充值订单支付状态更新失败');
            }

            $user = User::findOne($order->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $price = (float)($order->pay_price + $order->send_price);
            $desc = '充值余额：' . $order->pay_price . '元,赠送：' . $order->send_price . '元';
            $customDesc = \Yii::$app->serializer->encode($order->attributes);
            \Yii::$app->currency->setUser($user)->balance->add($price, $desc, $customDesc);
            \Yii::$app->currency->setUser($user)->integral->add(
                $order->send_integral,
                "余额充值,赠送积分{$order->send_integral}",
                $customDesc
            );

        } catch (\Exception $e) {
            \Yii::error($e);
            throw $e;
        }
    }
}
