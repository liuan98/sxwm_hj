<?php

namespace app\forms\common\order;

use app\events\OrderEvent;
use app\forms\common\template\tplmsg\Tplmsg;
use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\Model;
use app\models\Order;
use app\models\OrderClerk;
use app\models\User;


class CommonOrderClerk extends Model
{
    public $id;
    public $action_type;
    public $clerk_remark;
    public $clerk_id;
    public $clerk_type;

    public function rules()
    {
        return [
            [['id', 'action_type', 'clerk_id', 'clerk_type'], 'required'],
            [['id', 'action_type', 'clerk_id', 'clerk_type'], 'integer'],
            [['clerk_remark'], 'string'],
        ];
    }

    public function affirmPay()
    {
        $beginTransaction = \Yii::$app->db->beginTransaction();
        try {
            /* @var Order $order */
            $order = Order::find()->where([
                'is_delete' => 0,
                'send_type' => 1,
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ])->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($order->cancel_status != 0) {
                throw new \Exception('订单取消中,无法收款');
            }

            if ($order->is_pay == 1) {
                throw new \Exception('订单已支付,下拉刷新页面数据');
            }

            $clerkUserIds = ClerkUserStoreRelation::find()
                ->where(['store_id' => $order->store_id])
                ->select('clerk_user_id');

            if ($this->action_type != 2) {
                /** @var ClerkUser $clerkUser */
                $clerkUser = ClerkUser::find()->where([
                    'user_id' => $this->clerk_id,
                    'id' => $clerkUserIds,
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'mch_id' => $order->mch_id
                ])->with('store')->asArray()->one();
                if (!$clerkUser) {
                    throw new \Exception('用户不是核销员、无权限执行此操作');
                }
            }

            $order->is_pay = 1;
            $order->pay_type = 2;
            $order->pay_time = date('Y-m-d H:i:s', time());
            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }
            \Yii::$app->trigger(Order::EVENT_PAYED, new OrderEvent([
                'order' => $order
            ]));

            $orderClerk = new OrderClerk();
            $orderClerk->mall_id = \Yii::$app->mall->id;
            $orderClerk->affirm_pay_type = $this->action_type;
            $orderClerk->clerk_type = $this->clerk_type;
            $orderClerk->order_id = $order->id;
            $res = $orderClerk->save();

            if (!$res) {
                throw new \Exception($orderClerk);
            }

            $beginTransaction->commit();
            return true;
        } catch (\Exception $e) {
            $beginTransaction->rollBack();
            throw $e;
        }
    }

    public function orderClerk()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
//            if (!$this->clerk_remark) {
//                throw new \Exception('请填写核销备注');
//            }
            /** @var Order $order */
            $order = Order::find()->where([
                'is_delete' => 0,
                'send_type' => 1,
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ])->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中，不能进行操作');
            }

            if ($order->cancel_status == 2) {
                throw new \Exception('订单申请退款中');
            }

            if ($order->cancel_status == 1) {
                throw new \Exception('订单已退款');
            }

            if ($order->is_pay != 1) {
                throw new \Exception('订单未支付，请先进行收款');
            }

            $clerkUserIds = ClerkUserStoreRelation::find()->where(['store_id' => $order->store_id])->select('clerk_user_id');

            /** @var ClerkUser $clerkUser */
            $clerkUser = ClerkUser::find()->where([
                'user_id' => $this->clerk_id,
                'id' => $clerkUserIds,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'mch_id' => $order->mch_id
            ])->with('store')->one();
            if (!$clerkUser) {
                throw new \Exception('没有核销权限，禁止核销');
            }

            $order->is_send = 1;
            $order->send_time = date('Y-m-d H:i:s', time());
            $order->is_confirm = 1;
            $order->confirm_time = date('Y-m-d H:i:s', time());
            $order->clerk_id = $clerkUser->id;
            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            $orderClerk = OrderClerk::find()->where(['order_id' => $order->id])->one();
            if (!$orderClerk) {
                $orderClerk = new OrderClerk();
                $orderClerk->mall_id = \Yii::$app->mall->id;
                $orderClerk->affirm_pay_type = $this->action_type;
                $orderClerk->order_id = $order->id;
            }
            $orderClerk->clerk_remark = $this->clerk_remark ?: '';
            $orderClerk->clerk_type = $this->clerk_type;

            // jambalaya添加核销时产生佣金-s
            $hxpercent = \Yii::$app->mall->getMallSettingOne('hxpercent');//佣金比例
            if($hxpercent >0 && !empty($hxpercent)){
                $hx_money = $order->total_price* $hxpercent/100;
                $first = User::findOne($clerkUser->user_id);
                \Yii::$app->currency->setUser($first)->brokerage
                        ->add(floatval($hx_money), "由订单{$order->order_no}核销的佣金");

                // order_clerk添加核销佣金
                $orderClerk->yongjin_money = $hx_money;
            }
            
            // jambalaya添加核销时产生佣金-e

            $res = $orderClerk->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderClerk));
            }

            $transaction->commit();
            \Yii::$app->trigger(Order::EVENT_CONFIRMED, new OrderEvent([
                'order' => $order
            ]));



            //通知
            $tplMsg = new Tplmsg();
            $tplMsg->orderClerkTplMsg($order, '订单已核销');
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
