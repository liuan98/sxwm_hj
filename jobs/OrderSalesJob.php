<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/14
 * Time: 16:10
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */

namespace app\jobs;


use app\events\OrderEvent;
use app\models\Mall;
use app\models\Order;
use app\models\OrderRefund;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderSalesJob extends Component implements JobInterface
{
    public $orderId;

    public function execute($queue)
    {
        \Yii::error('order sales job->>' . $this->orderId);
        $order = Order::findOne([
            'id' => $this->orderId,
            'is_delete' => 0,
            'is_send' => 1,
            'is_confirm' => 1,
            'is_sale' => 0
        ]);
        if (!$order) {
            return;
        }
        $mall = Mall::findOne(['id' => $order->mall_id]);
        \Yii::$app->setMall($mall);

        $orderRefundList = OrderRefund::find()->where(['order_id' => $order->id, 'is_delete' => 0])->all();
        if ($orderRefundList) {
            /* @var OrderRefund[] $orderRefundList */
            foreach ($orderRefundList as $orderRefund) {
                if ($orderRefund->is_confirm == 0) {
                    return false;
                }
            }
        }

        $order->is_sale = 1;
        if ($order->save()) {
            $event = new OrderEvent([
                'order' => $order
            ]);
            \Yii::$app->trigger(Order::EVENT_SALES, $event);
        }
    }
}
