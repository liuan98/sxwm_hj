<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/11 14:49
 */


namespace app\controllers\api\notices;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;

class TestBuyNotify extends PaymentNotify
{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder)
    {
        \Yii::warning('支付结果通知：' . \Yii::$app->serializer->encode($paymentOrder->attributes));
    }
}
