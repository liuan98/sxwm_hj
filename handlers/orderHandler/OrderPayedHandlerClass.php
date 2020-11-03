<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 10:58
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\handlers\orderHandler;

class OrderPayedHandlerClass extends BaseOrderPayedHandler
{
    public function handle()
    {
        \Yii::error('mall order payed');
        self::execute();
    }

    protected function execute()
    {
        $this->user = $this->event->order->user;
        if ($this->event->order->pay_type == 2) {
            if ($this->event->order->is_pay == 0) {
                // 支付方式：货到付款未支付时，只触发部分通知类
                self::notice();
            } else {
                // 支付方式：货到付款，订单支付时，触发剩余部分
                self::pay();
            }
        } else {
            self::notice();
            self::pay();
        }
        // 改价的情况 需重新计算分销价
        self::addShareOrder();
    }

    protected function notice()
    {
        \Yii::error('--mall notice--');
        $this->sendSms()->sendMail()->receiptPrint('pay')
            ->sendTemplate()->sendMpTemplate()->sendBuyPrompt()->setGoods();
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--mall pay--');
        $this->saveResult()->becomeJuniorByFirstPay()->becomeShare();
        return $this;
    }
}
