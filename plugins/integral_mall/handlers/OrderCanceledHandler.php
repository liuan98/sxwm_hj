<?php
/**
 * @copyright (c)天幕网络
 * @author Lu Wei
 * @link http://www.67930603.top/
 * Created by IntelliJ IDEA
 * Date Time: 2019/2/14 10:54
 */


namespace app\plugins\integral_mall\handlers;


use app\core\currency\IntegralModel;
use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Order;
use app\models\User;
use app\models\UserCard;
use app\models\UserInfo;
use app\plugins\integral_mall\models\IntegralMallOrders;
use app\plugins\integral_mall\Plugin;

class OrderCanceledHandler extends HandlerBase
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            /** @var OrderEvent $event */
            // 退回积分
            if ($event->order->sign !== (new Plugin())->getName()) {
                return true;
            }
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $integralOrder = IntegralMallOrders::find()->where([
                    'order_id' => $event->order->id,
                    'mall_id' => $event->order->mall_id
                ])->one();

                if (!$integralOrder) {
                    throw new \Exception('积分商城订单不存在,id=>' . $event->order->id);
                }

                $userInfo = UserInfo::find()->where([
                    'user_id' => $event->order->user_id
                ])->one();
                if (!$userInfo) {
                    throw new \Exception('用户信息不存在');
                }

                $user = User::findOne($event->order->user_id);
                \Yii::$app->currency->setUser($user)->integral->refund(
                    (int)$integralOrder->integral_num,
                    "积分商城:订单取消积分退回",
                    $integralOrder->token
                );
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        });
    }
}
