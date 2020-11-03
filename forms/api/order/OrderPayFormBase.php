<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/2/18 17:00
 */


namespace app\forms\api\order;


use app\core\payment\Payment;
use app\core\payment\PaymentOrder;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;

abstract class OrderPayFormBase extends Model
{
    abstract public function getResponseData();


    /**
     * @param Order[] $orders
     * @return array
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    protected function getReturnData($orders)
    {
        $hasMchOrder = false;
        foreach ($orders as $order) {
            if ($order->mch_id != 0) {
                $hasMchOrder = true;
                break;
            }
        }
        $supportPayTypes = (array)$order->decodeSupportPayTypes($order->support_pay_types);
        if (!count($supportPayTypes)) {
            $supportPayTypes = [
                Payment::PAY_TYPE_BALANCE,
                Payment::PAY_TYPE_WECHAT,
                Payment::PAY_TYPE_ALIPAY,
            ];
        }
        if ($hasMchOrder && isset($supportPayTypes[PaymentOrder::PAY_TYPE_HUODAO])) {
            unset($supportPayTypes[PaymentOrder::PAY_TYPE_HUODAO]);
        }
        $paymentOrders = [];
        foreach ($orders as $order) {
            $paymentOrder = new PaymentOrder([
                'title' => $this->getOrderTitle($order),
                'amount' => (float)$order->total_pay_price,
                'orderNo' => $order->order_no,
                'notifyClass' => OrderPayNotify::class,
                'supportPayTypes' => $supportPayTypes,
            ]);
            $paymentOrders[] = $paymentOrder;
        }
        $id = \Yii::$app->payment->createOrder($paymentOrders);
        return [
            'code' => 0,
            'data' => [
                'id' => $id,
            ],
        ];
    }

    /**
     * @param Order $order
     */
    private function getOrderTitle($order)
    {
        /** @var OrderDetail[] $details */
        $details = $order->getDetail()->andWhere(['is_delete' => 0])->with('goods')->all();
        if (!$details || !is_array($details) || !count($details)) {
            return $order->order_no;
        }
        $titles = [];
        foreach ($details as $detail) {
            if (!$detail->goods) {
                continue;
            }
            $titles[] = $detail->goods->name;
        }
        $title = implode(';', $titles);
        if (mb_strlen($title) > 32) {
            return mb_substr($title, 0, 32);
        } else {
            return $title;
        }
    }
}
